#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Geek ao Cubo (modomaratona.com) — Módulo de Panorama Factual (Task 3.2)
Lê a fila de artigos brutos coletados pelo scraper (raw_articles.json),
chama a Groq API (llama-3.3-70b-versatile, Free Tier) para traduzir e condensar
cada matéria estrangeira em um "Panorama Factual" limpo em PT-BR,
e salva a fila enriquecida (panorama_queue.json) para consumo da Task 3.3.

@author  Antigravity AI Designer
@version 1.0.0
@since   2026-05-26
"""

import os
import json
import time
import logging
from datetime import datetime
from groq import Groq
from dotenv import load_dotenv

# ---------------------------------------------------------------------------
# Configuração de Logging
# ---------------------------------------------------------------------------
logging.basicConfig(
    level=logging.INFO,
    format="[%(asctime)s] %(levelname)s: %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("GroqPanorama")

# Carrega variáveis de ambiente do arquivo .env (na pasta automation/)
load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), ".env"))


# ---------------------------------------------------------------------------
# Prompt do Sistema — Tradução + Condensação em Panorama Factual
# ---------------------------------------------------------------------------
SYSTEM_PROMPT = """Você é um jornalista factual especializado em anime e cultura geek japonesa.
Sua única função é receber o texto bruto de uma matéria estrangeira (em inglês ou japonês)
e produzir um "Panorama Factual" em português do Brasil.

REGRAS OBRIGATÓRIAS:
1. IDIOMA: O output DEVE ser 100% em português brasileiro (PT-BR) claro e direto.
2. FOCO FACTUAL: Extraia APENAS os fatos concretos — nomes, datas, números, anúncios,
   declarações de fontes e acontecimentos verificáveis. Descarte opiniões vagas.
3. ESTRUTURA DE SAÍDA (siga exatamente este formato JSON):
   {
     "titulo_sugerido": "Título factual e descritivo do panorama em PT-BR",
     "categoria_sugerida": "NOTÍCIA | LANÇAMENTO | RUMOR | ANÁLISE | GUIA",
     "tags_sugeridas": ["tag1", "tag2", "tag3"],
     "panorama": "Texto corrido do panorama factual, organizado em parágrafos curtos."
   }
4. TAMANHO DO PANORAMA: Mínimo de 150 palavras, máximo de 400 palavras.
   Seja denso em fatos, sem enrolação.
5. LIMPEZA: Ignore e descarte textos de navegação, publicidade, rodapés de copyright,
   menções a redes sociais e boilerplate editorial do portal de origem.
6. TÍTULOS E NOMES: Mantenha nomes de animes, personagens, estudios e diretores em sua
   grafia original em japonês/inglês dentro do texto quando relevante, mas escreva
   o texto em torno deles em PT-BR.
7. SAÍDA PURA: Retorne APENAS o JSON válido descrito acima. Sem texto antes ou depois.
   Sem blocos de código markdown. Apenas o objeto JSON."""


# ---------------------------------------------------------------------------
# Classe Principal
# ---------------------------------------------------------------------------
class GroqPanorama:
    """
    Consome a fila raw_articles.json, processa cada artigo pendente via Groq API
    e grava os panoramas em panorama_queue.json para consumo da Task 3.3.
    """

    # Modelo recomendado: rápido, gratuito e com contexto adequado para artigos
    MODEL = "llama-3.3-70b-versatile"

    # Limite de tokens para o panorama (custo baixo no free tier)
    MAX_OUTPUT_TOKENS = 700

    # Pausa entre chamadas para respeitar rate limits do free tier (~30 RPM)
    RATE_LIMIT_SLEEP = 2.5  # segundos

    def __init__(self, config_path: str = "config.json"):
        """Inicializa o cliente Groq e carrega os caminhos de dados."""
        api_key = os.getenv("GROQ_API_KEY")
        if not api_key:
            raise EnvironmentError(
                "Variável de ambiente GROQ_API_KEY não encontrada. "
                "Crie o arquivo automation/.env com: GROQ_API_KEY=gsk_..."
            )

        self.client = Groq(api_key=api_key)

        # Resolve caminhos relativos à pasta do script
        base_dir = os.path.dirname(os.path.abspath(__file__))

        # Lê config.json para caminhos padronizados
        config_file = os.path.join(base_dir, config_path)
        with open(config_file, "r", encoding="utf-8") as f:
            config = json.load(f)

        self.raw_path     = os.path.join(base_dir, config["paths"]["raw_articles_file"])
        self.panorama_path = os.path.join(base_dir, config["paths"].get(
            "panorama_queue_file", "panorama_queue.json"
        ))

        logger.info(f"GroqPanorama inicializado | Modelo: {self.MODEL}")
        logger.info(f"Fila de entrada : {self.raw_path}")
        logger.info(f"Fila de saída   : {self.panorama_path}")

    # ------------------------------------------------------------------
    # Persistência de dados
    # ------------------------------------------------------------------

    def _load_json(self, path: str) -> list:
        """Carrega uma lista JSON do disco. Retorna lista vazia se arquivo não existe."""
        if os.path.exists(path):
            try:
                with open(path, "r", encoding="utf-8") as f:
                    return json.load(f)
            except Exception as e:
                logger.error(f"Erro ao carregar {path}: {e}")
        return []

    def _save_json(self, path: str, data: list) -> None:
        """Persiste uma lista JSON no disco de forma segura."""
        try:
            with open(path, "w", encoding="utf-8") as f:
                json.dump(data, f, ensure_ascii=False, indent=2)
        except Exception as e:
            logger.error(f"Erro ao salvar {path}: {e}")

    # ------------------------------------------------------------------
    # Chamada à Groq API
    # ------------------------------------------------------------------

    def _build_user_message(self, article: dict) -> str:
        """Monta a mensagem de usuário com metadados e corpo da matéria."""
        lang_label = {"ja": "japonês", "en": "inglês"}.get(article.get("language", "en"), "inglês")
        return (
            f"FONTE: {article.get('source', 'Desconhecida')}\n"
            f"IDIOMA ORIGINAL: {lang_label}\n"
            f"TÍTULO ORIGINAL: {article.get('title', 'Sem título')}\n"
            f"URL DE REFERÊNCIA: {article.get('url', '')}\n\n"
            f"TEXTO BRUTO:\n{article.get('raw_body', '')[:4000]}"  # Trunca em 4k chars (seguro para free tier)
        )

    def _call_groq(self, article: dict) -> dict | None:
        """
        Envia o artigo bruto para a Groq API e retorna o panorama factual parseado.
        Retorna None em caso de falha ou resposta inválida.
        """
        user_message = self._build_user_message(article)

        try:
            response = self.client.chat.completions.create(
                model=self.MODEL,
                messages=[
                    {"role": "system", "content": SYSTEM_PROMPT},
                    {"role": "user",   "content": user_message},
                ],
                max_tokens=self.MAX_OUTPUT_TOKENS,
                temperature=0.3,   # Baixo: máxima consistência factual
                top_p=0.9,
                stream=False,
            )

            raw_output = response.choices[0].message.content.strip()

            # Valida e faz parse do JSON de saída
            panorama_data = json.loads(raw_output)

            # Checagem mínima de campos obrigatórios
            required_keys = {"titulo_sugerido", "categoria_sugerida", "tags_sugeridas", "panorama"}
            if not required_keys.issubset(panorama_data.keys()):
                logger.warning(f"Resposta da Groq com campos incompletos para: {article.get('url')}")
                logger.debug(f"Resposta recebida: {raw_output}")
                return None

            # Adiciona metadados de uso de tokens para monitoramento de custo
            usage = response.usage
            panorama_data["_meta_tokens"] = {
                "input_tokens":  usage.prompt_tokens,
                "output_tokens": usage.completion_tokens,
                "total_tokens":  usage.total_tokens,
            }

            return panorama_data

        except json.JSONDecodeError as e:
            logger.error(f"Groq retornou JSON inválido para '{article.get('title')}': {e}")
            logger.debug(f"Output bruto: {raw_output if 'raw_output' in dir() else 'N/A'}")
            return None

        except Exception as e:
            logger.error(f"Erro na chamada Groq para '{article.get('title')}': {e}")
            return None

    # ------------------------------------------------------------------
    # Pipeline principal
    # ------------------------------------------------------------------

    def run(self, dry_run: bool = False, limit: int = 0) -> None:
        """
        Executa o pipeline completo de geração de panoramas.

        Args:
            dry_run: Se True, não persiste resultados no disco (apenas loga).
            limit:   Número máximo de artigos a processar nesta execução (0 = todos).
        """
        logger.info("=== INICIANDO PIPELINE DE PANORAMA FACTUAL (GROQ) ===")

        raw_articles   = self._load_json(self.raw_path)
        panorama_queue = self._load_json(self.panorama_path)

        # Filtra apenas artigos pendentes
        pending = [a for a in raw_articles if a.get("status") == "pending"]

        if not pending:
            logger.info("Nenhum artigo pendente encontrado em raw_articles.json. Finalizando.")
            return

        if limit > 0:
            pending = pending[:limit]

        logger.info(f"{len(pending)} artigo(s) pendente(s) para processamento.")

        success_count = 0
        fail_count    = 0

        for i, article in enumerate(pending, start=1):
            title  = article.get("title", "Sem título")
            source = article.get("source", "?")
            url    = article.get("url", "")

            logger.info(f"[{i}/{len(pending)}] Processando [{source}]: '{title}'")

            panorama_data = self._call_groq(article)

            if panorama_data:
                # Constrói o registro final enriquecido para a fila da Task 3.3
                enriched = {
                    "original_title":     title,
                    "original_url":       url,
                    "original_source":    source,
                    "original_language":  article.get("language", "en"),
                    "scraped_at":         article.get("scraped_at", ""),
                    "panorama_at":        datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                    "status":             "panorama_pronto",
                    # Campos gerados pelo Groq
                    "titulo_sugerido":    panorama_data["titulo_sugerido"],
                    "categoria_sugerida": panorama_data["categoria_sugerida"],
                    "tags_sugeridas":     panorama_data["tags_sugeridas"],
                    "panorama":           panorama_data["panorama"],
                    "_meta_tokens":       panorama_data.get("_meta_tokens", {}),
                }

                panorama_queue.append(enriched)

                # Atualiza status do artigo original para evitar reprocessamento
                article["status"] = "panorama_pronto"

                logger.info(
                    f"  ✅ Panorama gerado | '{panorama_data['titulo_sugerido']}' "
                    f"| {panorama_data.get('_meta_tokens', {}).get('total_tokens', '?')} tokens"
                )
                success_count += 1

            else:
                # Marca como falha para inspecção manual, não bloqueia pipeline
                article["status"] = "panorama_falhou"
                fail_count += 1
                logger.warning(f"  ❌ Falha ao gerar panorama para: '{title}'")

            # Respeita o rate limit do free tier entre cada requisição
            if i < len(pending):
                logger.debug(f"Aguardando {self.RATE_LIMIT_SLEEP}s (rate limit free tier)...")
                time.sleep(self.RATE_LIMIT_SLEEP)

        # Persiste resultados
        if not dry_run:
            self._save_json(self.raw_path,     raw_articles)
            self._save_json(self.panorama_path, panorama_queue)
            logger.info(f"Dados persistidos em disco.")
        else:
            logger.info("[DRY-RUN] Nenhum arquivo foi modificado.")

        logger.info(
            f"=== PIPELINE FINALIZADO | ✅ {success_count} sucesso(s) | "
            f"❌ {fail_count} falha(s) ==="
        )


# ---------------------------------------------------------------------------
# Execução direta via CLI
# ---------------------------------------------------------------------------
if __name__ == "__main__":
    import sys

    dry  = "--dry-run" in sys.argv
    lim  = 0

    # Suporte a --limit=N para processar apenas N artigos por execução
    for arg in sys.argv:
        if arg.startswith("--limit="):
            try:
                lim = int(arg.split("=")[1])
            except ValueError:
                logger.warning("Valor inválido para --limit. Processando todos os pendentes.")

    try:
        pipeline = GroqPanorama()
        pipeline.run(dry_run=dry, limit=lim)
    except EnvironmentError as e:
        logger.critical(str(e))
        sys.exit(1)
    except Exception as e:
        logger.critical(f"Falha crítica na execução: {e}")
        sys.exit(1)
