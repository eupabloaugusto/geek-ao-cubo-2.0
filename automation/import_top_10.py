#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Script rápido para importar os 10 animes mais populares do MyAnimeList.
Serve como teste para validação do modelo 'Raw Payload' da arquitetura sem filtros.
"""

import sys
import time
import requests
import logging
from import_mal_current_season import MALImporterSeason

logging.basicConfig(
	level=logging.INFO,
	format="[%(asctime)s] %(levelname)s: %(message)s",
	datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("Top10Importer")

def fetch_top_10_animes():
	logger.info("Buscando os Top 10 animes mais populares da Jikan API...")
	api_url = "https://api.jikan.moe/v4/top/anime"
	try:
		resp = requests.get(api_url, params={"filter": "bypopularity", "limit": 10}, timeout=15)
		if resp.status_code == 200:
			data = resp.json()
			return data.get("data", [])
		else:
			logger.error(f"Falha na API Jikan: HTTP {resp.status_code}")
	except Exception as e:
		logger.error(f"Exceção ao buscar os animes populares: {e}")
	return []

def main():
	importer = MALImporterSeason()
	if not importer.wp_url:
		logger.error("Credenciais do WordPress não encontradas no .env.")
		sys.exit(1)

	top_10 = fetch_top_10_animes()
	
	if not top_10:
		logger.error("Nenhum anime retornado.")
		sys.exit(1)

	success_count = 0
	fail_count = 0

	for i, anime_data in enumerate(top_10, start=1):
		logger.info(f"[{i}/10] Importando anime top popular...")
		success = importer.import_anime(anime_data, dry_run=False, update=False)
		if success:
			success_count += 1
		else:
			fail_count += 1

		time.sleep(importer.JIKAN_DELAY)

	logger.info(
		f"=== IMPORTAÇÃO TOP 10 CONCLUÍDA ===\n"
		f"  Sucessos: {success_count}\n"
		f"  Falhas: {fail_count}"
	)

if __name__ == "__main__":
	main()
