#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Script para limpar todo o catálogo atual de animes do WordPress (CPT 'anime').
Remove também as imagens destacadas (featured_media) associadas para não
deixar arquivos residuais no banco ou na pasta wp-content/uploads.
"""

import os
import sys
import logging
import requests
from base64 import b64encode
from dotenv import load_dotenv

logging.basicConfig(
	level=logging.INFO,
	format="[%(asctime)s] %(levelname)s: %(message)s",
	datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("DeleteCatalog")

class CatalogCleaner:
	def __init__(self):
		self.base_dir = os.path.dirname(os.path.abspath(__file__))
		load_dotenv(dotenv_path=os.path.join(self.base_dir, ".env"))

		self.wp_url      = os.getenv("WP_BASE_URL", "").rstrip("/")
		self.wp_user     = os.getenv("WP_USERNAME", "")
		self.wp_password = os.getenv("WP_APP_PASSWORD", "")

		if not all([self.wp_url, self.wp_user, self.wp_password]):
			logger.error("Credenciais ausentes no arquivo .env!")
			sys.exit(1)

		credentials  = f"{self.wp_user}:{self.wp_password}"
		encoded_auth = b64encode(credentials.encode("utf-8")).decode("utf-8")
		self.headers = {
			"Authorization": f"Basic {encoded_auth}",
			"Content-Type":  "application/json",
		}
		self.api_base = f"{self.wp_url}/wp-json/wp/v2"

	def delete_all_animes(self):
		logger.info("=== INICIANDO LIMPEZA DO CATÁLOGO DE ANIMES ===")
		
		total_deleted = 0
		media_deleted = 0

		while True:
			# Busca a primeira página de 100 animes
			logger.info("Buscando lote de até 100 animes...")
			resp = requests.get(
				f"{self.api_base}/anime",
				params={"per_page": 100, "status": "any"},
				headers=self.headers,
				timeout=15
			)

			if resp.status_code != 200:
				logger.error(f"Erro ao buscar animes: HTTP {resp.status_code}. Abortando.")
				break

			animes = resp.json()
			
			if not animes:
				logger.info("Nenhum anime encontrado. O catálogo está limpo!")
				break

			logger.info(f"Processando remoção de {len(animes)} animes...")
			
			for anime in animes:
				anime_id = anime.get("id")
				anime_title = anime.get("title", {}).get("rendered", "Sem Título")
				featured_media = anime.get("featured_media")

				# 1. Deletar Media (Se houver)
				if featured_media and featured_media > 0:
					logger.info(f"  Deletando capa residual (Media ID: {featured_media}) do anime '{anime_title}'...")
					try:
						res_media = requests.delete(
							f"{self.api_base}/media/{featured_media}?force=true",
							headers=self.headers,
							timeout=15
						)
						if res_media.status_code in (200, 201):
							media_deleted += 1
						else:
							logger.warning(f"    ⚠️ Erro HTTP {res_media.status_code} ao deletar media.")
					except Exception as e:
						logger.error(f"    ❌ Erro na deleção da mídia: {e}")

				# 2. Deletar Anime
				logger.info(f"  Deletando anime '{anime_title}' (ID: {anime_id})...")
				try:
					res_anime = requests.delete(
						f"{self.api_base}/anime/{anime_id}?force=true",
						headers=self.headers,
						timeout=15
					)
					if res_anime.status_code in (200, 201):
						total_deleted += 1
					else:
						logger.error(f"    ❌ Erro HTTP {res_anime.status_code} ao deletar anime.")
				except Exception as e:
					logger.error(f"    ❌ Erro na deleção do anime: {e}")

		logger.info(f"=== LIMPEZA CONCLUÍDA ===")
		logger.info(f"  Animes removidos: {total_deleted}")
		logger.info(f"  Imagens residuais removidas: {media_deleted}")

if __name__ == "__main__":
	cleaner = CatalogCleaner()
	cleaner.delete_all_animes()
