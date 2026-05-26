#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Sincronizador Contínuo Diário (Task 1.3)
Vanece a API REST do WordPress local à procura de animes cadastrados com o
status "Em Exibição", consulta a API Jikan MAL em tempo real para verificar se
houve alterações em notas, membros ou episódios, e atualiza cirurgicamente.

Gerencia também a transição automática da taxonomia de "Em Exibição" para
"Finalizado" quando o status no MyAnimeList mudar para concluído.

Uso:
  python sync_airing_animes.py --dry-run   (Executa simulação com dados fictícios caso o banco esteja vazio)
  python sync_airing_animes.py             (Executa sincronização em produção)
"""

import os
import sys
import time
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
logger = logging.getLogger("MALSync")


# ---------------------------------------------------------------------------
# Classe Sincronizadora
# ---------------------------------------------------------------------------
class MALSync:
	JIKAN_BASE_URL = "https://api.jikan.moe/v4"
	JIKAN_DELAY    = 2.0  # delay para respeitar rate limit do Jikan (HTTP 429)

	def __init__(self):
		"""Inicializa as credenciais de acesso local do WordPress."""
		self.base_dir = os.path.dirname(os.path.abspath(__file__))
		load_dotenv(dotenv_path=os.path.join(self.base_dir, ".env"))

		self.wp_url      = os.getenv("WP_BASE_URL", "").rstrip("/")
		self.wp_user     = os.getenv("WP_USERNAME", "")
		self.wp_password = os.getenv("WP_APP_PASSWORD", "")

		if not all([self.wp_url, self.wp_user, self.wp_password]):
			logger.warning(
				"Credenciais do WordPress não configuradas no arquivo .env!\n"
				"Se você for rodar uma sincronização real no WordPress, configure:\n"
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
	# Helpers REST API
	# ------------------------------------------------------------------

	def _get_term_id_by_name(self, taxonomy: str, name: str) -> int | None:
		"""Busca o ID de um termo de taxonomia pelo nome."""
		if not self.wp_url:
			return None
		try:
			resp = requests.get(
				f"{self.api_base}/{taxonomy}",
				params={"search": name, "_fields": "id,name"},
				headers=self.headers,
				timeout=10
			)
			if resp.status_code == 200:
				terms = resp.json()
				for term in terms:
					if term["name"].lower() == name.lower():
						return term["id"]
		except Exception as e:
			logger.error(f"Erro ao buscar termo '{name}' em '{taxonomy}': {e}")
		return None

	def _fetch_local_airing_animes(self, term_id: int) -> list:
		"""Consulta o WP REST API filtrando pelo termo da taxonomia de exibição."""
		if not self.wp_url:
			return []
		try:
			resp = requests.get(
				f"{self.api_base}/anime",
				params={"status_exibicao": term_id, "per_page": 100, "status": "publish"},
				headers=self.headers,
				timeout=15
			)
			if resp.status_code == 200:
				return resp.json()
			logger.error(f"Erro ao obter animes locais: HTTP {resp.status_code}")
		except Exception as e:
			logger.error(f"Falha de conexão ao obter animes locais: {e}")
		return []

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
	# Fluxo Principal
	# ------------------------------------------------------------------

	def sync(self, dry_run: bool = False) -> None:
		"""Executa a rotina de sincronização diária."""
		logger.info("=== INICIANDO SINCRONIZAÇÃO DIÁRIA DE ANIMES (Task 1.3) ===")

		# 1. Obtém ID do termo "Em Exibição" no WordPress
		airing_term_id = None
		if self.wp_url:
			airing_term_id = self._get_term_id_by_name("status_exibicao", "Em Exibição")
			if not airing_term_id:
				logger.warning("Termo 'Em Exibição' não encontrado localmente no WP. Criando...")
				# Cria se não existir
				try:
					resp = requests.post(f"{self.api_base}/status_exibicao", json={"name": "Em Exibição"}, headers=self.headers, timeout=10)
					if resp.status_code in (200, 201):
						airing_term_id = resp.json().get("id")
				except Exception as e:
					logger.error(f"Falha ao criar termo: {e}")

		# 2. Busca animes locais vinculados a este termo
		local_animes = []
		if airing_term_id:
			local_animes = self._fetch_local_airing_animes(airing_term_id)

		# MOCK / DADOS DE TESTE PARA DRY-RUN CASO O BANCO ESTEJA VAZIO
		if not local_animes and dry_run:
			logger.info("Banco local vazio ou sem animes em exibição. Carregando dados fictícios para fins de teste dry-run.")
			local_animes = [
				{
					"id": 9999,
					"title": {"rendered": "One Piece"},
					"slug": "one-piece",
					"acf": {
						"anime_id_mal": 21,
						"anime_studio": "Toei Animation",
						"anime_nota_mal": 8.40,  # Nota menor para simular alteração
						"anime_membros": 2100000,
						"anime_total_episodios": 1090,  # Menor quantidade de episódios para simular
						"anime_duracao": "24 min per ep",
						"anime_rating": "pg13",
						"anime_source": "manga"
					}
				},
				{
					"id": 9998,
					"title": {"rendered": "Sousou no Frieren"},
					"slug": "sousou-no-frieren",
					"acf": {
						"anime_id_mal": 52991,
						"anime_studio": "Madhouse",
						"anime_nota_mal": 9.38,  # MAL score atual pode diferir
						"anime_membros": 600000,
						"anime_total_episodios": 28,
						"anime_duracao": "24 min per ep",
						"anime_rating": "pg13",
						"anime_source": "manga"
					}
				}
			]

		if not local_animes:
			logger.info("Nenhum anime com status 'Em Exibição' encontrado para atualizar. Finalizando.")
			return

		logger.info(f"Encontrados {len(local_animes)} animes locais para verificar.")

		success_count = 0
		updated_count = 0
		delay         = self.JIKAN_DELAY

		for i, local in enumerate(local_animes, start=1):
			title  = local.get("title", {}).get("rendered", "Sem título")
			acf    = local.get("acf", {})
			mal_id = acf.get("anime_id_mal")
			wp_id  = local.get("id")

			if not mal_id:
				logger.warning(f"[{i}/{len(local_animes)}] Anime '{title}' (WP ID: {wp_id}) sem anime_id_mal cadastrado. Pulando.")
				continue

			logger.info(f"[{i}/{len(local_animes)}] Verificando Jikan MAL para: '{title}' (MAL ID: {mal_id})")

			# 3. Consulta Jikan API
			jikan_url = f"{self.JIKAN_BASE_URL}/anime/{mal_id}"
			
			try:
				resp = requests.get(jikan_url, timeout=15)
				
				# Rate Limit Adaptativo
				if resp.status_code == 429:
					logger.warning("  HTTP 429 (Rate Limit)! Aguardando 5 segundos e reprocessando...")
					time.sleep(5.0)
					# Tenta de novo no loop atual
					continue

				if resp.status_code != 200:
					logger.error(f"  Falha na Jikan API para MAL ID {mal_id}: HTTP {resp.status_code}")
					time.sleep(delay)
					continue

				mal_data = resp.json().get("data", {})
				if not mal_data:
					time.sleep(delay)
					continue

				# 4. Compara variáveis e detecta diferenças
				remote_score  = float(mal_data.get("score") or 0.0)
				remote_members = int(mal_data.get("members") or 0)
				remote_eps     = int(mal_data.get("episodes") or 0)
				remote_status  = mal_data.get("status", "")
				remote_status_pt = self._map_status_exibicao(remote_status)

				local_score  = float(acf.get("anime_nota_mal") or 0.0)
				local_members = int(acf.get("anime_membros") or 0)
				local_eps     = int(acf.get("anime_total_episodios") or 0)

				# Detecta se existem alterações reais
				diffs = []
				acf_update = {}

				if abs(remote_score - local_score) > 0.005:
					diffs.append(f"Nota: {local_score:.2f} -> {remote_score:.2f}")
					acf_update["anime_nota_mal"] = remote_score

				if abs(remote_members - local_members) > 1000: # Aceita uma tolerância de 1k para evitar updates a todo segundo
					diffs.append(f"Membros: {local_members} -> {remote_members}")
					acf_update["anime_membros"] = remote_members

				if remote_eps != local_eps:
					diffs.append(f"Episódios: {local_eps} -> {remote_eps}")
					acf_update["anime_total_episodios"] = remote_eps

				payload = {}
				
				# Trata transição de status (Em Exibição -> Finalizado)
				status_changed = False
				if remote_status_pt != "Em Exibição":
					diffs.append(f"Status: Em Exibição -> {remote_status_pt}")
					status_changed = True
					
					# Resolve o ID da taxonomia de destino "Finalizado" ou "Pausado"
					if not dry_run and self.wp_url:
						dest_term_id = self._get_term_id_by_name("status_exibicao", remote_status_pt)
						if dest_term_id:
							payload["status_exibicao"] = [dest_term_id]

				if diffs:
					logger.info(f"  📢 Mudanças detectadas: " + " | ".join(diffs))
					
					# 5. Executa atualização PATCH no WordPress local
					if acf_update:
						payload["acf"] = acf_update

					if dry_run:
						logger.info(f"  [DRY-RUN] Anime '{title}' atualizado com sucesso (Simulado).")
						updated_count += 1
					else:
						# Envia requisição PATCH para atualizar apenas os campos informados
						resp_post = requests.post(
							f"{self.api_base}/anime/{wp_id}",
							json=payload,
							headers=self.headers,
							timeout=20
						)
						if resp_post.status_code == 200:
							logger.info(f"  ✅ WordPress atualizado com sucesso para '{title}'!")
							updated_count += 1
						else:
							logger.error(f"  ❌ Erro HTTP {resp_post.status_code} ao atualizar '{title}': {resp_post.text[:200]}")
				else:
					logger.info("  ✓ Sem alterações pendentes.")

				success_count += 1
				
				# Delay normal de controle
				time.sleep(delay)

			except Exception as e:
				logger.error(f"  ❌ Exceção ao sincronizar anime '{title}': {e}")
				time.sleep(delay)

		logger.info(
			f"=== SINCRONIZAÇÃO DIÁRIA CONCLUÍDA ===\n"
			f"  Animes Verificados: {success_count}\n"
			f"  Animes Atualizados: {updated_count}"
		)


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------
def main():
	dry_run = "--dry-run" in sys.argv
	sync    = MALSync()
	
	if not dry_run and not sync.wp_url:
		logger.error(
			"Arquivo .env não configurado ou credenciais ausentes!\n"
			"Para testar o script local de forma simulada sem conectar ao WordPress, utilize:\n"
			"  python sync_airing_animes.py --dry-run"
		)
		sys.exit(1)

	sync.sync(dry_run=dry_run)


if __name__ == "__main__":
	main()
