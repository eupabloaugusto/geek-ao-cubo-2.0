#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Importador Jikan MAL Wave 1 (Task 1.1)
Importa os Top 500 Animes mais populares do MyAnimeList para o WordPress local
cadastrando no Custom Post Type 'anime', preenchendo ACF e taxonomias.

Uso:
  python import_mal_wave1.py --dry-run --limit=5   (Simulação rápida)
  python import_mal_wave1.py                       (Execução real completa)
"""

import os
import sys
import time
import logging
import requests
from base64 import b64encode
from datetime import datetime
from dotenv import load_dotenv

# ---------------------------------------------------------------------------
# Configuração de Logging
# ---------------------------------------------------------------------------
logging.basicConfig(
	level=logging.INFO,
	format="[%(asctime)s] %(levelname)s: %(message)s",
	datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("MALImporter")


# ---------------------------------------------------------------------------
# Classe Principal
# ---------------------------------------------------------------------------
class MALImporter:
	JIKAN_BASE_URL = "https://api.jikan.moe/v4"
	JIKAN_DELAY    = 2.0  # Throttling de 2 segundos para respeitar rate limit do Jikan API (HTTP 429)

	def __init__(self):
		"""Inicializa as credenciais de acesso local do WordPress."""
		# Carrega variáveis .env localizadas no diretório da automação
		load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), ".env"))

		self.wp_url      = os.getenv("WP_BASE_URL", "").rstrip("/")
		self.wp_user     = os.getenv("WP_USERNAME", "")
		self.wp_password = os.getenv("WP_APP_PASSWORD", "")

		if not all([self.wp_url, self.wp_user, self.wp_password]):
			logger.warning(
				"Credenciais do WordPress não configuradas totalmente no arquivo .env!\n"
				"Se você for rodar uma importação real no WordPress, configure:\n"
				"  WP_BASE_URL (ex: http://localhost/geek-ao-cubo)\n"
				"  WP_USERNAME (ex: admin)\n"
				"  WP_APP_PASSWORD (senha de aplicativo criada no WP admin)"
			)

		credentials  = f"{self.wp_user}:{self.wp_password}"
		encoded_auth = b64encode(credentials.encode("utf-8")).decode("utf-8")
		self.headers = {
			"Authorization": f"Basic {encoded_auth}",
			"Content-Type":  "application/json",
		}
		self.api_base = f"{self.wp_url}/wp-json/wp/v2"

	# ------------------------------------------------------------------
	# Utilitários de Taxonomia e Mídia do WordPress
	# ------------------------------------------------------------------

	def _get_or_create_term(self, taxonomy: str, name: str) -> int | None:
		"""Busca um termo pelo nome em uma taxonomia específica e cria se não existir."""
		if not self.wp_url:
			return None

		try:
			# 1. Busca se já existe
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

			# 2. Se não existir, cria o termo dinamicamente
			resp = requests.post(
				f"{self.api_base}/{taxonomy}",
				json={"name": name},
				headers=self.headers,
				timeout=10,
			)
			if resp.status_code in (200, 201):
				term_id = resp.json().get("id")
				logger.info(f"  🏷️ Criado novo termo '{name}' na taxonomia '{taxonomy}' (ID: {term_id})")
				return term_id
			else:
				logger.warning(f"  ⚠️ Falha ao criar termo '{name}' em '{taxonomy}': HTTP {resp.status_code}")

		except Exception as e:
			logger.error(f"  ❌ Exceção ao resolver termo '{name}' em '{taxonomy}': {e}")
		return None

	def _upload_media(self, image_url: str, title: str) -> int | None:
		"""Baixa a capa oficial do MAL e faz upload na galeria de mídia do WordPress."""
		if not self.wp_url or not image_url:
			return None

		# Sanitiza nome de arquivo amigável para SEO
		slug     = title.lower().replace(" ", "-").replace(":", "").replace("/", "")
		slug     = "".join(c for c in slug if c.isalnum() or c == "-").strip("-")
		filename = f"capa-{slug}.jpg"

		try:
			resp = requests.get(image_url, timeout=10)
			if resp.status_code != 200:
				logger.warning(f"  ⚠️ Falha ao baixar imagem: {image_url}")
				return None

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
				media_id = upload_resp.json().get("id")
				logger.info(f"  🖼️ Imagem vinculada na mídia WP. ID: {media_id}")
				return media_id
			else:
				logger.warning(f"  ⚠️ Falha no upload da mídia para WP: HTTP {upload_resp.status_code}")
		except Exception as e:
			logger.error(f"  ❌ Erro no upload de imagem de {title}: {e}")
		return None

	def _get_anime_id_by_slug(self, slug: str) -> int | None:
		"""Retorna o ID WP do anime pelo slug, ou None se não existir."""
		if not self.wp_url:
			return None
		try:
			resp = requests.get(
				f"{self.api_base}/anime",
				params={"slug": slug, "status": "any", "_fields": "id,slug"},
				headers=self.headers,
				timeout=10,
			)
			if resp.status_code == 200:
				animes = resp.json()
				if animes:
					return int(animes[0]["id"])
			return None
		except Exception as e:
			logger.error(f"  ❌ Erro ao buscar slug '{slug}': {e}")
			return None

	def _check_anime_exists(self, slug: str) -> bool:
		"""Verifica se o anime já está cadastrado procurando pelo slug."""
		return self._get_anime_id_by_slug(slug) is not None

	# ------------------------------------------------------------------
	# Helpers de Classificação e Status
	# ------------------------------------------------------------------

	def _extract_broadcast_utc(self, anime_data: dict) -> tuple[str, str]:
		"""Extrai dia e hora de exibição do campo broadcast da Jikan e converte JST (UTC+9) para UTC.
		Retorna (horario_utc, dia_semana). Ex: ('08:00', 'Saturdays').
		JST é sempre UTC+9 (sem DST), então basta subtrair 9 horas.
		"""
		broadcast = anime_data.get("broadcast") or {}
		broadcast_time = broadcast.get("time") or ""   # ex: "17:00" (JST)
		broadcast_day  = broadcast.get("day")  or ""   # ex: "Saturdays"
		horario_utc = ""
		if broadcast_time:
			try:
				h, m = map(int, broadcast_time.split(":"))
				h_utc = (h - 9) % 24  # JST é UTC+9
				horario_utc = f"{h_utc:02d}:{m:02d}"
			except Exception:
				horario_utc = ""
		return horario_utc, broadcast_day

	def _fetch_anilist_banner(self, mal_id: int) -> str:
		"""Consulta a AniList GraphQL API pelo idMal e retorna o bannerImage horizontal."""
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
				banner = resp.json().get("data", {}).get("Media", {}).get("bannerImage")
				if banner:
					logger.info(f"  🖼️ Banner AniList obtido: {banner}")
					return banner
			elif resp.status_code == 429:
				logger.warning("  ⚠️ AniList rate limit. Pulando banner.")
			else:
				logger.warning(f"  ⚠️ AniList retornou HTTP {resp.status_code}. Sem banner.")
		except Exception as e:
			logger.warning(f"  ⚠️ Erro ao buscar banner AniList para MAL ID {mal_id}: {e}")
		return ""

	def _map_source(self, source_raw: str) -> str:
		"""Normaliza o campo 'source' da Jikan para os slugs aceitos pelo ACF."""
		if not source_raw:
			return "other"
		s = source_raw.lower().strip()
		if s in ("manga", "4-koma manga", "web manga", "manga adaptation"):
			return "manga"
		elif s in ("light novel", "light_novel", "novel"):
			return "light_novel"
		elif s in ("visual novel", "visual_novel"):
			return "visual_novel"
		elif s in ("original",):
			return "original"
		elif s in ("game", "card game", "video game"):
			return "game"
		return "other"

	def _map_rating(self, rating_raw: str) -> str:
		"""Mapeia classificação indicativa da Jikan para os slugs do ACF."""
		if not rating_raw:
			return "g"
		rating = rating_raw.lower()
		if "pg-13" in rating:
			return "pg13"
		elif "pg" in rating:
			return "pg"
		elif "r - 17+" in rating:
			return "r17"
		elif "r+" in rating:
			return "r"
		elif "rx" in rating or "hentai" in rating:
			return "rx"
		return "g"

	def _map_status_exibicao(self, status_raw: str) -> str:
		"""Traduz e padroniza status de exibição da Jikan."""
		if not status_raw:
			return "Pausado"
		status = status_raw.lower()
		if "currently airing" in status:
			return "Em Exibição"
		elif "finished airing" in status:
			return "Finalizado"
		elif "not yet aired" in status:
			return "Brevemente"
		return "Pausado"

	# ------------------------------------------------------------------
	# Pipeline de Execução
	# ------------------------------------------------------------------

	def fetch_top_animes(self, limit: int = 500) -> list:
		"""Coleta os animes mais populares do MyAnimeList via paginação Jikan."""
		animes       = []
		page         = 1
		items_needed = limit

		logger.info(f"Buscando ranking de popularidade MAL via Jikan API (Objetivo: {limit} animes)...")

		while items_needed > 0:
			logger.info(f"Requisitando página {page} da Jikan API...")
			api_url = f"{self.JIKAN_BASE_URL}/top/anime"

			try:
				resp = requests.get(api_url, params={"page": page, "filter": "bypopularity"}, timeout=15)
				if resp.status_code == 429:
					logger.warning("  Rate limit atingido (HTTP 429). Aguardando 5 segundos...")
					time.sleep(5.0)
					continue

				if resp.status_code != 200:
					logger.error(f"  Falha na chamada da Jikan API: HTTP {resp.status_code}. Abortando.")
					break

				data      = resp.json()
				page_data = data.get("data", [])

				if not page_data:
					logger.info("  Nenhum anime retornado nesta página. Fim da listagem.")
					break

				for anime in page_data:
					animes.append(anime)
					items_needed -= 1
					if items_needed == 0:
						break

				logger.info(f"  Página {page} carregada com sucesso. Acumulado: {len(animes)} animes.")

				# Delay obrigatório para não estourar limite da API
				time.sleep(self.JIKAN_DELAY)
				page += 1

			except Exception as e:
				logger.error(f"  ❌ Erro de conexão com a Jikan API na página {page}: {e}")
				break

		return animes

	def _build_alt_titles(self, anime_data: dict, display_title: str) -> str:
		"""Monta string de títulos alternativos separados por vírgula (exceto o título de exibição)."""
		alts = []
		# title original (romanização japonesa), se diferente do display
		raw_title = anime_data.get("title", "")
		if raw_title and raw_title != display_title:
			alts.append(raw_title)
		# Synonyms e outros títulos do array titles
		for t in anime_data.get("titles", []):
			val = t.get("title", "").strip()
			if val and val != display_title and val not in alts:
				alts.append(val)
		return ", ".join(alts)

	def _map_genre_ptbr(self, genre_en: str) -> str:
		"""Traduz gêneros do MAL (inglês) para PT-BR. Gêneros do nicho mantidos como estão."""
		TRADUZIR = {
			"action":       "Ação",
			"adventure":    "Aventura",
			"comedy":       "Comédia",
			"drama":        "Drama",
			"fantasy":      "Fantasia",
			"horror":       "Terror",
			"mystery":      "Mistério",
			"romance":      "Romance",
			"sci-fi":       "Ficção Científica",
			"science fiction": "Ficção Científica",
			"supernatural": "Sobrenatural",
			"sports":       "Esportes",
			"psychological": "Psicológico",
			"historical":   "Histórico",
			"military":     "Militar",
			"school":       "Escola",
			"music":        "Música",
			"award winning": "Premiado",
			"suspense":     "Suspense",
			"avant garde":  "Vanguarda",
			"mythology":    "Mitologia",
			"racing":       "Corrida",
			"martial arts": "Artes Marciais",
			"super power":  "Superpoderes",
			"vampire":      "Vampiro",
			"space":        "Espaço",
		}
		return TRADUZIR.get(genre_en.lower(), genre_en)

	def import_anime(self, anime_data: dict, dry_run: bool = False, update: bool = False) -> bool:
		"""Monta o payload, resolve taxonomias e publica o anime no WordPress."""
		title  = anime_data.get("title_english") or anime_data.get("title", "")
		mal_id = anime_data.get("mal_id")

		if not title or not mal_id:
			return False

		# Gera o slug a partir do título em inglês (ou alternativo) para URL limpa em ASCII
		slug_source = anime_data.get("title_english") or title
		slug = slug_source.lower().replace(" ", "-").replace(":", "").replace("/", "").replace("'", "")
		slug = "".join(c for c in slug if c.isalnum() or c == "-").strip("-")

		logger.info(f"Processando Anime: '{title}' (MAL ID: {mal_id})")

		# 1. Verificação de duplicidade
		existing_id = None
		if not dry_run:
			existing_id = self._get_anime_id_by_slug(slug)
			if existing_id and not update:
				logger.info(f"  ⏭️ Anime slug '/{slug}/' já existente no WordPress. Pulando.")
				return True

		# 2. Resolve estúdios (pega o primeiro estúdio principal)
		studios     = anime_data.get("studios", [])
		studio_name = studios[0].get("name", "Desconhecido") if studios else "Desconhecido"

		# 3. Resolve status e classificação
		rating_slug = self._map_rating(anime_data.get("rating", ""))
		status_pt   = self._map_status_exibicao(anime_data.get("status", ""))

		# 4. Cria e resolve taxonomias (Somente se não for Dry Run)
		genre_ids    = []
		status_terms = []

		if not dry_run and self.wp_url:
			for g in anime_data.get("genres", []):
				g_id = self._get_or_create_term("genero", self._map_genre_ptbr( g.get("name", "") ))
				if g_id:
					genre_ids.append(g_id)

			status_term_id = self._get_or_create_term("status_exibicao", status_pt)
			if status_term_id:
				status_terms.append(status_term_id)

		# 5. Download e envio da imagem destacada
		image_url = anime_data.get("images", {}).get("webp", {}).get("large_image_url") or anime_data.get("images", {}).get("jpg", {}).get("large_image_url")
		featured_media_id = None
		if not dry_run and self.wp_url and image_url:
			featured_media_id = self._upload_media(image_url, title)

		# 5b. URL do trailer (YouTube) retornada pela Jikan
		trailer_data = anime_data.get("trailer") or {}
		trailer_url  = trailer_data.get("url") or ""
		if not trailer_url and trailer_data.get("youtube_id"):
			trailer_url = f"https://www.youtube.com/watch?v={trailer_data['youtube_id']}"

		# 5c. Horário de exibição (broadcast Jikan JST → UTC)
		horario_utc, broadcast_day = self._extract_broadcast_utc(anime_data)

		# 6. Monta o payload conforme campos do Custom Post Type 'anime' e ACF
		payload = {
			"title":   title,
			"slug":    slug,
			"status":  "publish",
			"content": anime_data.get("synopsis", ""),
			"acf": {
				"anime_id_mal":          int(mal_id),
				"anime_studio":          studio_name,
				"anime_nota_mal":        float(anime_data.get("score") or 0.0),
				"anime_membros":         int(anime_data.get("members") or 0),
				"anime_ranking":         int(anime_data.get("rank") or 0),
				"anime_popularidade":    int(anime_data.get("popularity") or 0),
				"anime_imagem_capa_url": image_url or "",
				"anime_trailer_url":     trailer_url,
				"anime_banner_url":      self._fetch_anilist_banner( int(mal_id) ),
				"anime_ano":             int(anime_data.get("year") or anime_data.get("aired", {}).get("prop", {}).get("from", {}).get("year") or 0),
				"anime_total_episodios": int(anime_data.get("episodes") or 0),
				"anime_duracao":         anime_data.get("duration", ""),
				"anime_rating":          rating_slug,
				"anime_source":          self._map_source( anime_data.get("source", "") ),
				"anime_sinopse":              anime_data.get("synopsis", ""),
				"anime_titulo_japones":       anime_data.get("title_japanese", ""),
				"anime_titulos_alternativos": self._build_alt_titles(anime_data, title),
				"anime_idioma":               "legendado",
				"anime_horario_exibicao":     horario_utc,
				"anime_dia_semana":           broadcast_day,
			}
		}

		if genre_ids:
			payload["genero"] = genre_ids
		if status_terms:
			payload["status_exibicao"] = status_terms
		if featured_media_id:
			payload["featured_media"] = featured_media_id

		if dry_run:
			logger.info(f"  [DRY-RUN] Publicação simulada com sucesso para '{title}'")
			return True

		if not self.wp_url:
			logger.error("  ❌ URL do WordPress não configurada. Impossível publicar.")
			return False

		# 7. Cria ou atualiza via REST API
		try:
			if update and existing_id:
				# PATCH no post existente
				url  = f"{self.api_base}/anime/{existing_id}"
				resp = requests.post(url, json=payload, headers=self.headers, timeout=25)
				action_label = f"atualizado (ID: {existing_id})"
			else:
				# POST — cria novo
				url  = f"{self.api_base}/anime"
				resp = requests.post(url, json=payload, headers=self.headers, timeout=25)
				action_label = f"importado! ID: {resp.json().get('id') if resp.status_code in (200,201) else '?'}"

			if resp.status_code in (200, 201):
				post_data = resp.json()
				logger.info(f"  ✅ Anime {action_label} | Link: {post_data.get('link')}")
				return True
			else:
				logger.error(f"  ❌ Erro HTTP {resp.status_code} ao salvar anime: {resp.text[:300]}")
				return False
		except Exception as e:
			logger.error(f"  ❌ Erro de conexão ao salvar anime '{title}': {e}")
			return False


# ---------------------------------------------------------------------------
# Execução CLI
# ---------------------------------------------------------------------------
def main():
	dry_run = "--dry-run" in sys.argv
	update  = "--update"  in sys.argv
	limit   = 500

	# Lê parâmetro limit caso passado por CLI (ex: --limit=10)
	for arg in sys.argv:
		if arg.startswith("--limit="):
			try:
				limit = int(arg.split("=")[1])
			except ValueError:
				logger.warning("Valor de limit inválido. Importando limit padrão (500).")

	importer = MALImporter()

	if not dry_run and not importer.wp_url:
		logger.error(
			"Arquivo .env não configurado ou credenciais ausentes!\n"
			"Para testar o script local de forma simulada sem conectar ao WordPress, utilize:\n"
			"  python import_mal_wave1.py --dry-run --limit=5"
		)
		sys.exit(1)

	# Coleta da Jikan API
	anime_list = importer.fetch_top_animes(limit=limit)

	if not anime_list:
		logger.error("Nenhum anime pôde ser carregado da API Jikan. Finalizando.")
		sys.exit(1)

	logger.info(f"Iniciando importação de {len(anime_list)} animes no WordPress...")

	success_count = 0
	fail_count    = 0

	for i, anime in enumerate(anime_list, start=1):
		logger.info(f"[{i}/{len(anime_list)}]")
		success = importer.import_anime(anime, dry_run=dry_run, update=update)
		if success:
			success_count += 1
		else:
			fail_count += 1

		# Pequena pausa para evitar sobrecarga no banco de dados local
		time.sleep(0.5)

	logger.info(
		f"=== PIPELINE DE IMPORTAÇÃO CONCLUÍDO ===\n"
		f"  Total Coletado: {len(anime_list)}\n"
		f"  Sucessos: {success_count}\n"
		f"  Falhas: {fail_count}"
	)


if __name__ == "__main__":
	main()
