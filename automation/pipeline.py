#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Orquestrador do Pipeline de Automação
Executa as Tasks 3.1 → 3.2 → 3.3 → 3.4 em sequência como um único comando.

Uso:
  python pipeline.py                         # Roda pipeline completo
  python pipeline.py --dry-run               # Simula sem persistir/publicar
  python pipeline.py --limit=3               # Processa max 3 artigos por etapa
  python pipeline.py --skip-scraper          # Pula coleta (usa fila existente)
  python pipeline.py --skip-panorama         # Pula Groq (usa panoramas existentes)
  python pipeline.py --skip-editorial        # Pula Sonnet (usa editoriais existentes)
  python pipeline.py --only-publish          # Apenas publica o que já está na fila

@author  Antigravity AI Designer
@version 1.0.0
@since   2026-05-26
"""

import sys
import logging

logging.basicConfig(
    level=logging.INFO,
    format="[%(asctime)s] %(levelname)s: %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("Pipeline")


def parse_args() -> dict:
    """Faz parsing simples dos argumentos de linha de comando."""
    args = sys.argv[1:]
    return {
        "dry_run":         "--dry-run"         in args,
        "skip_scraper":    "--skip-scraper"    in args or "--only-publish" in args,
        "skip_panorama":   "--skip-panorama"   in args or "--only-publish" in args,
        "skip_editorial":  "--skip-editorial"  in args or "--only-publish" in args,
        "limit": next(
            (int(a.split("=")[1]) for a in args if a.startswith("--limit=")), 0
        ),
    }


def main():
    opts = parse_args()

    logger.info("=" * 60)
    logger.info("  GEEK AO CUBO — PIPELINE DE AUTOMAÇÃO COMPLETO")
    logger.info("=" * 60)
    logger.info(f"  dry-run       : {opts['dry_run']}")
    logger.info(f"  limit         : {opts['limit'] or 'sem limite'}")
    logger.info(f"  skip_scraper  : {opts['skip_scraper']}")
    logger.info(f"  skip_panorama : {opts['skip_panorama']}")
    logger.info(f"  skip_editorial: {opts['skip_editorial']}")
    logger.info("=" * 60)

    # ------------------------------------------------------------------
    # ETAPA 1 — Coleta e Raspagem (Task 3.1)
    # ------------------------------------------------------------------
    if not opts["skip_scraper"]:
        logger.info("\n📡 ETAPA 1/4 — Coleta RSS (scraper.py)")
        try:
            from scraper import AnimeNewsScraper
            scraper = AnimeNewsScraper()
            scraper.run(dry_run=opts["dry_run"])
        except Exception as e:
            logger.error(f"Etapa 1 falhou: {e}. Continuando pipeline...")
    else:
        logger.info("\n⏭️  ETAPA 1/4 — Coleta RSS ignorada (--skip-scraper)")

    # ------------------------------------------------------------------
    # ETAPA 2 — Panorama Factual via Groq (Task 3.2)
    # ------------------------------------------------------------------
    if not opts["skip_panorama"]:
        logger.info("\n🤖 ETAPA 2/4 — Panorama Factual (groq_panorama.py)")
        try:
            from groq_panorama import GroqPanorama
            groq_pipeline = GroqPanorama()
            groq_pipeline.run(dry_run=opts["dry_run"], limit=opts["limit"])
        except Exception as e:
            logger.error(f"Etapa 2 falhou: {e}. Continuando pipeline...")
    else:
        logger.info("\n⏭️  ETAPA 2/4 — Panorama Groq ignorado (--skip-panorama)")

    # ------------------------------------------------------------------
    # ETAPA 3 — Artigo Editorial via Claude Sonnet (Task 3.3)
    # ------------------------------------------------------------------
    if not opts["skip_editorial"]:
        logger.info("\n✍️  ETAPA 3/4 — Editorial Humanizado (editorial_sonnet.py)")
        try:
            from editorial_sonnet import EditorialSonnet
            editorial_pipeline = EditorialSonnet()
            editorial_pipeline.run(dry_run=opts["dry_run"], limit=opts["limit"])
        except Exception as e:
            logger.error(f"Etapa 3 falhou: {e}. Continuando pipeline...")
    else:
        logger.info("\n⏭️  ETAPA 3/4 — Editorial Sonnet ignorado (--skip-editorial)")

    # ------------------------------------------------------------------
    # ETAPA 4 — Publicação WordPress REST API (Task 3.4)
    # ------------------------------------------------------------------
    logger.info("\n🚀 ETAPA 4/4 — Publicação WordPress (publisher.py)")
    try:
        from publisher import WPPublisher
        publisher = WPPublisher()
        publisher.run(dry_run=opts["dry_run"], limit=opts["limit"])
    except Exception as e:
        logger.error(f"Etapa 4 falhou: {e}")

    logger.info("\n" + "=" * 60)
    logger.info("  PIPELINE COMPLETO — todas as etapas finalizadas.")
    logger.info("=" * 60)


if __name__ == "__main__":
    main()
