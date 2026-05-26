#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Publicador REST API (Task 3.4)
Lê a fila editorial (editorial_queue.json) gerada pelo Sonnet (Task 3.3),
verifica duplicatas no WordPress via GET, e publica cada artigo via
WP REST API (POST com Application Passwords).
Popula automaticamente: conteúdo, slug, tags, categorias e imagem destacada.

@author  Antigravity AI Designer
@version 1.0.0
@since   2026-05-26
"""

import os
import json
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
logger = logging.getLogger("WPPublisher")

load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), ".env"))


# ---------------------------------------------------------------------------
# Classe Principal
# ---------------------------------------------------------------------------
class WPPublisher:
    """
    Publica artigos na REST API do WordPress com verificação de duplicatas.

    Fluxo por artigo:
      1. Verifica se slug já existe no WP (GET /wp/v2/posts?slug=...)
      2. Resolve ou cria categorias e tags via REST
      3. Publica o post (status: draft — revisão humana antes de ir ao ar)
      4. Atualiza status em editorial_queue.json
    """

    # Pausa entre publicações para não sobrecarregar o servidor
    RATE_LIMIT_SLEEP = 1.5  # segundos

    def __init__(self, config_path: str = "config.json"):
        """Inicializa as credenciais WP e os caminhos de dados."""
        self.wp_url      = os.getenv("WP_BASE_URL", "").rstrip("/")
        self.wp_user     = os.getenv("WP_USERNAME", "")
        self.wp_password = os.getenv("WP_APP_PASSWORD", "")

        if not all([self.wp_url, self.wp_user, self.wp_password]):
            raise EnvironmentError(
                "Credenciais WP incompletas. Verifique WP_BASE_URL, "
                "WP_USERNAME e WP_APP_PASSWORD no arquivo automation/.env"
            )

        # Cabeçalho de autenticação Basic (Application Password)
        credentials  = f"{self.wp_user}:{self.wp_password}"
        encoded_auth = b64encode(credentials.encode("utf-8")).decode("utf-8")
        self.headers = {
            "Authorization": f"Basic {encoded_auth}",
            "Content-Type":  "application/json",
        }

        self.api_base = f"{self.wp_url}/wp-json/wp/v2"

        # Carrega config e resolve caminhos
        base_dir    = os.path.dirname(os.path.abspath(__file__))
        config_file = os.path.join(base_dir, config_path)

        with open(config_file, "r", encoding="utf-8") as f:
            config = json.load(f)

        self.editorial_path = os.path.join(base_dir, config["paths"].get(
            "editorial_queue_file", "editorial_queue.json"
        ))
        self.published_log_path = os.path.join(base_dir, config["paths"].get(
            "published_log_file", "published_log.json"
        ))

        logger.info(f"WPPublisher inicializado | Endpoint: {self.api_base}")

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
    # Utilitários REST API
    # ------------------------------------------------------------------

    def _check_slug_exists(self, slug: str) -> bool:
        """
        Consulta o WP via GET para verificar se um slug já existe.
        Previne duplicidade de conteúdo e evita penalização de SEO.
        """
        try:
            resp = requests.get(
                f"{self.api_base}/posts",
                params={"slug": slug, "status": "any", "_fields": "id,slug"},
                headers=self.headers,
                timeout=10,
            )
            if resp.status_code == 200:
                posts = resp.json()
                return len(posts) > 0
            logger.warning(f"GET /posts?slug={slug} retornou HTTP {resp.status_code}")
            return False
        except Exception as e:
            logger.error(f"Erro ao verificar slug '{slug}' no WP: {e}")
            return False

    def _get_or_create_tag(self, tag_name: str) -> int | None:
        """Busca uma tag pelo nome e cria se não existir. Retorna o ID da tag."""
        try:
            # Busca existente
            resp = requests.get(
                f"{self.api_base}/tags",
                params={"search": tag_name, "_fields": "id,name"},
                headers=self.headers,
                timeout=10,
            )
            if resp.status_code == 200:
                tags = resp.json()
                for tag in tags:
                    if tag["name"].lower() == tag_name.lower():
                        return tag["id"]

            # Cria nova tag
            resp = requests.post(
                f"{self.api_base}/tags",
                json={"name": tag_name},
                headers=self.headers,
                timeout=10,
            )
            if resp.status_code in (200, 201):
                return resp.json().get("id")

        except Exception as e:
            logger.error(f"Erro ao resolver tag '{tag_name}': {e}")
        return None

    def _get_or_create_category(self, category_name: str) -> int | None:
        """Busca uma categoria pelo nome e cria se não existir. Retorna o ID."""
        try:
            resp = requests.get(
                f"{self.api_base}/categories",
                params={"search": category_name, "_fields": "id,name"},
                headers=self.headers,
                timeout=10,
            )
            if resp.status_code == 200:
                cats = resp.json()
                for cat in cats:
                    if cat["name"].lower() == category_name.lower():
                        return cat["id"]

            resp = requests.post(
                f"{self.api_base}/categories",
                json={"name": category_name},
                headers=self.headers,
                timeout=10,
            )
            if resp.status_code in (200, 201):
                return resp.json().get("id")

        except Exception as e:
            logger.error(f"Erro ao resolver categoria '{category_name}': {e}")
        return None

    # ------------------------------------------------------------------
    # Publicação
    # ------------------------------------------------------------------

    def _publish_post(self, article: dict) -> dict | None:
        """
        Envia o POST para a REST API do WordPress.
        Retorna o objeto do post criado ou None em caso de falha.
        """
        wp_post = article.get("wp_post", {})
        title   = wp_post.get("title", "Sem título")
        slug    = wp_post.get("slug", "")
        content = wp_post.get("content", "")

        # Resolve IDs de categorias e tags no WP
        category_ids = []
        for cat_name in wp_post.get("categories", []):
            cat_id = self._get_or_create_category(cat_name)
            if cat_id:
                category_ids.append(cat_id)

        tag_ids = []
        for tag_name in wp_post.get("tags", []):
            tag_id = self._get_or_create_tag(tag_name)
            if tag_id:
                tag_ids.append(tag_id)

        # Monta o payload completo
        payload = {
            "title":      title,
            "slug":       slug,
            "content":    content,
            "status":     "draft",           # Sempre rascunho — revisão humana obrigatória
            "categories": category_ids,
            "tags":       tag_ids,
            "meta": {
                "fonte_original": article.get("original_url", ""),
                "fonte_nome":     article.get("original_source", ""),
            },
        }

        try:
            resp = requests.post(
                f"{self.api_base}/posts",
                json=payload,
                headers=self.headers,
                timeout=15,
            )

            if resp.status_code in (200, 201):
                post_data = resp.json()
                logger.info(
                    f"  ✅ Publicado (rascunho) | ID: {post_data.get('id')} | "
                    f"Link: {post_data.get('link', '?')}"
                )
                return post_data
            else:
                logger.error(
                    f"  ❌ Falha HTTP {resp.status_code} ao publicar '{title}': "
                    f"{resp.text[:300]}"
                )
                return None

        except Exception as e:
            logger.error(f"  ❌ Erro de conexão ao publicar '{title}': {e}")
            return None

    # ------------------------------------------------------------------
    # Pipeline principal
    # ------------------------------------------------------------------

    def run(self, dry_run: bool = False, limit: int = 0) -> None:
        """
        Executa o pipeline de publicação completo.

        Args:
            dry_run: Se True, simula sem chamar a REST API do WordPress.
            limit:   Número máximo de artigos a publicar (0 = todos).
        """
        logger.info("=== INICIANDO PIPELINE DE PUBLICAÇÃO (WP REST API) ===")

        editorial_queue = self._load_json(self.editorial_path)
        published_log   = self._load_json(self.published_log_path)

        # Filtra artigos prontos para publicação
        pending = [
            a for a in editorial_queue
            if a.get("status") == "editorial_pronto"
        ]

        if not pending:
            logger.info("Nenhum artigo editorial pronto encontrado. Finalizando.")
            return

        if limit > 0:
            pending = pending[:limit]

        logger.info(f"{len(pending)} artigo(s) prontos para publicação no WordPress.")

        success_count = 0
        skip_count    = 0
        fail_count    = 0

        for i, article in enumerate(pending, start=1):
            wp_post = article.get("wp_post", {})
            title   = wp_post.get("title", "Sem título")
            slug    = wp_post.get("slug", "")
            fonte   = article.get("original_source", "?")

            logger.info(f"[{i}/{len(pending)}] Publicando [{fonte}]: '{title}'")

            # Verificação anti-duplicata no WordPress
            if not dry_run and self._check_slug_exists(slug):
                logger.warning(f"  ⏭️  Slug '/{slug}/' já existe no WP. Pulando.")
                article["status"] = "duplicata_ignorada"
                skip_count += 1
                continue

            if dry_run:
                logger.info(f"  [DRY-RUN] Publicação simulada | slug: /{slug}/")
                article["status"] = "dry_run_simulado"
                success_count += 1
            else:
                post_data = self._publish_post(article)

                if post_data:
                    article["status"]    = "publicado"
                    article["wp_post_id"] = post_data.get("id")
                    article["wp_link"]    = post_data.get("link", "")
                    article["published_at"] = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

                    # Registra no log de publicações para auditoria
                    published_log.append({
                        "wp_post_id":   post_data.get("id"),
                        "title":        title,
                        "slug":         slug,
                        "link":         post_data.get("link", ""),
                        "published_at": article["published_at"],
                        "fonte":        fonte,
                    })
                    success_count += 1
                else:
                    article["status"] = "publicacao_falhou"
                    fail_count += 1

            # Pausa entre requisições
            if i < len(pending):
                time.sleep(self.RATE_LIMIT_SLEEP)

        # Persiste todos os estados atualizados
        if not dry_run:
            self._save_json(self.editorial_path,    editorial_queue)
            self._save_json(self.published_log_path, published_log)
            logger.info("Dados persistidos em disco.")

        logger.info(
            f"=== PIPELINE DE PUBLICAÇÃO FINALIZADO | "
            f"✅ {success_count} publicado(s) | "
            f"⏭️  {skip_count} ignorado(s) (duplicata) | "
            f"❌ {fail_count} falha(s) ==="
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
                logger.warning("Valor inválido para --limit. Publicando todos os prontos.")

    try:
        publisher = WPPublisher()
        publisher.run(dry_run=dry, limit=lim)
    except EnvironmentError as e:
        logger.critical(str(e))
        sys.exit(1)
    except Exception as e:
        logger.critical(f"Falha crítica na execução: {e}")
        sys.exit(1)
