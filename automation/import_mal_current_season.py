#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Importador Jikan MAL Current Season
Importa os animes da Temporada Atual do MyAnimeList para o WordPress local
cadastrando no Custom Post Type 'anime', preenchendo ACF e taxonomias.
Inclui também importação de trailers embutidos, episódios e temporadas.

Uso:
  python import_mal_current_season.py --dry-run   (Simulação rápida)
  python import_mal_current_season.py             (Execução real completa)
"""

import os
import sys
import time
import re
import json
import argparse
import logging
import requests
from base64 import b64encode
from dotenv import load_dotenv

logging.basicConfig(
	level=logging.INFO,
	format="[%(asctime)s] %(levelname)s: %(message)s",
	datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("MALImporterSeason")


class MALImporterSeason:
	JIKAN_BASE_URL = "https://api.jikan.moe/v4"
	JIKAN_DELAY    = 2.0

	def __init__(self):
		load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), ".env"))

		self.wp_url      = os.getenv("WP_BASE_URL", "").rstrip("/")
		self.wp_user     = os.getenv("WP_USERNAME", "")
		self.wp_password = os.getenv("WP_APP_PASSWORD", "")

		if not all([self.wp_url, self.wp_user, self.wp_password]):
			logger.warning("Credenciais do WordPress não configuradas totalmente no arquivo .env!")

		credentials  = f"{self.wp_user}:{self.wp_password}"
		encoded_auth = b64encode(credentials.encode("utf-8")).decode("utf-8")
		self.headers = {
			"Authorization": f"Basic {encoded_auth}",
			"Content-Type":  "application/json",
		}
		self.api_base = f"{self.wp_url}/wp-json/wp/v2"

	def _get_or_create_term(self, taxonomy: str, name: str) -> int | None:
		if not self.wp_url: return None
		try:
			resp = requests.get(
				f"{self.api_base}/{taxonomy}",
				params={"search": name, "_fields": "id,name"},
				headers=self.headers,
				timeout=10,
			)
			if resp.status_code == 200:
				terms = resp.json()
				for term in terms:
					if term["name"].lower() == name.lower():
						return term["id"]
			resp = requests.post(
				f"{self.api_base}/{taxonomy}",
				json={"name": name},
				headers=self.headers,
				timeout=10,
			)
			if resp.status_code in (200, 201):
				return resp.json().get("id")
		except Exception as e:
			logger.error(f"  ❌ Exceção ao resolver termo '{name}' em '{taxonomy}': {e}")
		return None

	def _upload_media(self, image_url: str, title: str) -> int | None:
		if not self.wp_url or not image_url: return None
		slug = title.lower().replace(" ", "-").replace(":", "").replace("/", "")
		slug = "".join(c for c in slug if c.isalnum() or c == "-").strip("-")
		filename = f"capa-{slug}.jpg"
		try:
			resp = requests.get(image_url, timeout=10)
			if resp.status_code != 200: return None
			media_headers = {
				"Authorization":       self.headers["Authorization"],
				"Content-Disposition": f'attachment; filename="{filename}"',
				"Content-Type":        resp.headers.get("Content-Type", "image/jpeg"),
			}
			upload_resp = requests.post(
				f"{self.api_base}/media",
				data=resp.content,
				headers=media_headers,
				timeout=20,
			)
			if upload_resp.status_code in (200, 201):
				return upload_resp.json().get("id")
		except Exception as e:
			logger.error(f"  ❌ Erro no upload de imagem de {title}: {e}")
		return None

	def _get_anime_id_by_slug(self, slug: str) -> int | None:
		if not self.wp_url: return None
		try:
			resp = requests.get(
				f"{self.api_base}/anime",
				params={"slug": slug, "status": "any", "_fields": "id,slug"},
				headers=self.headers,
				timeout=10,
			)
			if resp.status_code == 200:
				animes = resp.json()
				if animes: return int(animes[0]["id"])
		except Exception:
			pass
		return None

	def _extract_broadcast_utc(self, anime_data: dict) -> tuple[str, str]:
		broadcast = anime_data.get("broadcast") or {}
		broadcast_time = broadcast.get("time") or ""
		broadcast_day  = broadcast.get("day")  or ""
		horario_utc = ""
		if broadcast_time:
			try:
				h, m = map(int, broadcast_time.split(":"))
				h_utc = (h - 9) % 24
				horario_utc = f"{h_utc:02d}:{m:02d}"
			except Exception:
				pass
		return horario_utc, broadcast_day

	def _fetch_anilist_banner(self, mal_id: int) -> str:
		query = """
		query ($idMal: Int) {
		  Media(idMal: $idMal, type: ANIME) {
		    bannerImage
		  }
		}
		"""
		try:
			resp = requests.post(
				"https://graphql.anilist.co",
				json={"query": query, "variables": {"idMal": mal_id}},
				headers={"Content-Type": "application/json", "Accept": "application/json"},
				timeout=10,
			)
			if resp.status_code == 200:
				return resp.json().get("data", {}).get("Media", {}).get("bannerImage") or ""
		except Exception:
			pass
		return ""

	def _map_source(self, source_raw: str) -> str:
		if not source_raw: return "other"
		s = source_raw.lower().strip()
		if s in ("manga", "4-koma manga", "web manga", "manga adaptation"): return "manga"
		elif s in ("light novel", "light_novel", "novel"): return "light_novel"
		elif s in ("visual novel", "visual_novel"): return "visual_novel"
		elif s in ("original",): return "original"
		elif s in ("game", "card game", "video game"): return "game"
		return "other"

	def _map_rating(self, rating_raw: str) -> str:
		if not rating_raw: return "g"
		rating = rating_raw.lower()
		if "pg-13" in rating: return "pg13"
		elif "pg" in rating: return "pg"
		elif "r - 17+" in rating: return "r17"
		elif "r+" in rating: return "r"
		elif "rx" in rating or "hentai" in rating: return "rx"
		return "g"

	def _map_status_exibicao(self, status_raw: str) -> str:
		if not status_raw: return "Pausado"
		status = status_raw.lower()
		if "currently airing" in status: return "Em Exibição"
		elif "finished airing" in status: return "Finalizado"
		elif "not yet aired" in status: return "Brevemente"
		return "Pausado"

	def _map_genre_ptbr(self, genre_en: str) -> str:
		TRADUZIR = {
			"action": "Ação", "adventure": "Aventura", "comedy": "Comédia", "drama": "Drama",
			"fantasy": "Fantasia", "horror": "Terror", "mystery": "Mistério", "romance": "Romance",
			"sci-fi": "Ficção Científica", "science fiction": "Ficção Científica", "supernatural": "Sobrenatural",
			"sports": "Esportes", "psychological": "Psicológico", "historical": "Histórico",
			"military": "Militar", "school": "Escola", "music": "Música", "award winning": "Premiado",
			"suspense": "Suspense", "avant garde": "Vanguarda", "mythology": "Mitologia",
			"racing": "Corrida", "martial arts": "Artes Marciais", "super power": "Superpoderes",
			"vampire": "Vampiro", "space": "Espaço",
		}
		return TRADUZIR.get(genre_en.lower(), genre_en)
		
	def _build_alt_titles(self, anime_data: dict, display_title: str) -> str:
		alts = []
		raw_title = anime_data.get("title", "")
		if raw_title and raw_title != display_title:
			alts.append(raw_title)
		for t in anime_data.get("titles", []):
			val = t.get("title", "").strip()
			if val and val != display_title and val not in alts:
				alts.append(val)
		return ", ".join(alts)

	def fetch_season_animes(self) -> list:
		animes = []
		page = 1

		logger.info(f"Buscando animes da Temporada Atual via Jikan API...")

		while True:
			logger.info(f"Requisitando página {page}...")
			api_url = f"{self.JIKAN_BASE_URL}/seasons/now"

			try:
				resp = requests.get(api_url, params={"page": page}, timeout=15)
				if resp.status_code == 429:
					logger.warning("  Rate limit atingido (HTTP 429). Aguardando 5 segundos...")
					time.sleep(5.0)
					continue

				if resp.status_code != 200:
					logger.error(f"  Falha na Jikan API: HTTP {resp.status_code}.")
					break

				data = resp.json()
				page_data = data.get("data", [])
				pagination = data.get("pagination", {})

				if not page_data:
					break

				animes.extend(page_data)
				logger.info(f"  Página {page} carregada com sucesso. Acumulado: {len(animes)} animes.")

				if not pagination.get("has_next_page"):
					break

				time.sleep(self.JIKAN_DELAY)
				page += 1

			except Exception as e:
				logger.error(f"  ❌ Erro de conexão com a Jikan API na página {page}: {e}")
				break

		return animes

	def _get_or_create_temporada(self, season: str, year: int) -> int | None:
		if not season or not year: return None
		season_map = {
			"spring": "Primavera",
			"summer": "Verão",
			"fall": "Outono",
			"winter": "Inverno"
		}
		season_pt = season_map.get(season.lower(), season.capitalize())
		title = f"Temporada de {season_pt} {year}"
		slug = f"{season_pt.lower()}-{year}"
		
		resp = requests.get(f"{self.api_base}/temporada", params={"slug": slug, "_fields": "id,slug"}, headers=self.headers)
		if resp.status_code == 200 and resp.json():
			return resp.json()[0]["id"]
		
		payload = {
			"title": title,
			"slug": slug,
			"status": "publish",
			"acf": {
				"temp_periodo": season_pt,
				"temp_ano": year,
				"temp_descricao": f"Animes em lançamento na temporada de {season_pt} de {year}."
			}
		}
		res = requests.post(f"{self.api_base}/temporada", json=payload, headers=self.headers)
		if res.status_code in (200, 201):
			return res.json().get("id")
		return None

	def _link_anime_to_temporada(self, temporada_id: int, anime_id: int):
		try:
			resp = requests.get(f"{self.api_base}/temporada/{temporada_id}", headers=self.headers)
			if resp.status_code == 200:
				acf_data = resp.json().get("acf", {})
				animes_list = acf_data.get("temp_animes") or []
				if isinstance(animes_list, list):
					if anime_id not in animes_list:
						animes_list.append(anime_id)
						requests.post(f"{self.api_base}/temporada/{temporada_id}", json={"acf": {"temp_animes": animes_list}}, headers=self.headers)
		except Exception as e:
			logger.error(f"    ❌ Erro ao vincular anime à temporada: {e}")

	def _detect_idioma_slug(self, mal_id: int) -> str:
		"""Detecta dublagem PT-BR via dubladores cadastrados no MAL/Jikan."""
		try:
			resp = requests.get(
				f"{self.JIKAN_BASE_URL}/anime/{mal_id}/characters",
				timeout=15,
			)
			if resp.status_code != 200:
				return "legendado"
			for item in resp.json().get("data", []):
				for va in item.get("voice_actors", []):
					if va.get("language") in ("Portuguese", "Portuguese (BR)"):
						return "dublado"
		except Exception as e:
			logger.warning(f"  ⚠️ Não foi possível detectar idioma (MAL {mal_id}): {e}")
		return "legendado"

	def import_anime(self, anime_data: dict, dry_run: bool = False, update: bool = False, has_featured_media: bool = True) -> bool:
		title  = anime_data.get("title_english") or anime_data.get("title", "")
		mal_id = anime_data.get("mal_id")

		if not title or not mal_id: return False

		slug_source = anime_data.get("title_english") or title
		slug = slug_source.lower().replace(" ", "-").replace(":", "").replace("/", "").replace("'", "")
		slug = "".join(c for c in slug if c.isalnum() or c == "-").strip("-")

		logger.info(f"Processando Anime: '{title}' (MAL ID: {mal_id})")

		existing_id = None
		if not dry_run:
			existing_id = self._get_anime_id_by_slug(slug)
			if existing_id and not update:
				logger.info(f"  ⏭️ Anime slug '/{slug}/' já existente. Pulando.")
				return True

		studios = anime_data.get("studios", [])
		studio_name = studios[0].get("name", "Desconhecido") if studios else "Desconhecido"
		rating_slug = self._map_rating(anime_data.get("rating", ""))
		status_pt = self._map_status_exibicao(anime_data.get("status", ""))

		genre_ids = []
		status_terms = []

		if not dry_run and self.wp_url:
			for g in anime_data.get("genres", []):
				g_id = self._get_or_create_term("genero", self._map_genre_ptbr( g.get("name", "") ))
				if g_id: genre_ids.append(g_id)

			status_term_id = self._get_or_create_term("status_exibicao", status_pt)
			if status_term_id: status_terms.append(status_term_id)

		image_url = anime_data.get("images", {}).get("webp", {}).get("large_image_url") or anime_data.get("images", {}).get("jpg", {}).get("large_image_url")
		featured_media_id = None
		if not dry_run and self.wp_url and image_url:
			if not existing_id or not has_featured_media:
				featured_media_id = self._upload_media(image_url, title)

		idioma_slug = "legendado"
		if not dry_run and mal_id:
			idioma_slug = self._detect_idioma_slug(int(mal_id))
			time.sleep(0.5)

		payload = {
			"title":   title,
			"slug":    slug,
			"status":  "publish",
			"content": anime_data.get("synopsis", ""),
			"acf": {
				"anime_id_mal": int(mal_id),
				"anime_idioma": idioma_slug,
			}
		}
		
		if featured_media_id:
			payload["featured_media"] = featured_media_id

		if genre_ids: payload["genero"] = genre_ids
		if status_terms: payload["status_exibicao"] = status_terms

		if dry_run:
			logger.info(f"  [DRY-RUN] Publicação simulada para '{title}'")
			return True

		if not self.wp_url:
			return False

		final_anime_id = None
		try:
			if update and existing_id:
				url  = f"{self.api_base}/anime/{existing_id}"
				resp = requests.post(url, json=payload, headers=self.headers, timeout=25)
				action_label = f"atualizado (ID: {existing_id})"
				if resp.status_code in (200, 201):
					final_anime_id = existing_id
			else:
				url  = f"{self.api_base}/anime"
				resp = requests.post(url, json=payload, headers=self.headers, timeout=25)
				if resp.status_code in (200, 201):
					final_anime_id = resp.json().get('id')
					action_label = f"importado! ID: {final_anime_id}"

			if final_anime_id:
				logger.info(f"  ✅ Anime {action_label}")
				
				# Tratar temporada
				season = anime_data.get("season")
				year   = anime_data.get("year")
				if season and year:
					temporada_id = self._get_or_create_temporada(season, year)
					if temporada_id:
						self._link_anime_to_temporada(temporada_id, final_anime_id)
				
				return True
			else:
				logger.error(f"  ❌ Erro HTTP {resp.status_code} ao salvar anime.")
				return False
		except Exception as e:
			logger.error(f"  ❌ Erro de conexão ao salvar anime '{title}': {e}")
			return False

def main():
	parser = argparse.ArgumentParser()
	parser.add_argument("--dry-run", action="store_true", help="Simulação rápida")
	parser.add_argument("--update", action="store_true", help="Força a atualização de registros existentes")
	parser.add_argument("--limit", type=int, help="Limitar a quantidade de animes processados (para testes)")
	args = parser.parse_args()

	dry_run = args.dry_run
	update  = args.update
	limit   = args.limit

	importer = MALImporterSeason()

	if not dry_run and not importer.wp_url:
		logger.error("Sem credenciais. Use --dry-run")
		sys.exit(1)

	anime_list = importer.fetch_season_animes()

	if limit:
		anime_list = anime_list[:limit]
		logger.info(f"Limitação ativada: Processando apenas os primeiros {limit} animes.")

	if not anime_list:
		logger.error("Nenhum anime pôde ser carregado da API Jikan. Finalizando.")
		sys.exit(1)

	logger.info(f"Iniciando importação de {len(anime_list)} animes da temporada no WordPress...")

	success_count = 0
	fail_count    = 0

	for i, anime in enumerate(anime_list, start=1):
		logger.info(f"[{i}/{len(anime_list)}]")
		success = importer.import_anime(anime, dry_run=dry_run, update=update)
		if success:
			success_count += 1
		else:
			fail_count += 1

		time.sleep(0.5)

	logger.info(
		f"=== PIPELINE DE IMPORTAÇÃO CONCLUÍDO ===\n"
		f"  Total Coletado: {len(anime_list)}\n"
		f"  Sucessos: {success_count}\n"
		f"  Falhas: {fail_count}"
	)

if __name__ == "__main__":
	main()
