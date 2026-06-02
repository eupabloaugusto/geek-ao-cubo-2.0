# Plano de Engenharia — Geek ao Cubo (modomaratona.com)

> Documento de referência técnica (engenharia) para a evolução do portal **Geek ao Cubo**, com arquitetura em camadas, contratos entre subsistemas e um roadmap de implementação alinhado ao briefing e ao sistema Atomic Design do tema `geek-ao-cubo`.

## Objetivos de produto (o “porquê” técnico)

- **SEO-first**: escalar tráfego orgânico para \(300k+/mês\) com páginas de catálogo e páginas-pillars que suportem clusters.
- **Performance-first**: Core Web Vitals alto (mobile ≥ 90) com HTML semântico, sem jQuery e assets sob demanda.
- **Monetização**: AdSense como principal (2 ads/página como baseline) + afiliados (`rel="sponsored"`) como secundário.
- **Automação**: ingestão e atualização de dados via Jikan/MAL + pipeline Python para conteúdo editorial assistido por IA.

---

## Navegação do portal (estado atual + lacunas)

### Já implementado
- **Home**: `front-page.php` (editorial + seções dinâmicas).
- **Artigos**: `single.php` + organismos de artigo (`secao-artigo-unico`, `secao-pos-artigo`).
- **Catálogo de Anime/Mangá**: **catálogo de animes** concluído (componentes + helpers + template); **catálogo de mangá** ainda não existe como domínio completo.
- **Página de Anime**: `single-anime.php` concluída.

### Falta implementar (escopo de navegação informado)
- **Página de Mangá**: domínio + CPT/ACF + templates + SEO.
- **Página de Dublador**: domínio + modelagem + template + links internos a partir de personagens/dubladores.

---

## Arquitetura em camadas (visão completa)

```mermaid
flowchart TD
  subgraph PresentationLayer[Camada_de_Apresentacao_(Tema)]
    Templates[Templates_WP_(front-page,_archive,_single,_search)]
    Atomic[Atomic_Design_(atoms/molecules/organisms)]
    Assets[Assets_(CSS/JS)_sob_demanda]
  end

  subgraph ApplicationLayer[Camada_de_Aplicacao_(Regras_de_negocio)]
    Helpers[Query_Helpers_(includes/cpt-helpers.php)]
    Integrations[Integracoes_(Jikan,_YouTube,_SEO_schema,_Monetizacao)]
  end

  subgraph DomainLayer[Camada_de_Dominio_(Conteudo)]
    CPTAnime[CPT_anime]
    CPTTemporada[CPT_temporada]
    CPTReview[CPT_review]
    CPTManga[CPT_manga_(novo)]
    Tax[Taxonomias_(genero,status_exibicao,+novas)]
    ACF[ACF_groups_(acf-json)]
  end

  subgraph DataLayer[Camada_de_Dados]
    WPDB[(WordPress_DB)]
    Transients[Transients_cache]
    LocalJSON[ACF_Local_JSON]
    JikanCache[(Cache_API_Jikan)]
  end

  subgraph AutomationLayer[Camada_de_Automacao_(Python)]
    Import[Importacao_catalogo_(waves)]
    Editorial[Pipeline_editorial_(RSS/scrape->IA->REST)]
    Sync[Sync_diaria_(notas/status/episodios)]
  end

  subgraph ExternalServices[Servicos_Externos]
    Jikan[Jikan_MAL_API]
    Groq[Groq/LLM]
    WPApi[WordPress_REST_API]
    Adsense[Google_AdSense]
  end

  Templates --> Atomic
  Atomic --> Assets
  Templates --> Helpers
  Helpers --> WPDB
  Helpers --> Transients
  Integrations --> Jikan
  AutomationLayer -->|publica/atualiza| WPApi
  WPApi --> WPDB
  Editorial --> Groq
  Integrations --> Adsense
  ACF --> LocalJSON
  CPTManga --> ACF
```

---

## Camada de Apresentação (Tema `geek-ao-cubo`)

### 1) Atomic Design (contrato de UI)
- **Unidade de composição**: `mm_render_component($type,$name,$args)` renderiza e enfileira assets do componente.
- **Regra 1:1**: cada componente possui arquivo `.php` + `.css` (e `.js` quando interativo).
- **BEM estrito**: classes e hierarquia do DOM são contrato com o CSS (não mudar sem atualizar CSS/Storybook).
- **Tokens obrigatórios**: todo valor visual deve vir de `design-tokens.css`.

### 2) Templates (rotas principais)
- **Home**: `front-page.php`
- **Anime**: `archive-anime.php`, `single-anime.php`
- **Episódio**: `single-episodio.php`
- **Temporada**: `single-temporada.php`
- **Review**: `single-review.php`
- **Artigos**: `single.php`, `category.php`, `tag.php`, `archive.php`, `search.php`, `404.php` (alguns ainda pendentes conforme fase 4)

### 3) Assets (CSS/JS)
- **Global**: `design-tokens.css` + `style.css` + utilitários JS globais (`assets/js/mm-utils.js`) + CSS específicos por template.
- **Componentes**: CSS/JS sob demanda via `mm_render_component()`.
- **Nota operacional**: quando um componente é renderizado após `wp_head()`, o tema deve garantir que o CSS seja impresso (evitar páginas “sem estilo”).

---

## Camada de Domínio (conteúdo WordPress)

### CPTs existentes (núcleo)
- **`anime`**: entidade central do catálogo.
- **`temporada`**: agrupa `anime` (ACF relationship multi).
- **`review`**: análise editorial ligada a `anime`.

### Dados Dinâmicos ("Lean WP")
Para manter o banco de dados leve e com alta performance, dados com alto volume ou alta frequência de atualização **não** são salvos como CPTs. Eles são renderizados sob demanda via chamadas à API do Jikan/MAL e persistidos em *transients* no WPDB. Exemplos:
- **`episodio`**: renderizados dinamicamente nas páginas de animes/temporadas.
- **`personagem` e `dublador`**: consultados on-the-fly para preencher as seções detalhadas.
- **`trilha sonora`** e **`recomendacoes`**: processados via fallback/cache.

### Taxonomias existentes
- **`genero`** (CPT `anime`)
- **`status_exibicao`** (CPT `anime`)

### ACF (fonte de verdade do modelo)
- **ACF Local JSON** versionado em `geek-ao-cubo/acf-json/`.
- Relações bidirecionais devem ser garantidas por código para evitar loops caros.

---

## Camada de Aplicação (regras e helpers)

### `includes/cpt-helpers.php` (queries e integrações)
- **Queries**: helpers de catálogo (letra/filtros), episódios do anime, reviews, temporada, etc.
- **Integrações**:
  - **Jikan/MAL** (dados dinâmicos e cache em transients).
  - **YouTube** (extração de ID e `embed-video`).

### Observabilidade mínima (backend WP)
- Logs opcionais em modo debug para falhas de integração externa (HTTP 429/5xx).
- Métrica prática: taxa de “retorno vazio” nas integrações e tempo médio de resposta.

---

## Camada de Integração Externa (APIs)

### 1) Jikan API (MyAnimeList)
- **Dados no backend** (PHP) com cache em **transients**: relations, recommendations, characters/staff, etc.
- **Dados no cliente** (JS) para atualização “real-time” sem pesar o servidor: ex.: `hero-anime.js`.

### 2) MangaDex API (Mangás)
- **Complementação de Dados**: O MyAnimeList (via Jikan) não retorna a listagem estruturada de volumes e capítulos de mangás. Utilizamos a API v5 da MangaDex como solução complementar (`get_manga_aggregate`).
- **Resolução de Identidade (Cross-Reference)**: O cruzamento é feito no backend buscando o MAL ID na base da MangaDex, retornando um UUID. Esse UUID é persistido de forma permanente no postmeta `manga_mangadex_uuid` do WordPress, garantindo máximo desempenho e evitando buscas de lookup redundantes.

### Regras de resiliência e Cache Avançado
- **Rate limit (429) e Throttling:** Throttle hardcoded de `usleep(350000)` (350ms) antes de cada requisição à API, garantindo respeito irrestrito ao limite de 3req/s.
- **Stale-While-Revalidate (SWR):** Interceptação do cache nativo (`get_option('_transient_timeout_')`). Quando expirado, devolve o cache "Stale" pro cliente instantaneamente e agenda `wp_schedule_single_event` para atualização silenciosa em background via WP-Cron, zerando o tempo de espera do usuário.
- **Mutex Lock (Anti-Manada):** Em caso de pico de acesso a cache expirado (Cache Stampede), uma tranca de 2 minutos (`set_transient('lock_jikan_...')`) previne múltiplas threads de engatilharem o WP-Cron repetidamente.
- **Fallbacks:** Em caso de erro na API (4xx, 5xx), a camada de SWR serve como fallback permanente até que a API retorne status 200, protegendo a quebra do layout.
- **Contratos de retorno**: retornar sempre estruturas previsíveis para o front (arrays com chaves consistentes).

---

## Camada de Automação (Python)

### Responsabilidades
- **Importação em ondas** do catálogo (Top -> Airing -> Long tail).
- **Sincronização diária** (notas/status/membros/episódios).
- **Pipeline editorial**: coleta -> panorama factual -> geração editorial -> publicação via REST.

### Contratos com o WP
- **REST API** autenticada (Application Passwords).
- **Idempotência**: checar duplicatas antes de criar.
- **Logs** e rota de alerta (Slack/Discord) para falhas.

---

## SEO & Schema (camada transversal)

### Requisitos globais
- **H1 único** por página e hierarquia semântica.
- **Alt text** obrigatório em imagens (componentes de imagem exigem parâmetro `alt`).
- **BreadcrumbList** em navegação.
- JSON-LD por tipo:
  - `anime` → `TVSeries`
  - `episodio` → `TVEpisode`
  - `review` → `Review` (ou `Article` com `reviewRating`)
  - `artigo` → `Article`

---

## Monetização (camada transversal)

- **AdSense**: componente atômico `anuncio-adsense` + inserção no conteúdo.
- **Afiliados**: filtro em `the_content` forçando `rel="sponsored" target="_blank"`.
- **Adblock**: aviso não intrusivo (sem interstitial bloqueante).

---

## Segurança, performance e operação (camada transversal)

- **Sem jQuery** e scripts no footer (`defer`/`in_footer` quando aplicável).
- **Cache**: WP Rocket / CDN (Cloudflare) em produção.
- **Hardening**: 2FA, permissões 644/755, backup diário, ocultar login/admin, WAF.

---

## Roadmap de engenharia para completar a navegação (Mangá + Dublador)

### A) Mangá — Catálogo e Página de Detalhe (novo domínio)

#### A1) Modelo (CPT + ACF + taxonomias)
- **Novo CPT**: `manga`
- **Campos mínimos ACF (sugestão prática)**:
  - `manga_id_mal` (NUMERIC)
  - `manga_sinopse` (textarea/editor)
  - `manga_imagem_capa_url`
  - `manga_nota_mal`
  - `manga_rank`, `manga_popularidade`, `manga_membros` (quando aplicável)
  - `manga_status_publicacao` (taxonomia ou select)
  - `manga_ano_inicio`, `manga_ano_fim`
  - Relacionamentos:
    - `manga_anime_relacionado` (relationship opcional para `anime` quando existir adaptação)

#### A2) Templates
- **Catálogo**: `archive-manga.php` (espelhar a arquitetura do `archive-anime.php`)
  - Reusar `barra-filtros` / `barra-filtros-mobile` / `lista-catalogo` com ajustes de labels e helpers.
- **Detalhe**: `single-manga.php`
  - Reusar padrões do `hero-anime` via um novo organismo `hero-manga` (preferível) ou adaptar um `hero-entry` genérico.

#### A3) Integração Jikan
- Implementar `mm_get_jikan_data()` equivalente para `manga` (endpoint `/v4/manga/{id}/...`).
- Cache e resiliência idênticos ao anime.

#### A4) SEO
- Schema recomendado: `Book` (ou `CreativeWork`) com `aggregateRating` quando houver.
- Slug/URL consistente com o restante do portal.

---

### B) Dublador — Páginas e links internos (Novo Domínio)

#### B1) Decisão de modelagem (Arquitetura Atual)
- Optamos pela criação de um **CPT físico (`dublador`)** para estruturar páginas dedicadas (ex: `/dublador/kenjiro-tsuda/`) visando alto desempenho em SEO (indexação direta e Schema específico).
- Apenas dubladores notáveis ganham posts físicos, mantendo o banco de dados saudável.

#### B2) Modelo de Dados Híbrido
- O ACF armazena dados-chave (`dublador_id_mal`, bio).
- O restante dos dados (trabalhos de voz, foto atualizada) é resolvido dinamicamente via Jikan API no momento do carregamento da página, unindo o melhor dos dois mundos: URL fixa local + dados sempre quentes do MAL.

#### B3) Integração Jikan (Operacional)
- Endpoint principal: `person/{id}/full` e conexões a partir de `anime/{id}/characters`.
- Estratégia de estabilidade: As chamadas Jikan são postas em cache persistente (`set_transient`) evitando rate limits 429 e dependência estrita do serviço de terceiros a cada load da página.

#### B4) Conectar navegação (Cruzamento Inteligente)
- `card-personagem-dublador` e cards de `trabalhos-voz` interceptam os dados da Jikan:
  - **Se existir post local** correspondente ao `mal_id`, gera link para a página local.
  - **Se NÃO existir post local**, o fallback gera link para a **busca interna do site** (`/?s=Nome`), mantendo o usuário retido no nosso ecossistema e abandonando o link externo pro MyAnimeList.

---

## Critérios de pronto (Definition of Done — engenharia)

- **Camadas respeitadas**:
  - templates não fazem queries complexas inline: usam helpers.
  - UI apenas via Atomic Design (componentes).
- **SEO**:
  - H1 único + breadcrumbs + schema adequado + alt text.
- **Performance**:
  - sem jQuery, assets sob demanda, sem layout shift relevante.
- **Documentação**:
  - componente novo/alterado → doc em `geek-ao-cubo/docs/` + registro em `docs/changelog.md`.
- **Compatibilidade**:
  - text domain `geek-ao-cubo` em todas as strings.

---


