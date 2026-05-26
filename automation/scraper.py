#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Scraper Multilíngue (Task 3.1)
Coleta notícias e artigos de portais estrangeiros (Natalie-JP, ANN-US) via RSS,
baixa e higieniza o corpo das matérias usando BeautifulSoup4 e estrutura os dados.

@author Antigravity AI Designer
@version 2.0.0
@since 2026-05-26
"""

import os
import json
import logging
import requests
import feedparser
from bs4 import BeautifulSoup
from datetime import datetime

# Configuração de Logging elegante
logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)
logger = logging.getLogger("AnimeNewsScraper")


class AnimeNewsScraper:
    def __init__(self, config_path="config.json"):
        """Inicializa o scraper carregando as configurações e as bases de dados locais."""
        self.config_dir = os.path.dirname(os.path.abspath(__file__))
        self.config_path = os.path.join(self.config_dir, config_path)
        
        # Carrega arquivo de configurações
        if not os.path.exists(self.config_path):
            raise FileNotFoundError(f"Arquivo de configuração não encontrado em: {self.config_path}")
            
        with open(self.config_path, "r", encoding="utf-8") as f:
            self.config = json.load(f)
            
        # Define caminhos absolutos para persistência de dados
        self.db_path = os.path.join(self.config_dir, self.config["paths"]["database_file"])
        self.raw_path = os.path.join(self.config_dir, self.config["paths"]["raw_articles_file"])
        
        # Inicializa o banco local de links processados (evita duplicidade absoluta)
        self.processed_urls = self._load_processed_urls()
        
    def _load_processed_urls(self):
        """Carrega do disco a lista de URLs já processadas anteriormente."""
        if os.path.exists(self.db_path):
            try:
                with open(self.db_path, "r", encoding="utf-8") as f:
                    return set(json.load(f))
            except Exception as e:
                logger.error(f"Erro ao carregar banco de dados local: {e}. Resetando base.")
        return set()
        
    def _save_processed_url(self, url):
        """Persiste uma nova URL no banco de dados local para blindagem contra duplicatas."""
        self.processed_urls.add(url)
        try:
            with open(self.db_path, "w", encoding="utf-8") as f:
                json.dump(list(self.processed_urls), f, ensure_ascii=False, indent=2)
        except Exception as e:
            logger.error(f"Erro ao salvar URL no banco de dados local: {e}")

    def fetch_rss_feeds(self):
        """Varre todos os feeds RSS cadastrados na configuração buscando novos posts."""
        logger.info("Iniciando varredura dos feeds RSS estrangeiros...")
        new_entries = []
        
        for source in self.config["sources"]:
            name = source["name"]
            rss_url = source["rss_url"]
            lang = source["language"]
            selectors = source["selectors"]
            
            logger.info(f"Escaneando fonte: {name} ({rss_url})")
            
            try:
                # Parse do XML do RSS feed
                feed = feedparser.parse(rss_url)
                
                if feed.bozo:
                    logger.warning(f"Aviso: Feed parser detectou anomalia XML no feed de {name}, prosseguindo com tolerância.")
                    
                limit = self.config["scraper_settings"]["max_articles_per_source"]
                count = 0
                
                for entry in feed.entries:
                    if count >= limit:
                        break
                        
                    url = getattr(entry, "link", "")
                    title = getattr(entry, "title", "")
                    
                    if not url or not title:
                        continue
                        
                    # Checagem de duplicidade: ignora se já tiver processado
                    if url in self.processed_urls:
                        continue
                        
                    logger.info(f"Nova notícia encontrada: '{title}'")
                    new_entries.append({
                        "title": title,
                        "url": url,
                        "source": name,
                        "language": lang,
                        "selectors": selectors,
                        "published_raw": getattr(entry, "published", "")
                    })
                    count += 1
                    
            except Exception as e:
                logger.error(f"Falha crítica ao ler feed RSS de {name}: {e}")
                
        return new_entries

    def extract_clean_content(self, url, selectors):
        """Visita a página externa do post e extrai apenas o texto limpo do artigo."""
        headers = {
            "User-Agent": self.config["scraper_settings"]["user_agent"]
        }
        timeout = self.config["scraper_settings"]["timeout_seconds"]
        
        try:
            response = requests.get(url, headers=headers, timeout=timeout)
            if response.status_code != 200:
                logger.error(f"Erro HTTP {response.status_code} ao acessar: {url}")
                return None
                
            html_content = response.text
            soup = BeautifulSoup(html_content, "html.parser")
            
            # Limpa elementos inúteis da página antes de raspar
            for trash in soup(["script", "style", "iframe", "form", "footer", "nav", "aside", "noscript"]):
                trash.decompose()
                
            # Tenta encontrar o corpo da notícia utilizando os seletores prioritários ordenados
            article_body = None
            for selector in selectors:
                article_body = soup.select_one(selector)
                if article_body:
                    break
                    
            # Se não encontrar por nenhum seletor configurado, usa fallback genérico de tags de artigo
            if not article_body:
                article_body = soup.find("article")
                
            if not article_body:
                # Último recurso: usa parágrafos avulsos
                paragraphs = soup.find_all("p")
                text = "\n\n".join([p.get_text().strip() for p in paragraphs if len(p.get_text().strip()) > 30])
            else:
                # Extrai os parágrafos de dentro do container principal limpo
                paragraphs = article_body.find_all(["p", "h2", "h3"])
                text = "\n\n".join([p.get_text().strip() for p in paragraphs if p.get_text().strip()])
                
            # Limpeza fina de strings vazias ou linhas de copyright e redes sociais
            lines = [line.strip() for line in text.split("\n") if line.strip()]
            cleaned_text = "\n".join(lines)
            
            if len(cleaned_text) < 150:
                logger.warning(f"Texto extraído excessivamente curto ({len(cleaned_text)} char), possível falha no seletor de: {url}")
                
            return cleaned_text
            
        except Exception as e:
            logger.error(f"Erro ao extrair conteúdo da página de {url}: {e}")
            return None

    def run(self, dry_run=False):
        """Executa o pipeline completo: varre RSS, raspa conteúdo limpo e persiste dados."""
        logger.info("=== INICIANDO EXECUÇÃO DA AUTOMAÇÃO (COLETA E RASPAÇÃO) ===")
        
        new_entries = self.fetch_rss_feeds()
        
        if not new_entries:
            logger.info("Nenhuma novidade encontrada nos portais estrangeiros. Finalizando com sucesso.")
            return
            
        logger.info(f"Total de {len(new_entries)} novos posts identificados. Iniciando raspagem individual...")
        
        scraped_articles = []
        
        for entry in new_entries:
            url = entry["url"]
            title = entry["title"]
            source = entry["source"]
            lang = entry["language"]
            selectors = entry["selectors"]
            
            logger.info(f"Raspando [{source}]: '{title}'...")
            
            clean_body = self.extract_clean_content(url, selectors)
            
            if not clean_body:
                logger.warning(f"Não foi possível extrair corpo textual limpo para: {url}. Pulando.")
                continue
                
            article_data = {
                "title": title,
                "url": url,
                "source": source,
                "language": lang,
                "raw_body": clean_body,
                "scraped_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                "status": "pending" # Preparado para a Task 3.2 (Groq Panorama)
            }
            
            scraped_articles.append(article_data)
            
            # Marca como processado na base local para nunca repetir
            if not dry_run:
                self._save_processed_url(url)
                
        if not scraped_articles:
            logger.info("Nenhum post pôde ser estruturado. Finalizando.")
            return
            
        # Carrega posts pendentes anteriores da fila se existirem
        existing_raw = []
        if os.path.exists(self.raw_path):
            try:
                with open(self.raw_path, "r", encoding="utf-8") as f:
                    existing_raw = json.load(f)
            except Exception as e:
                logger.error(f"Erro ao ler raw_articles.json anterior: {e}")
                
        # Junta a fila de novidades
        combined_raw = scraped_articles + existing_raw
        
        if not dry_run:
            try:
                with open(self.raw_path, "w", encoding="utf-8") as f:
                    json.dump(combined_raw, f, ensure_ascii=False, indent=2)
                logger.info(f"Sucesso! {len(scraped_articles)} novas matérias de anime salvas na fila para tradução/panorama em: {self.raw_path}")
            except Exception as e:
                logger.error(f"Falha ao salvar raw_articles.json no disco: {e}")
        else:
            logger.info(f"[DRY-RUN] Processamento concluído. {len(scraped_articles)} matérias coletadas em memória com sucesso.")
            
        logger.info("=== PIPELINE DE COLETA FINALIZADO COM SUCESSO ===")


# Execução direta se invocado via CLI
if __name__ == "__main__":
    import sys
    dry = "--dry-run" in sys.argv
    try:
        scraper = AnimeNewsScraper()
        scraper.run(dry_run=dry)
    except Exception as e:
        logger.critical(f"Falha crítica na execução do script: {e}")
