#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Daemon Agendador Local do Pipeline (Task 2.1 + 2.2)
Executa automaticamente os scripts de automacao nas frequencias do briefing:
  - pipeline.py         -> a cada 6 horas      (coleta RSS + editorial IA + publicacao WP)
  - sync_airing_animes  -> 1x por dia as 07:00  (sincronizacao diaria de animes em exibicao)
  - import_mal_waves    -> 1x por semana (Seg)   (importacao evergreen de novas waves MAL)

Uso:
  python scheduler.py                        # Inicia o daemon (bloqueante)
  python scheduler.py --status               # Exibe jobs e proximas execucoes sem iniciar
  python scheduler.py --test-run editorial   # Dispara o job 'editorial' imediatamente (dry-run)
  python scheduler.py --test-run sync        # Dispara o job 'sync' imediatamente (dry-run)
  python scheduler.py --test-run waves       # Dispara o job 'waves' imediatamente (dry-run)
  python scheduler.py --test-alert           # Envia alerta de teste para o webhook configurado

@author  Antigravity AI Designer
@version 1.1.0
@since   2026-05-26
"""

import os
import sys
import json
import logging
import subprocess
from datetime import datetime
from logging.handlers import RotatingFileHandler

# Importa o modulo de alertas (Task 2.2)
try:
    from alerts import alert_job_failure, alert_job_timeout, alert_credentials_invalid, alert_rate_limit
    _ALERTS_AVAILABLE = True
except ImportError:
    _ALERTS_AVAILABLE = False

# ---------------------------------------------------------------------------
# Carrega configuração do pipeline
# ---------------------------------------------------------------------------
BASE_DIR    = os.path.dirname(os.path.abspath(__file__))
CONFIG_FILE = os.path.join(BASE_DIR, "config.json")

with open(CONFIG_FILE, "r", encoding="utf-8") as _f:
    _config = json.load(_f)

SCHED_CFG = _config.get("scheduler", {})

LOG_DIR        = os.path.join(BASE_DIR, SCHED_CFG.get("log_dir", "logs"))
LOCK_FILE      = os.path.join(BASE_DIR, SCHED_CFG.get("lock_file", "scheduler.lock"))
MAX_LOG_BYTES  = SCHED_CFG.get("max_log_bytes", 5_242_880)   # 5 MB
LOG_BACKUPS    = SCHED_CFG.get("log_backup_count", 3)

# Frequências
EDITORIAL_HOURS    = SCHED_CFG.get("editorial_interval_hours", 6)
SYNC_HOUR          = SCHED_CFG.get("sync_daily_hour", 7)
SYNC_MINUTE        = SCHED_CFG.get("sync_daily_minute", 0)
WAVES_DOW          = SCHED_CFG.get("waves_day_of_week", "mon")
WAVES_HOUR         = SCHED_CFG.get("waves_hour", 3)
WAVES_MINUTE       = SCHED_CFG.get("waves_minute", 0)

# ---------------------------------------------------------------------------
# Configura Logging (console + arquivo rotativo)
# ---------------------------------------------------------------------------
os.makedirs(LOG_DIR, exist_ok=True)

_log_file = os.path.join(LOG_DIR, "scheduler.log")
_rotating_handler = RotatingFileHandler(
    _log_file,
    maxBytes=MAX_LOG_BYTES,
    backupCount=LOG_BACKUPS,
    encoding="utf-8",
)
_rotating_handler.setFormatter(
    logging.Formatter("[%(asctime)s] %(levelname)s (%(name)s): %(message)s",
                      datefmt="%Y-%m-%d %H:%M:%S")
)

logging.basicConfig(
    level=logging.INFO,
    format="[%(asctime)s] %(levelname)s (%(name)s): %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
    handlers=[logging.StreamHandler(sys.stdout), _rotating_handler],
)
logger = logging.getLogger("Scheduler")


# ---------------------------------------------------------------------------
# Lock File — Proteção Anti-Zumbi
# ---------------------------------------------------------------------------

def _write_lock():
    """Grava o PID do processo atual no arquivo de lock."""
    with open(LOCK_FILE, "w", encoding="utf-8") as lf:
        lf.write(str(os.getpid()))
    logger.info(f"Lock file criado: {LOCK_FILE} (PID: {os.getpid()})")


def _read_lock_pid() -> int | None:
    """Lê o PID do lock file, ou None se não existir."""
    if not os.path.exists(LOCK_FILE):
        return None
    try:
        with open(LOCK_FILE, "r", encoding="utf-8") as lf:
            return int(lf.read().strip())
    except (ValueError, IOError):
        return None


def _is_pid_running(pid: int) -> bool:
    """Verifica se um processo com o PID fornecido ainda está ativo no Windows."""
    try:
        result = subprocess.run(
            ["tasklist", "/FI", f"PID eq {pid}", "/NH", "/FO", "CSV"],
            capture_output=True, text=True, timeout=5
        )
        return str(pid) in result.stdout
    except Exception:
        return False


def _check_no_duplicate():
    """Encerra o programa se outra instância do scheduler já estiver ativa."""
    pid = _read_lock_pid()
    if pid and _is_pid_running(pid):
        logger.error(
            f"[ERRO] INSTANCIA DUPLICADA! O Scheduler ja esta rodando (PID: {pid}).\n"
            f"   Use stop_scheduler.bat para encerrá-lo antes de iniciar uma nova instância.\n"
            f"   Lock file: {LOCK_FILE}"
        )
        sys.exit(1)
    elif pid:
        # Lock stale (processo morreu sem limpar o arquivo)
        logger.warning(f"Lock file stale encontrado (PID {pid} não está ativo). Removendo e continuando.")
        os.remove(LOCK_FILE)


def _cleanup_lock():
    """Remove o lock file ao encerrar."""
    if os.path.exists(LOCK_FILE):
        os.remove(LOCK_FILE)
        logger.info("Lock file removido. Scheduler encerrado com sucesso.")


# ---------------------------------------------------------------------------
# Jobs do Pipeline
# ---------------------------------------------------------------------------

def _run_script(script_name: str, job_id: str = "", dry_run: bool = False, extra_args: list = None):
    """Executa um script Python da pasta automation como subprocesso.
    Detecta automaticamente falhas criticas e dispara alertas via webhook.
    """
    script_path = os.path.join(BASE_DIR, script_name)
    cmd = [sys.executable, script_path]
    if dry_run:
        cmd.append("--dry-run")
    if extra_args:
        cmd.extend(extra_args)

    jid = job_id or script_name
    logger.info(f"[RUN] Iniciando: {script_name} {'(--dry-run)' if dry_run else ''}")
    start = datetime.now()
    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=3600,  # timeout de 1 hora por seguranca
            cwd=BASE_DIR,
        )
        elapsed = (datetime.now() - start).total_seconds()

        # Repassa a saida do script para o log do scheduler
        if result.stdout:
            for line in result.stdout.strip().splitlines():
                logger.info(f"  [stdout] {line}")
        if result.stderr:
            for line in result.stderr.strip().splitlines():
                logger.warning(f"  [stderr] {line}")

        if result.returncode == 0:
            logger.info(f"[OK] {script_name} concluido em {elapsed:.1f}s (exit 0)")
        else:
            err_summary = (result.stderr or result.stdout or "(sem output)")[-300:]
            logger.error(f"[ERRO] {script_name} encerrou com codigo {result.returncode} em {elapsed:.1f}s")

            # --- Alerta: falha de credenciais ---
            cred_keywords = ["credenciais", "WP_APP_PASSWORD", "GROQ_API_KEY",
                             "ANTHROPIC_API_KEY", "401", "403", "Unauthorized"]
            if _ALERTS_AVAILABLE and any(kw.lower() in err_summary.lower() for kw in cred_keywords):
                svc = "WordPress" if "WP_" in err_summary else "API (Groq/Anthropic)"
                alert_credentials_invalid(jid, svc, dry_run=dry_run)

            # --- Alerta: rate limit persistente ---
            elif _ALERTS_AVAILABLE and ("429" in err_summary or "rate limit" in err_summary.lower()):
                api = "Jikan" if "jikan" in script_name.lower() else "API Externa"
                alert_rate_limit(jid, api, dry_run=dry_run)

            # --- Alerta: falha generica ---
            elif _ALERTS_AVAILABLE:
                alert_job_failure(jid, f"exit code {result.returncode}: {err_summary}", dry_run=dry_run)

    except subprocess.TimeoutExpired:
        logger.error(f"[TIMEOUT] {script_name} excedeu 1 hora e foi cancelado.")
        if _ALERTS_AVAILABLE:
            alert_job_timeout(jid, dry_run=dry_run)
    except Exception as exc:
        logger.error(f"[EXCECAO] Falha ao executar {script_name}: {exc}")
        if _ALERTS_AVAILABLE:
            alert_job_failure(jid, str(exc), dry_run=dry_run)


def job_editorial(dry_run: bool = False):
    """Job 1: Pipeline editorial completo (a cada 6h)."""
    logger.info("=" * 55)
    logger.info("  JOB [1/3] -- PIPELINE EDITORIAL (a cada 6h)")
    logger.info("=" * 55)
    _run_script("pipeline.py", job_id="editorial", dry_run=dry_run)


def job_sync_daily(dry_run: bool = False):
    """Job 2: Sincronizacao diaria de animes em exibicao (07:00)."""
    logger.info("=" * 55)
    logger.info("  JOB [2/3] -- SYNC DIARIO ANIMES (07:00 diario)")
    logger.info("=" * 55)
    _run_script("sync_airing_animes.py", job_id="sync_daily", dry_run=dry_run)


def job_waves_evergreen(dry_run: bool = False):
    """Job 3: Importacao semanal de novas waves do MAL (Seg 03:00)."""
    logger.info("=" * 55)
    logger.info("  JOB [3/3] -- WAVES EVERGREEN MAL (Seg 03:00 semanal)")
    logger.info("=" * 55)
    _run_script("import_mal_waves.py", job_id="waves_evergreen", dry_run=dry_run)


# ---------------------------------------------------------------------------
# Comandos CLI auxiliares
# ---------------------------------------------------------------------------

def cmd_status(scheduler):
    """Exibe os jobs agendados e suas próximas execuções sem iniciar o daemon."""
    print("\n" + "=" * 60)
    print("  GEEK AO CUBO — STATUS DO SCHEDULER LOCAL")
    print("=" * 60)
    jobs = scheduler.get_jobs()
    if not jobs:
        print("  Nenhum job registrado.")
    for job in jobs:
        # next_run_time é um __slot__ não inicializado para jobs pendentes (scheduler parado)
        nxt = getattr(job, "next_run_time", None)
        nxt_str = nxt.strftime("%Y-%m-%d %H:%M:%S") if nxt else "(scheduler parado — disponível ao iniciar)"
        print(f"  [{job.id}]")
        print(f"     Nome:         {job.name}")
        print(f"     Próxima exec: {nxt_str}")
        print(f"     Trigger:      {job.trigger}")
        print()
    print(f"  Lock file:  {LOCK_FILE}")
    print(f"  Log file:   {_log_file}")
    print("=" * 60 + "\n")


def cmd_test_run(job_key: str):
    """Dispara um job específico imediatamente em modo dry-run para validação."""
    JOB_MAP = {
        "editorial": job_editorial,
        "sync":      job_sync_daily,
        "waves":     job_waves_evergreen,
    }
    fn = JOB_MAP.get(job_key.lower())
    if not fn:
        print(f"[ERRO] Job '{job_key}' nao encontrado. Use: editorial | sync | waves")
        sys.exit(1)
    print(f"\n[TEST-RUN] Disparando job '{job_key}' em modo --dry-run\n")
    fn(dry_run=True)
    print(f"\n[OK] Test-run de '{job_key}' concluido.\n")


# ---------------------------------------------------------------------------
# Daemon Principal
# ---------------------------------------------------------------------------

def main():
    args = sys.argv[1:]

    # --test-run <job>
    if "--test-run" in args:
        idx = args.index("--test-run")
        if idx + 1 >= len(args):
            print("Uso: python scheduler.py --test-run <editorial|sync|waves>")
            sys.exit(1)
        cmd_test_run(args[idx + 1])
        return

    # --test-alert: envia alerta de teste para o webhook
    if "--test-alert" in args:
        if not _ALERTS_AVAILABLE:
            print("[ERRO] Modulo alerts.py nao encontrado na pasta automation/.")
            sys.exit(1)
        from alerts import _test_alert
        _test_alert()
        return

    try:
        from apscheduler.schedulers.blocking import BlockingScheduler
        from apscheduler.triggers.interval import IntervalTrigger
        from apscheduler.triggers.cron import CronTrigger
    except ImportError:
        logger.error(
            "[ERRO] APScheduler nao instalado!\n"
            "   Execute: pip install apscheduler>=3.10.4"
        )
        sys.exit(1)

    scheduler = BlockingScheduler(timezone="America/Sao_Paulo")

    # --- Registro dos Jobs ---

    # Job 1: Pipeline editorial (a cada N horas)
    scheduler.add_job(
        func=job_editorial,
        trigger=IntervalTrigger(hours=EDITORIAL_HOURS),
        id="editorial",
        name=f"Pipeline Editorial (a cada {EDITORIAL_HOURS}h)",
        max_instances=1,
        coalesce=True,    # Se atrasou, executa 1x (não tenta compensar múltiplas execuções)
        misfire_grace_time=600,  # 10 min de tolerância para início tardio
    )

    # Job 2: Sync diário de animes em exibição
    scheduler.add_job(
        func=job_sync_daily,
        trigger=CronTrigger(hour=SYNC_HOUR, minute=SYNC_MINUTE),
        id="sync_daily",
        name=f"Sync Diário Animes ({SYNC_HOUR:02d}:{SYNC_MINUTE:02d})",
        max_instances=1,
        coalesce=True,
        misfire_grace_time=1800,  # 30 min de tolerância
    )

    # Job 3: Importação semanal de waves evergreen
    scheduler.add_job(
        func=job_waves_evergreen,
        trigger=CronTrigger(
            day_of_week=WAVES_DOW,
            hour=WAVES_HOUR,
            minute=WAVES_MINUTE,
        ),
        id="waves_evergreen",
        name=f"Waves Evergreen MAL ({WAVES_DOW.capitalize()} {WAVES_HOUR:02d}:{WAVES_MINUTE:02d})",
        max_instances=1,
        coalesce=True,
        misfire_grace_time=3600,  # 1h de tolerância
    )

    # --status: exibe jobs e sai (antes de iniciar o daemon)
    if "--status" in args:
        cmd_status(scheduler)
        sys.exit(0)

    # Verificação de instância duplicada
    _check_no_duplicate()
    _write_lock()

    # Banner de inicialização
    logger.info("=" * 60)
    logger.info("  GEEK AO CUBO — SCHEDULER LOCAL INICIADO")
    logger.info("=" * 60)
    logger.info(f"  PID:          {os.getpid()}")
    logger.info(f"  Timezone:     America/Sao_Paulo")
    logger.info(f"  Lock file:    {LOCK_FILE}")
    logger.info(f"  Log file:     {_log_file}")
    logger.info("  Jobs registrados:")
    for job in scheduler.get_jobs():
        nxt = job.next_run_time
        nxt_str = nxt.strftime("%Y-%m-%d %H:%M:%S") if nxt else "Pendente"
        logger.info(f"    [{job.id}] {job.name} → próxima: {nxt_str}")
    logger.info("=" * 60)
    logger.info("  Pressione Ctrl+C para encerrar o daemon.")
    logger.info("=" * 60 + "\n")

    try:
        scheduler.start()
    except (KeyboardInterrupt, SystemExit):
        logger.info("\nSinal de encerramento recebido. Parando o Scheduler...")
        scheduler.shutdown(wait=False)
    finally:
        _cleanup_lock()


if __name__ == "__main__":
    main()
