#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Módulo Editorial Humanizado (Task 3.3)
Lê a fila de panoramas factuais (panorama_queue.json) gerada pelo Groq (Task 3.2),
chama a API do Claude Sonnet para expandir cada panorama em um artigo editorial
premium, humanizado e em HTML semântico puro, e grava a fila final
(editorial_queue.json) para consumo do Publicador da Task 3.4.

@author  Antigravity AI Designer
@version 1.0.0
@since   2026-05-26
"""

import os
import json
import time
import logging
import re
from datetime import datetime
import anthropic
from dotenv import load_dotenv

# ---------------------------------------------------------------------------
# Configuração de Logging
# ---------------------------------------------------------------------------
logging.basicConfig(
    level=logging.INFO,
    format="[%(asctime)s] %(levelname)s: %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("EditorialSonnet")

load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), ".env"))


# ---------------------------------------------------------------------------
# System Prompt — Tom de voz do Maro (conforme prompt_editorial_sonnet.md)
# ---------------------------------------------------------------------------
SYSTEM_PROMPT = """Você é o Maro, editor-chefe e escritor do portal de anime "Geek ao Cubo" (modomaratona.com). Seu trabalho é ler um panorama factual fornecido em português (que resume notícias e artigos estrangeiros de anime) e expandi-lo em um post de blog premium, envolvente e altamente otimizado.

Siga rigorosamente as diretrizes abaixo:

1. TOM E VOZ:
- Fale diretamente com o leitor geek brasileiro. Adote um tom apaixonado, enérgico, bem-humorado e especialista.
- Use gírias e jargões da comunidade (hype, drop, filler, canon, shippar) de forma fluida e natural.
- Evite tom formal, corporativo, acadêmico ou robótico. Não use introduções pomposas ou resumos finais mecânicos.

2. RITMO E ESCRITA:
- Varie o tamanho das frases. Use frases muito curtas e diretas para criar impacto e dinamismo.
- Aplique a técnica "Show, don't tell". Descreva detalhes visuais, sentimentos e ações concretas.
- Converse com o leitor com perguntas retóricas rápidas (ex: "E aí, pronto pro choro?").

3. PALAVRAS PROIBIDAS (ELIMINE 100%):
- "Crucial", "fundamental", "essencial", "além disso" (como transição padrão), "ademais", "outrossim", "vale ressaltar", "é importante destacar", "jornada" (substitua por saga/aventura), "desvendar" (substitua por descobrir/sacar), "em resumo", "concluindo", "em suma".

4. FORMATO DE SAÍDA:
- Retorne APENAS o código HTML semântico limpo, pronto para inserção direta no banco de dados.
- NÃO envolva a resposta em blocos de código markdown (como ```html ... ```). Retorne o texto cru.
- Use <h2> e <h3> para seções. Parágrafos (<p>) devem ter no máximo 3 ou 4 linhas. Use <strong> e <blockquote> para citações do anime.
- Sugira links internos em âncoras <a> usando caminhos relativos de slugs lógicos (ex: "/animes/frieren/").

Seu texto deve ser autêntico, memorável e irresistível para qualquer fã de anime. Escreva como um redator humano genial."""


# ---------------------------------------------------------------------------
# Classe Principal
# ---------------------------------------------------------------------------
class EditorialSonnet:
    """
    Consome panorama_queue.json, processa cada panorama via Claude Sonnet
    e grava os artigos prontos em editorial_queue.json para a Task 3.4.
    """

    MODEL = "claude-sonnet-4-5"

    # Tokens de saída: artigos completos de 400–800 palavras em HTML
    MAX_OUTPUT_TOKENS = 2048

    # Pausa entre chamadas para respeitar rate limits da API Anthropic
    RATE_LIMIT_SLEEP = 3.0  # segundos

    def __init__(self, config_path: str = "config.json"):
        """Inicializa o cliente Anthropic e carrega os caminhos de dados."""
        api_key = os.getenv("ANTHROPIC_API_KEY")
        if not api_key:
            raise EnvironmentError(
                "Variável de ambiente ANTHROPIC_API_KEY não encontrada. "
                "Adicione ao arquivo automation/.env: ANTHROPIC_API_KEY=sk-ant-..."
            )

        self.client = anthropic.Anthropic(api_key=api_key)

        base_dir   = os.path.dirname(os.path.abspath(__file__))
        config_file = os.path.join(base_dir, config_path)

        with open(config_file, "r", encoding="utf-8") as f:
            config = json.load(f)

        self.panorama_path  = os.path.join(base_dir, config["paths"].get(
            "panorama_queue_file", "panorama_queue.json"
        ))
        self.editorial_path = os.path.join(base_dir, config["paths"].get(
            "editorial_queue_file", "editorial_queue.json"
        ))

        logger.info(f"EditorialSonnet inicializado | Modelo: {self.MODEL}")
        logger.info(f"Fila de entrada : {self.panorama_path}")
        logger.info(f"Fila de saída   : {self.editorial_path}")

    # ------------------------------------------------------------------
    # Persistência
    # ------------------------------------------------------------------

    def _load_json(self, path: str) -> list:
        if os.path.exists(path):
            try:
                with open(path, "r", encoding="utf-8") as f:
                    return json.load(f)
            except Exception as e:
                logger.error(f"Erro ao carregar {path}: {e}")
        return []

    def _save_json(self, path: str, data: list) -> None:
        try:
            with open(path, "w", encoding="utf-8") as f:
                json.dump(data, f, ensure_ascii=False, indent=2)
        except Exception as e:
            logger.error(f"Erro ao salvar {path}: {e}")

    # ------------------------------------------------------------------
    # Geração de slug SEO
    # ------------------------------------------------------------------

    @staticmethod
    def _slugify(text: str) -> str:
        """Converte um título em slug URL-friendly para SEO."""
        replacements = {
            "á": "a", "à": "a", "ã": "a", "â": "a", "ä": "a",
            "é": "e", "ê": "e", "ë": "e",
            "í": "i", "î": "i", "ï": "i",
            "ó": "o", "õ": "o", "ô": "o", "ö": "o",
            "ú": "u", "û": "u", "ü": "u",
            "ç": "c", "ñ": "n",
        }
        slug = text.lower()
        for char, replacement in replacements.items():
            slug = slug.replace(char, replacement)
        slug = re.sub(r"[^a-z0-9\s-]", "", slug)
        slug = re.sub(r"[\s]+", "-", slug.strip())
        slug = re.sub(r"-+", "-", slug)
        return slug[:80]  # Limita a 80 chars (boas práticas SEO)

    # ------------------------------------------------------------------
    # Construção da mensagem de usuário
    # ------------------------------------------------------------------

    def _build_user_message(self, panorama: dict) -> str:
        """
        Monta a mensagem de usuário enviada ao Sonnet com o contexto
        completo do panorama factual para expansão editorial.
        """
        tags = ", ".join(panorama.get("tags_sugeridas", []))
        return (
            f"CATEGORIA EDITORIAL: {panorama.get('categoria_sugerida', 'NOTÍCIA')}\n"
            f"TAGS SUGERIDAS: {tags}\n"
            f"TÍTULO SUGERIDO: {panorama.get('titulo_sugerido', '')}\n"
            f"FONTE ORIGINAL: {panorama.get('original_source', '')} "
            f"({panorama.get('original_language', 'en').upper()})\n"
            f"URL DE REFERÊNCIA: {panorama.get('original_url', '')}\n\n"
            f"PANORAMA FACTUAL:\n{panorama.get('panorama', '')}\n\n"
            "Expanda o panorama acima em um artigo editorial completo para o Geek ao Cubo. "
            "Siga rigorosamente o tom de voz, as regras de HTML e as diretrizes do system prompt."
        )

    # ------------------------------------------------------------------
    # Chamada à API Anthropic
    # ------------------------------------------------------------------

    def _call_sonnet(self, panorama: dict) -> str | None:
        """
        Envia o panorama ao Claude Sonnet e retorna o HTML do artigo gerado.
        Retorna None em caso de falha.
        """
        user_message = self._build_user_message(panorama)

        try:
            response = self.client.messages.create(
                model=self.MODEL,
                max_tokens=self.MAX_OUTPUT_TOKENS,
                system=SYSTEM_PROMPT,
                messages=[
                    {"role": "user", "content": user_message}
                ],
            )

            html_content = response.content[0].text.strip()

            # Sanitização: remove blocos markdown caso o modelo os inclua
            html_content = re.sub(r"^```[a-z]*\n?", "", html_content, flags=re.MULTILINE)
            html_content = re.sub(r"\n?```$", "", html_content, flags=re.MULTILINE)
            html_content = html_content.strip()

            # Valida se a resposta parece HTML (deve ter ao menos uma tag)
            if not re.search(r"<[a-z]+", html_content, re.IGNORECASE):
                logger.warning("Resposta do Sonnet não parece conter HTML válido.")
                logger.debug(f"Resposta: {html_content[:300]}")
                return None

            logger.info(
                f"  📝 Artigo gerado | "
                f"~{len(html_content.split())} palavras | "
                f"input={response.usage.input_tokens} / "
                f"output={response.usage.output_tokens} tokens"
            )

            return html_content

        except Exception as e:
            logger.error(f"Erro na chamada ao Sonnet: {e}")
            return None

    # ------------------------------------------------------------------
    # Pipeline principal
    # ------------------------------------------------------------------

    def run(self, dry_run: bool = False, limit: int = 0) -> None:
        """
        Executa o pipeline editorial completo.

        Args:
            dry_run: Se True, não persiste resultados no disco.
            limit:   Número máximo de artigos a processar (0 = todos).
        """
        logger.info("=== INICIANDO PIPELINE EDITORIAL (CLAUDE SONNET) ===")

        panorama_queue  = self._load_json(self.panorama_path)
        editorial_queue = self._load_json(self.editorial_path)

        # Filtra apenas panoramas prontos e ainda não publicados
        pending = [
            p for p in panorama_queue
            if p.get("status") == "panorama_pronto"
        ]

        if not pending:
            logger.info("Nenhum panorama pronto encontrado na fila. Finalizando.")
            return

        if limit > 0:
            pending = pending[:limit]

        logger.info(f"{len(pending)} panorama(s) prontos para expansão editorial.")

        success_count = 0
        fail_count    = 0

        for i, panorama in enumerate(pending, start=1):
            titulo    = panorama.get("titulo_sugerido", "Sem título")
            fonte     = panorama.get("original_source", "?")
            url_orig  = panorama.get("original_url", "")

            logger.info(f"[{i}/{len(pending)}] Expandindo [{fonte}]: '{titulo}'")

            html_content = self._call_sonnet(panorama)

            if html_content:
                slug = self._slugify(titulo)

                # Constrói o registro final pronto para o Publicador (Task 3.4)
                editorial_record = {
                    # Metadados de rastreabilidade
                    "original_title":     panorama.get("original_title", ""),
                    "original_url":       url_orig,
                    "original_source":    fonte,
                    "original_language":  panorama.get("original_language", "en"),
                    "scraped_at":         panorama.get("scraped_at", ""),
                    "panorama_at":        panorama.get("panorama_at", ""),
                    "editorial_at":       datetime.now().strftime("%Y-%m-%d %H:%M:%S"),

                    # Status do pipeline
                    "status": "editorial_pronto",

                    # Dados para publicação no WordPress
                    "wp_post": {
                        "title":      titulo,
                        "slug":       slug,
                        "content":    html_content,
                        "status":     "draft",          # Publicado como rascunho para revisão humana
                        "categories": [panorama.get("categoria_sugerida", "NOTÍCIA")],
                        "tags":       panorama.get("tags_sugeridas", []),
                        "meta": {
                            "fonte_original": url_orig,
                            "fonte_nome":     fonte,
                        },
                    },
                }

                editorial_queue.append(editorial_record)

                # Atualiza status do panorama para evitar reprocessamento
                panorama["status"] = "editorial_pronto"

                logger.info(f"  ✅ Artigo pronto | slug: /{slug}/")
                success_count += 1

            else:
                panorama["status"] = "editorial_falhou"
                fail_count += 1
                logger.warning(f"  ❌ Falha ao expandir panorama: '{titulo}'")

            # Rate limiting entre chamadas
            if i < len(pending):
                logger.debug(f"Aguardando {self.RATE_LIMIT_SLEEP}s antes da próxima chamada...")
                time.sleep(self.RATE_LIMIT_SLEEP)

        # Persiste resultados
        if not dry_run:
            self._save_json(self.panorama_path,  panorama_queue)
            self._save_json(self.editorial_path, editorial_queue)
            logger.info("Dados persistidos em disco.")
        else:
            logger.info("[DRY-RUN] Nenhum arquivo foi modificado.")

        logger.info(
            f"=== PIPELINE EDITORIAL FINALIZADO | "
            f"✅ {success_count} artigo(s) | ❌ {fail_count} falha(s) ==="
        )


# ---------------------------------------------------------------------------
# Execução direta via CLI
# ---------------------------------------------------------------------------
if __name__ == "__main__":
    import sys

    dry = "--dry-run" in sys.argv
    lim = 0

    for arg in sys.argv:
        if arg.startswith("--limit="):
            try:
                lim = int(arg.split("=")[1])
            except ValueError:
                logger.warning("Valor inválido para --limit. Processando todos os pendentes.")

    try:
        pipeline = EditorialSonnet()
        pipeline.run(dry_run=dry, limit=lim)
    except EnvironmentError as e:
        logger.critical(str(e))
        sys.exit(1)
    except Exception as e:
        logger.critical(f"Falha crítica na execução: {e}")
        sys.exit(1)
