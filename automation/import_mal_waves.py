#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Importador Jikan MAL Waves 2 & 3 (Task 1.2)
Importa em lotes resilientes os animes da:
  - Onda 2: Animes em exibição e lançamentos 2020-2026 (~2.000 páginas)
  - Onda 3: Restante do catálogo relevante (Nota > 7.0 + membros > 10.000)

Mantém progresso persistente no arquivo import_progress.json para retomar
automaticamente de onde parou em caso de falha de conexão ou interrupção.

Uso:
  python import_mal_waves.py --wave=2 --limit-pages=3 --dry-run (Testa Onda 2 simulação)
  python import_mal_waves.py --wave=2                           (Executa Onda 2 real)
  python import_mal_waves.py --wave=3                           (Executa Onda 3 real)
"""

import os
import sys
import time
import json
import logging
import requests
from base64 import b64encode
from dotenv import load_dotenv

# ---------------------------------------------------------------------------
# Configuração de Logging
# ---------------------------------------------------------------------------
logging.basicConfig(
	level=logging.INFO,
	format="[%(asctime)s] %(levelname)s: %(message)s",
	datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("MALWavesImporter")


# ---------------------------------------------------------------------------
# Classe Principal de Importação
# ---------------------------------------------------------------------------
class MALWavesImporter:
	JIKAN_BASE_URL    = "https://api.jikan.moe/v4"
	BASE_DELAY        = 2.0  # Delay base padrão (segundos)
	PROGRESS_FILENAME = "import_progress.json"

	def __init__(self):
		"""Inicializa credenciais WP e gerencia arquivo de progresso."""
		# Resolve caminhos absolutos
		self.base_dir = os.path.dirname(os.path.abspath(__file__))
		load_dotenv(dotenv_path=os.path.join(self.base_dir, ".env"))

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

		self.progress_path = os.path.join(self.base_dir, self.PROGRESS_FILENAME)
		self.progress      = self._load_progress()

	# ------------------------------------------------------------------
	# Gestão de Progresso Persistente
	# ------------------------------------------------------------------

	def _load_progress(self) -> dict:
		"""Carrega o progresso anterior em arquivo ou inicia um novo."""
		if os.path.exists(self.progress_path):
			try:
				with open(self.progress_path, "r", encoding="utf-8") as f:
					return json.load(f)
			except Exception as e:
				logger.error(f"Erro ao ler arquivo de progresso: {e}. Criando novo.")
		
		# Estado padrão inicial
		return {
			"wave_2": { "last_page": 0, "completed": False },
			"wave_3": { "last_page": 0, "completed": False }
		}

	def _save_progress(self) -> None:
		"""Persiste o progresso atual em arquivo JSON."""
		try:
			with open(self.progress_path, "w", encoding="utf-8") as f:
				json.dump(self.progress, f, ensure_ascii=False, indent=2)
		except Exception as e:
			logger.error(f"Falha ao salvar progresso em JSON: {e}")

	# ------------------------------------------------------------------
	# Utilitários de Taxonomia e Mídia do WordPress
	# ------------------------------------------------------------------

	def _get_or_create_term(self, taxonomy: str, name: str) -> int | None:
		"""Busca termo na taxonomia e cria caso ausente."""
		if not self.wp_url:
			return None

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
				term_id = resp.json().get("id")
				logger.info(f"  🏷️ Novo termo '{name}' na taxonomia '{taxonomy}' criado (ID: {term_id})")
				return term_id

		except Exception as e:
			logger.error(f"  ❌ Exceção ao resolver termo '{name}' em '{taxonomy}': {e}")
		return None

	def _upload_media(self, image_url: str, title: str) -> int | None:
		"""Envia a capa baixada para a galeria do WordPress."""
		if not self.wp_url or not image_url:
			return None

		slug     = title.lower().replace(" ", "-").replace(":", "").replace("/", "")
		slug     = "".join(c for c in slug if c.isalnum() or c == "-").strip("-")
		filename = f"capa-{slug}.jpg"

		try:
			resp = requests.get(image_url, timeout=10)
			if resp.status_code != 200:
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
				return upload_resp.json().get("id")
		except Exception as e:
			logger.error(f"  ❌ Erro no upload de imagem de {title}: {e}")
		return None

	def _check_anime_exists(self, slug: str) -> bool:
		"""Verifica se o anime já existe no WP procurando por slug."""
		if not self.wp_url:
			return False

		try:
			resp = requests.get(
				f"{self.api_base}/anime",
				params={"slug": slug, "status": "any", "_fields": "id,slug"},
				headers=self.headers,
				timeout=10,
			)
			if resp.status_code == 200:
				return len(resp.json()) > 0
			return False
		except Exception as e:
			logger.error(f"  ❌ Erro ao verificar duplicidade do slug '{slug}': {e}")
			return False

	def _map_rating(self, rating_raw: str) -> str:
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
	# Importação física de Item
	# ------------------------------------------------------------------

	def import_anime(self, anime_data: dict, dry_run: bool = False) -> bool:
		"""Importa o anime da Jikan para o banco local de dados do WP."""
		title  = anime_data.get("title", "")
		mal_id = anime_data.get("mal_id")

		if not title or not mal_id:
			return False

		slug_source = anime_data.get("title_english") or title
		slug = slug_source.lower().replace(" ", "-").replace(":", "").replace("/", "").replace("'", "")
		slug = "".join(c for c in slug if c.isalnum() or c == "-").strip("-")

		if not dry_run and self._check_anime_exists(slug):
			logger.info(f"  [Skipped] '{title}' (MAL ID: {mal_id}) já existente no WP.")
			return True

		studios     = anime_data.get("studios", [])
		studio_name = studios[0].get("name", "Desconhecido") if studios else "Desconhecido"
		rating_slug = self._map_rating(anime_data.get("rating", ""))
		status_pt   = self._map_status_exibicao(anime_data.get("status", ""))

		genre_ids    = []
		status_terms = []

		if not dry_run and self.wp_url:
			for g in anime_data.get("genres", []):
				g_id = self._get_or_create_term("genero", g.get("name", ""))
				if g_id:
					genre_ids.append(g_id)

			status_term_id = self._get_or_create_term("status_exibicao", status_pt)
			if status_term_id:
				status_terms.append(status_term_id)

		image_url = anime_data.get("images", {}).get("webp", {}).get("large_image_url") or anime_data.get("images", {}).get("jpg", {}).get("large_image_url")
		featured_media_id = None
		if not dry_run and self.wp_url and image_url:
			featured_media_id = self._upload_media(image_url, title)

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
				"anime_ano":             int(anime_data.get("year") or anime_data.get("aired", {}).get("prop", {}).get("from", {}).get("year") or 0),
				"anime_total_episodios": int(anime_data.get("episodes") or 0),
				"anime_duracao":         anime_data.get("duration", ""),
				"anime_rating":          rating_slug,
				"anime_source":          anime_data.get("source", "manga").lower(),
				"anime_sinopse":         anime_data.get("synopsis", ""),
			}
		}

		if genre_ids:
			payload["genero"] = genre_ids
		if status_terms:
			payload["status_exibicao"] = status_terms
		if featured_media_id:
			payload["featured_media"] = featured_media_id

		if dry_run:
			logger.info(f"  [DRY-RUN] Processado: '{title}' (MAL ID: {mal_id})")
			return True

		try:
			resp = requests.post(f"{self.api_base}/anime", json=payload, headers=self.headers, timeout=25)
			if resp.status_code in (200, 201):
				logger.info(f"  ✅ Importado: '{title}' (ID: {resp.json().get('id')})")
				return True
			logger.error(f"  ❌ Erro HTTP {resp.status_code} ao importar '{title}': {resp.text[:200]}")
		except Exception as e:
			logger.error(f"  ❌ Erro de conexão ao cadastrar '{title}': {e}")
		return False

	# ------------------------------------------------------------------
	# Loop Principal de Coleta por Ondas
	# ------------------------------------------------------------------

	def run_wave(self, wave_num: int, limit_pages: int = 0, dry_run: bool = False) -> None:
		"""
		Executa o processo de paginação de uma onda específica de importação.
		
		Args:
			wave_num:    Número da onda (2 ou 3).
			limit_pages: Quantidade máxima de páginas a processar nesta sessão (0 = todas).
			dry_run:     Se True, executa em simulação sem cadastrar no WP.
		"""
		wave_key = f"wave_{wave_num}"
		if wave_key not in self.progress:
			logger.error(f"Onda {wave_num} inválida.")
			return

		if self.progress[wave_key]["completed"]:
			logger.info(f"Onda {wave_num} já consta como Concluída no arquivo de progresso. Finalizando.")
			return

		start_page = self.progress[wave_key]["last_page"] + 1
		logger.info(f"=== INICIANDO IMPORTAÇÃO ONDA {wave_num} (Local-First) ===")
		logger.info(f"Retomando a partir da Página: {start_page}")

		current_page = start_page
		pages_count  = 0
		delay        = self.BASE_DELAY

		while True:
			# Respeita o limite de páginas por execução caso seja especificado
			if limit_pages > 0 and pages_count >= limit_pages:
				logger.info(f"Limite de {limit_pages} páginas nesta execução atingido. Parando.")
				break

			logger.info(f"Requisitando página {current_page} da Jikan API...")

			# Configura parâmetros com base na onda
			api_url = f"{self.JIKAN_BASE_URL}/anime"
			params = {
				"page":     current_page,
				"order_by": "popularity"
			}

			if wave_num == 2:
				# Onda 2: Animes Airing + 2020-2026
				params["start_date"] = "2020-01-01"
				params["end_date"]   = "2026-12-31"
			elif wave_num == 3:
				# Onda 3: Catalog Nota > 7.0
				params["min_score"] = 7.0

			try:
				resp = requests.get(api_url, params=params, timeout=15)
				
				# Tratamento adaptativo e exponencial de Rate Limit (HTTP 429)
				if resp.status_code == 429:
					logger.warning(f"  Rate Limit (HTTP 429) detectado! Aumentando delay para {delay * 2}s e aguardando...")
					time.sleep(delay * 2)
					delay = min(delay * 2, 30.0) # limite de 30s de sleep
					continue

				if resp.status_code != 200:
					logger.error(f"  Falha crítica na Jikan API: HTTP {resp.status_code}. Parando execução.")
					break

				# Restaura o delay base se a requisição obteve sucesso
				delay = self.BASE_DELAY

				data      = resp.json()
				page_data = data.get("data", [])
				pagination = data.get("pagination", {})
				has_next   = pagination.get("has_next_page", False)

				if not page_data:
					logger.info("  Nenhum anime retornado. Processamento da onda finalizado!")
					if not dry_run:
						self.progress[wave_key]["completed"] = True
						self._save_progress()
					break

				logger.info(f"  Página {current_page} obtida. Processando {len(page_data)} animes...")

				# Processa todos os animes da página
				for anime in page_data:
					# Filtro adicional local para a Onda 3 (membros > 10.000)
					if wave_num == 3:
						members = anime.get("members", 0)
						if members < 10000:
							logger.info(f"  [Skipped] '{anime.get('title')}' ignorado por baixa popularidade (Membros: {members} < 10k)")
							continue

					self.import_anime(anime, dry_run=dry_run)
					time.sleep(0.5) # Pausa leve local entre inserções

				# Grava o progresso no final de cada página com sucesso
				if not dry_run:
					self.progress[wave_key]["last_page"] = current_page
					self._save_progress()
					logger.info(f"  💾 Progresso salvo! Última página concluída: {current_page}")

				pages_count  += 1
				current_page += 1

				if not has_next:
					logger.info("  Não existem mais páginas adicionais no ranking. Fim da onda!")
					if not dry_run:
						self.progress[wave_key]["completed"] = True
						self._save_progress()
					break

				# Delay normal de rate limit
				time.sleep(delay)

			except Exception as e:
				logger.error(f"  ❌ Falha crítica de requisição na página {current_page}: {e}")
				break

		logger.info(f"=== SESSÃO ONDA {wave_num} CONCLUÍDA ===")


# ---------------------------------------------------------------------------
# Ponto de Entrada CLI
# ---------------------------------------------------------------------------
def main():
	dry_run     = "--dry-run" in sys.argv
	wave        = 2
	limit_pages = 0

	# Processa argumentos CLI
	for arg in sys.argv:
		if arg.startswith("--wave="):
			try:
				wave = int(arg.split("=")[1])
			except ValueError:
				logger.warning("Wave inválido. Usando Onda 2 por padrão.")
		elif arg.startswith("--limit-pages="):
			try:
				limit_pages = int(arg.split("=")[1])
			except ValueError:
				logger.warning("Parâmetro --limit-pages inválido. Importando todas.")

	importer = MALWavesImporter()

	if not dry_run and not importer.wp_url:
		logger.error(
			"Arquivo .env não configurado ou credenciais ausentes!\n"
			"Para testar o script local de forma simulada sem conectar ao WordPress, utilize:\n"
			"  python import_mal_waves.py --wave=2 --limit-pages=2 --dry-run"
		)
		sys.exit(1)

	importer.run_wave(wave_num=wave, limit_pages=limit_pages, dry_run=dry_run)


if __name__ == "__main__":
	main()
