#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Sistema de Alertas via Webhook (Task 2.2)
Envia notificações automáticas para Discord ou Slack quando o pipeline falha.

Suporta:
  - Discord Webhooks (formato embed rich)
  - Slack Webhooks (formato block kit)
  - Detecção automática do formato pelo prefixo da URL

Uso independente (teste manual):
  python alerts.py --test

@author  Antigravity AI Designer
@version 1.0.0
@since   2026-05-26
"""

import os
import sys
import json
import time
import logging
import hashlib
from datetime import datetime, timezone

import requests
from dotenv import load_dotenv

# ---------------------------------------------------------------------------
# Configuração
# ---------------------------------------------------------------------------
BASE_DIR    = os.path.dirname(os.path.abspath(__file__))
CONFIG_FILE = os.path.join(BASE_DIR, "config.json")

with open(CONFIG_FILE, "r", encoding="utf-8") as _f:
    _config = json.load(_f)

ALERT_CFG = _config.get("alerts", {})

ALERT_ENABLED       = ALERT_CFG.get("enabled", True)
WEBHOOK_ENV_KEY     = ALERT_CFG.get("webhook_url_env", "WEBHOOK_URL")
MENTION_ON_CRITICAL = ALERT_CFG.get("mention_on_critical", True)
COOLDOWN_SECONDS    = ALERT_CFG.get("cooldown_seconds", 300)
MAX_RETRIES         = ALERT_CFG.get("max_retries", 2)
RETRY_DELAY         = ALERT_CFG.get("retry_delay_seconds", 3)
INCLUDE_LOG_LINES   = ALERT_CFG.get("include_log_lines", 10)

# Arquivo de controle de cooldown (evita spam de notificações)
COOLDOWN_FILE = os.path.join(BASE_DIR, "alert_cooldown.json")

logger = logging.getLogger("Alerts")


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _load_webhook_url() -> str | None:
    """Carrega a URL do webhook da variável de ambiente."""
    load_dotenv(dotenv_path=os.path.join(BASE_DIR, ".env"))
    return os.getenv(WEBHOOK_ENV_KEY, "").strip() or None


def _is_discord(url: str) -> bool:
    """Retorna True se a URL for de um webhook Discord."""
    return "discord.com/api/webhooks" in url or "discordapp.com/api/webhooks" in url


def _is_slack(url: str) -> bool:
    """Retorna True se a URL for de um webhook Slack/Incoming."""
    return "hooks.slack.com" in url


def _get_alert_fingerprint(job_id: str, error_snippet: str) -> str:
    """Gera um hash único para identificar alertas repetidos dentro do cooldown."""
    raw = f"{job_id}:{error_snippet[:120]}"
    return hashlib.md5(raw.encode()).hexdigest()


def _is_in_cooldown(fingerprint: str) -> bool:
    """Verifica se um alerta idêntico já foi enviado dentro do período de cooldown."""
    if not os.path.exists(COOLDOWN_FILE):
        return False
    try:
        with open(COOLDOWN_FILE, "r", encoding="utf-8") as f:
            cooldowns = json.load(f)
        entry = cooldowns.get(fingerprint)
        if not entry:
            return False
        last_sent = entry.get("timestamp", 0)
        return (time.time() - last_sent) < COOLDOWN_SECONDS
    except Exception:
        return False


def _register_cooldown(fingerprint: str, job_id: str):
    """Registra o envio de um alerta no arquivo de cooldown."""
    cooldowns = {}
    if os.path.exists(COOLDOWN_FILE):
        try:
            with open(COOLDOWN_FILE, "r", encoding="utf-8") as f:
                cooldowns = json.load(f)
        except Exception:
            cooldowns = {}

    cooldowns[fingerprint] = {
        "job_id": job_id,
        "timestamp": time.time(),
        "sent_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
    }

    # Limpa entradas expiradas automaticamente
    now = time.time()
    cooldowns = {k: v for k, v in cooldowns.items()
                 if (now - v.get("timestamp", 0)) < COOLDOWN_SECONDS * 2}

    with open(COOLDOWN_FILE, "w", encoding="utf-8") as f:
        json.dump(cooldowns, f, indent=2, ensure_ascii=False)


def _get_last_log_lines(n: int = 10) -> str:
    """Lê as últimas N linhas do scheduler.log para incluir no alerta."""
    log_path = os.path.join(BASE_DIR, "logs", "scheduler.log")
    if not os.path.exists(log_path):
        return "(arquivo de log nao encontrado)"
    try:
        with open(log_path, "r", encoding="utf-8", errors="replace") as f:
            lines = f.readlines()
        tail = lines[-n:] if len(lines) >= n else lines
        return "".join(tail).strip()
    except Exception as e:
        return f"(erro ao ler log: {e})"


# ---------------------------------------------------------------------------
# Formatadores de Payload
# ---------------------------------------------------------------------------

def _build_discord_payload(
    job_id: str,
    level: str,
    title: str,
    description: str,
    log_tail: str,
) -> dict:
    """Monta o payload no formato Discord Embed."""
    color_map = {
        "CRITICAL": 0xE74C3C,  # vermelho forte
        "ERROR":    0xE67E22,  # laranja
        "WARNING":  0xF1C40F,  # amarelo
        "INFO":     0x3498DB,  # azul
    }
    color = color_map.get(level.upper(), 0x95A5A6)
    now_str = datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")

    fields = [
        {"name": "Job", "value": f"`{job_id}`", "inline": True},
        {"name": "Nivel", "value": f"`{level}`", "inline": True},
        {"name": "Horario", "value": f"`{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}`", "inline": True},
    ]

    if log_tail:
        # Discord tem limite de 1024 chars por field
        log_excerpt = log_tail[-900:] if len(log_tail) > 900 else log_tail
        fields.append({
            "name": "Ultimas linhas do log",
            "value": f"```\n{log_excerpt}\n```",
            "inline": False,
        })

    payload = {
        "username": "Geek ao Cubo Pipeline",
        "avatar_url": "https://modomaratona.com/favicon.ico",
        "embeds": [{
            "title": title,
            "description": description,
            "color": color,
            "fields": fields,
            "footer": {"text": "modomaratona.com | Pipeline Automatizado"},
            "timestamp": now_str,
        }],
    }

    if MENTION_ON_CRITICAL and level.upper() == "CRITICAL":
        payload["content"] = "@everyone Pipeline em estado CRITICO!"

    return payload


def _build_slack_payload(
    job_id: str,
    level: str,
    title: str,
    description: str,
    log_tail: str,
) -> dict:
    """Monta o payload no formato Slack Block Kit."""
    emoji_map = {
        "CRITICAL": ":rotating_light:",
        "ERROR":    ":x:",
        "WARNING":  ":warning:",
        "INFO":     ":information_source:",
    }
    emoji = emoji_map.get(level.upper(), ":bell:")

    blocks = [
        {
            "type": "header",
            "text": {"type": "plain_text", "text": f"{emoji} {title}"},
        },
        {
            "type": "section",
            "text": {"type": "mrkdwn", "text": description},
        },
        {
            "type": "section",
            "fields": [
                {"type": "mrkdwn", "text": f"*Job:*\n`{job_id}`"},
                {"type": "mrkdwn", "text": f"*Nivel:*\n`{level}`"},
                {"type": "mrkdwn", "text": f"*Horario:*\n`{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}`"},
            ],
        },
    ]

    if log_tail:
        log_excerpt = log_tail[-1500:] if len(log_tail) > 1500 else log_tail
        blocks.append({
            "type": "section",
            "text": {
                "type": "mrkdwn",
                "text": f"*Ultimas linhas do log:*\n```{log_excerpt}```",
            },
        })

    blocks.append({"type": "divider"})

    return {"blocks": blocks}


# ---------------------------------------------------------------------------
# Envio do Alerta
# ---------------------------------------------------------------------------

def send_alert(
    job_id: str,
    level: str,
    title: str,
    description: str,
    dry_run: bool = False,
) -> bool:
    """
    Envia um alerta via Webhook para Discord ou Slack.

    Args:
        job_id:      Identificador do job que gerou o alerta.
        level:       Nível do alerta: CRITICAL | ERROR | WARNING | INFO
        title:       Título curto do alerta.
        description: Mensagem detalhada.
        dry_run:     Se True, apenas loga o alerta sem enviar via HTTP.

    Returns:
        True se o alerta foi enviado (ou simulado em dry-run), False caso contrário.
    """
    if not ALERT_ENABLED:
        logger.debug("[Alerts] Alertas desabilitados no config.json.")
        return False

    # Verifica cooldown anti-spam
    fingerprint = _get_alert_fingerprint(job_id, description)
    if _is_in_cooldown(fingerprint):
        logger.info(f"[Alerts] Alerta suprimido por cooldown ({COOLDOWN_SECONDS}s) para job '{job_id}'.")
        return False

    # Lê as últimas linhas do log para contexto
    log_tail = _get_last_log_lines(INCLUDE_LOG_LINES)

    if dry_run:
        logger.info(
            f"[Alerts] [DRY-RUN] Alerta '{level}' gerado para job '{job_id}':\n"
            f"  Titulo: {title}\n"
            f"  Desc:   {description}\n"
            f"  Log:    (ultimas {INCLUDE_LOG_LINES} linhas incluidas)"
        )
        _register_cooldown(fingerprint, job_id)
        return True

    webhook_url = _load_webhook_url()
    if not webhook_url:
        logger.warning(
            "[Alerts] WEBHOOK_URL nao configurado no arquivo .env!\n"
            "  Adicione: WEBHOOK_URL=https://discord.com/api/webhooks/... ou\n"
            "            WEBHOOK_URL=https://hooks.slack.com/services/..."
        )
        return False

    # Monta o payload de acordo com a plataforma
    if _is_discord(webhook_url):
        payload = _build_discord_payload(job_id, level, title, description, log_tail)
    elif _is_slack(webhook_url):
        payload = _build_slack_payload(job_id, level, title, description, log_tail)
    else:
        # Formato genérico compatível com qualquer webhook JSON
        payload = {
            "text": f"[{level}] {title}\n{description}",
            "job_id": job_id,
            "timestamp": datetime.now().isoformat(),
        }

    # Envia com retries
    for attempt in range(1, MAX_RETRIES + 2):
        try:
            resp = requests.post(
                webhook_url,
                json=payload,
                headers={"Content-Type": "application/json"},
                timeout=10,
            )
            if resp.status_code in (200, 204):
                logger.info(f"[Alerts] Alerta '{level}' enviado para '{job_id}' (HTTP {resp.status_code}).")
                _register_cooldown(fingerprint, job_id)
                return True
            elif resp.status_code == 429:
                # Rate limit do webhook
                retry_after = float(resp.headers.get("Retry-After", RETRY_DELAY))
                logger.warning(f"[Alerts] Rate limit no webhook. Aguardando {retry_after}s...")
                time.sleep(retry_after)
            else:
                logger.error(f"[Alerts] Falha HTTP {resp.status_code} ao enviar alerta: {resp.text[:200]}")
                time.sleep(RETRY_DELAY)
        except requests.exceptions.ConnectionError:
            logger.error(f"[Alerts] Sem conexao com internet. Tentativa {attempt}/{MAX_RETRIES + 1}.")
            time.sleep(RETRY_DELAY)
        except Exception as exc:
            logger.error(f"[Alerts] Excecao ao enviar alerta (tentativa {attempt}): {exc}")
            time.sleep(RETRY_DELAY)

    logger.error(f"[Alerts] Todas as {MAX_RETRIES + 1} tentativas falharam para o alerta '{job_id}'.")
    return False


# ---------------------------------------------------------------------------
# Helpers de conveniência para o Scheduler
# ---------------------------------------------------------------------------

def alert_job_failure(job_id: str, error: str, dry_run: bool = False) -> bool:
    """Atalho para alertar falha crítica de um job do pipeline."""
    return send_alert(
        job_id=job_id,
        level="ERROR",
        title=f"Falha no Job: {job_id}",
        description=(
            f"O job `{job_id}` do pipeline **Geek ao Cubo** encerrou com erro.\n\n"
            f"**Erro:** `{error[:400]}`\n\n"
            "Verifique o log completo em `automation/logs/scheduler.log`."
        ),
        dry_run=dry_run,
    )


def alert_job_timeout(job_id: str, dry_run: bool = False) -> bool:
    """Atalho para alertar timeout de um job do pipeline."""
    return send_alert(
        job_id=job_id,
        level="CRITICAL",
        title=f"TIMEOUT: Job '{job_id}' excedeu 1 hora!",
        description=(
            f"O job `{job_id}` foi **cancelado forcadamente** por exceder o timeout de 1 hora.\n\n"
            "Possivel causa: loop infinito, deadlock ou API externa sem resposta.\n"
            "Verifique processos pendentes no servidor."
        ),
        dry_run=dry_run,
    )


def alert_rate_limit(job_id: str, api_name: str, dry_run: bool = False) -> bool:
    """Atalho para alertar rate limit persistente de API externa."""
    return send_alert(
        job_id=job_id,
        level="WARNING",
        title=f"Rate Limit: {api_name} bloqueou o job '{job_id}'",
        description=(
            f"A API **{api_name}** retornou erros HTTP 429 persistentes durante o job `{job_id}`.\n\n"
            "O scheduler pode estar sendo executado com frequencia excessiva.\n"
            f"Considere aumentar o intervalo em `config.json` > `scheduler.editorial_interval_hours`."
        ),
        dry_run=dry_run,
    )


def alert_credentials_invalid(job_id: str, service: str, dry_run: bool = False) -> bool:
    """Atalho para alertar credenciais REST inválidas ou expiradas."""
    return send_alert(
        job_id=job_id,
        level="CRITICAL",
        title=f"Credenciais invalidas: {service}",
        description=(
            f"O job `{job_id}` falhou porque as credenciais do servico **{service}** "
            f"estao invalidas ou expiraram.\n\n"
            "**Acao necessaria:** Atualize o arquivo `automation/.env` com as credenciais corretas:\n"
            "- WordPress: `WP_APP_PASSWORD` (renove em WP Admin > Usuarios > Perfil)\n"
            "- Groq: `GROQ_API_KEY` (console.groq.com)\n"
            "- Anthropic: `ANTHROPIC_API_KEY` (console.anthropic.com)"
        ),
        dry_run=dry_run,
    )


# ---------------------------------------------------------------------------
# CLI de Teste
# ---------------------------------------------------------------------------

def _test_alert():
    """Envia um alerta de teste para validar a configuração do webhook."""
    logging.basicConfig(
        level=logging.INFO,
        format="[%(asctime)s] %(levelname)s (%(name)s): %(message)s",
        datefmt="%Y-%m-%d %H:%M:%S",
    )

    webhook_url = _load_webhook_url()
    dry = not webhook_url

    if dry:
        print("\n[INFO] WEBHOOK_URL nao configurado. Rodando em modo dry-run (sem envio real).\n")
    else:
        platform = "Discord" if _is_discord(webhook_url) else "Slack" if _is_slack(webhook_url) else "Generico"
        print(f"\n[INFO] Webhook detectado: {platform}. Enviando alerta real...\n")

    ok = send_alert(
        job_id="test",
        level="INFO",
        title="Teste do Sistema de Alertas — Geek ao Cubo",
        description=(
            "Este e um alerta de **TESTE** do pipeline automatizado.\n\n"
            "Se voce recebeu esta mensagem, o sistema de alertas esta configurado corretamente!\n"
            "`modomaratona.com` | Scheduler v1.0"
        ),
        dry_run=dry,
    )

    if ok:
        print("\n[OK] Alerta de teste enviado com sucesso!\n")
    else:
        print("\n[FALHA] Nao foi possivel enviar o alerta de teste.\n")


if __name__ == "__main__":
    if "--test" in sys.argv:
        _test_alert()
    else:
        print("Uso: python alerts.py --test")
        print("     (envia um alerta de teste para o webhook configurado em WEBHOOK_URL no .env)")
