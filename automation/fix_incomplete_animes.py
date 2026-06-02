#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Script para percorrer todos os animes do banco de dados do WordPress e forçar
a atualização de informações incompletas (trailers ausentes, episódios, pontuações,
e temporadas/franquias via prequels).
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
logger = logging.getLogger("FixIncompleteAnimes")

def get_all_wp_animes(importer: MALImporterSeason):
	animes = []
	page = 1
	logger.info("Buscando lista de animes no WordPress...")
	while True:
		try:
			resp = requests.get(
				f"{importer.api_base}/anime",
				params={"per_page": 50, "page": page, "_fields": "id,title,acf"},
				headers=importer.headers,
				timeout=15
			)
			if resp.status_code == 400: # Page out of bounds
				break
			if resp.status_code == 200:
				data = resp.json()
				if not data:
					break
				animes.extend(data)
				logger.info(f"  Página {page} carregada. {len(animes)} animes encontrados até agora.")
				page += 1
			else:
				logger.error(f"Erro HTTP {resp.status_code} ao buscar animes do WP.")
				break
		except Exception as e:
			logger.error(f"Exceção ao buscar animes: {e}")
			break
	return animes

def main():
	importer = MALImporterSeason()
	if not importer.wp_url:
		logger.error("Credenciais do WordPress não encontradas no .env.")
		sys.exit(1)

	wp_animes = get_all_wp_animes(importer)
	logger.info(f"Total de {len(wp_animes)} animes encontrados no catálogo.")

	success_count = 0
	fail_count = 0

	for i, wp_anime in enumerate(wp_animes, start=1):
		wp_id = wp_anime.get("id")
		wp_title = wp_anime.get("title", {}).get("rendered", "Desconhecido")
		acf = wp_anime.get("acf", {})
		mal_id = acf.get("anime_id_mal")

		if not mal_id:
			logger.warning(f"[{i}/{len(wp_animes)}] Pulando anime '{wp_title}' (ID {wp_id}) - sem MAL ID.")
			continue

		logger.info(f"[{i}/{len(wp_animes)}] Corrigindo anime: '{wp_title}' (MAL ID: {mal_id})")

		# Busca os dados fresquinhos do anime no Jikan
		try:
			logger.info(f"  Buscando payload completo do Jikan para MAL ID {mal_id}...")
			jikan_resp = requests.get(f"{importer.JIKAN_BASE_URL}/anime/{mal_id}", timeout=15)
			if jikan_resp.status_code == 429:
				logger.warning("  Rate limit do Jikan atingido. Aguardando 5s...")
				time.sleep(5)
				jikan_resp = requests.get(f"{importer.JIKAN_BASE_URL}/anime/{mal_id}", timeout=15)

			if jikan_resp.status_code == 200:
				anime_data = jikan_resp.json().get("data", {})
				has_media = bool(wp_anime.get("featured_media"))
				
				# Reutiliza a lógica robusta de importação/atualização (True para update)
				success = importer.import_anime(anime_data, dry_run=False, update=True, has_featured_media=has_media)
				if success:
					success_count += 1
				else:
					fail_count += 1
			else:
				logger.error(f"  ❌ Erro Jikan HTTP {jikan_resp.status_code}")
				fail_count += 1
		except Exception as e:
			logger.error(f"  ❌ Exceção ao atualizar anime: {e}")
			fail_count += 1

		time.sleep(importer.JIKAN_DELAY)

	logger.info(
		f"=== CORREÇÃO EM MASSA CONCLUÍDA ===\n"
		f"  Total Processado: {len(wp_animes)}\n"
		f"  Corrigidos/Atualizados: {success_count}\n"
		f"  Falhas: {fail_count}"
	)

if __name__ == "__main__":
	main()
