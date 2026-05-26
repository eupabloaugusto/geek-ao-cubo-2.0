# 🚀 Backlog de Desenvolvimento — Fase 2 (modomaratona.com)

> **Status Atual do Projeto:** A base visual e estrutural (Fase 1) foi concluída com sucesso. Todos os 79 componentes do Atomic Design (36 átomos, 19 moléculas e 24 organismos) estão implementados com CSS fluido (`clamp()`), unidades relativas (`rem`), foco teclado acessível e conformidade WCAG 2.2 AA. O esqueleto do tema `hello-child` e o enfileiramento inteligente de assets estão ativos no `functions.php`.
> 
> **Objetivo da Fase 2 (Mês 3):** Transicionar da biblioteca de componentes estáticos (`storybook.html`) para um CMS WordPress dinâmico, automatizado por Inteligência Artificial (Groq API) e integrado à API Jikan (MyAnimeList), com monetização inicial ativa e otimização SEO de alto nível.

---

## 🗺️ Fluxo Arquitetural da Fase 2

O diagrama abaixo ilustra a integração entre as fontes de dados, a automação em Python, a estrutura de banco de dados do WordPress e a renderização dinâmica no frontend:

```mermaid
flowchart TD
    subgraph Fontes de Dados
        MAL[MyAnimeList Jikan API]
        AL[AniList GraphQL]
    end

    subgraph Pipeline de Automação (Python)
        Cron[Cron / Schedule Daemon] --> Fetch[Coletor de Dados]
        Fetch -->|Consome APIs| MAL
        Fetch -->|Consome APIs| AL
        Fetch --> Verify[Validador de Duplicatas]
        Verify -->|Evita posts repetidos| WP_Check[WP REST API - GET]
        Verify --> Prompt[Engine de Prompting]
        Prompt -->|Prompt Otimizado| Groq[Groq API - Llama 3]
        Groq -->|Texto Gerado e Revisado| Formatter[Formatador de Posts]
        Formatter -->|Gera HTML + SEO Meta| Publisher[Publicador WP REST API]
    end

    subgraph WordPress CMS (Backend)
        Publisher -->|Autenticação Segura| CPTs[Custom Post Types & ACF]
        CPTs --> DB[(Banco de Dados WP)]
    end

    subgraph Frontend Dinâmico (Cliente)
        DB -->|Templates PHP| Templates[single-anime.php / single-episodio.php]
        Templates -->|Renderiza Componentes| Atomic[Componentes Atomic Design]
        Atomic -->|JS Fetch cached| MAL
    end
```

---

## 🗃️ Mapeamento de CPTs e ACF (Advanced Custom Fields)

Para suportar a arquitetura dinâmica da Fase 2, os Custom Post Types (CPTs) devem ser configurados com a seguinte estrutura de metadados:

### 1. Custom Post Type: `anime`
* **Descrição:** Catálogo principal contendo informações do anime.
* **Mapeamento de Campos (ACF):**
  * `anime_sinopse` (Área de Texto)
  * `anime_nota_mal` (Número decimal) -> Atualizado periodicamente
  * `anime_studio` (Texto único)
  * `anime_ano` (Número)
  * `anime_duracao` (Texto)
  * `anime_ranking` (Número)
  * `anime_popularidade` (Número)
  * `anime_membros` (Número)
  * `anime_id_mal` (Texto/Número) -> ID único do MyAnimeList para sincronização
  * **Taxonomias Customizadas:**
    * `genero` (ex: Ação, Romance, Isekai)
    * `status_exibicao` (ex: Em Exibição, Finalizado, Brevemente)

### 2. Custom Post Type: `episodio`
* **Descrição:** Posts individuais por episódio.
* **Mapeamento de Campos (ACF):**
  * `ep_numero` (Número)
  * `ep_data_lancamento` (Data/Hora)
  * `ep_resumo` (Editor Visual WP)
  * `ep_anime_relacionado` (Relação -> Aponta para o CPT `anime`) -> Obrigatório para montagem hierárquica do breadcrumb e backlinks
  * `ep_trailer_url` (URL de Embed)

### 3. Custom Post Type: `temporada`
* **Descrição:** Agrupamento de animes por período sazonal.
* **Mapeamento de Campos (ACF):**
  * `temp_ano` (Número)
  * `temp_periodo` (Select: Inverno, Primavera, Verão, Outono)
  * `temp_animes` (Relação Relacional -> Múltiplos CPTs `anime`)

### 4. Custom Post Type: `review`
* **Descrição:** Avaliações editoriais completas feitas por redação/IA revisada.
* **Mapeamento de Campos (ACF):**
  * `review_nota` (Número decimal de 0 a 10)
  * `review_pros` (Repetidor de Texto)
  * `review_contras` (Repetidor de Texto)
  * `review_veredicto` (Área de Texto)
  * `review_anime_relacionado` (Relação -> Aponta para CPT `anime`)

---

## 📋 Detalhamento do Backlog Priorizado

### 🚀 Sprint 1: WordPress Core & CMS Setup (Backend)
> **Foco:** Estabelecer a fundação do banco de dados estruturado e as relações entre os tipos de conteúdo.

- [x] **Task 1.1: Registrar Custom Post Types via Código**
  * *Descrição:* Criar a estrutura PHP em `hello-child/functions.php` ou em um plugin utilitário para registrar os CPTs (`anime`, `episodio`, `temporada` e `review`) com URLs amigáveis e reescritas automáticas de slugs.
  * *SEO Match:* Garantir slugs semânticos (ex: `/animes/one-piece/`, `/episodios/one-piece-episodio-1100/`).
- [x] **Task 1.2: Criar Taxonomias Customizadas**
  * *Descrição:* Configurar taxonomias `genero` e `status_exibicao` para o CPT `anime`.
- [x] **Task 1.3: Importar Estruturas de Campos do ACF**
  * *Descrição:* Criar e mapear os grupos de campos ACF correspondentes a cada CPT (definidos na tabela de metadados acima). Exportar o arquivo JSON de configuração para `/hello-child/acf-json/` para versionamento do banco de dados.
- [x] **Task 1.4: Configurar Relações Bidirecionais no ACF**
  * *Descrição:* Implementar lógica no `functions.php` que garanta que ao associar um Episódio a um Anime, o Anime saiba quais episódios possui (facilitando loops eficientes sem sobrecarga de queries).

---

### 🎨 Sprint 2: Montagem de Templates PHP (Atomic Assembly)
> **Foco:** Conectar o código estático do Storybook à renderização real dinâmica alimentada pelos dados do banco de dados.

- [x] **Task 2.1: Implementar `single-anime.php`**
  * *Descrição:* Agrupar os organismos criados: `hero-anime` no topo, `sidebar-anime-info` à direita (ou abaixo no mobile), seções dinâmicas de `secao-relacionados`, `secao-personagens`, `secao-dubladores`, `secao-reviews`, `secao-estatisticas` e `secao-recomendacoes`.
  * *PHP Logic:* Substituir os dados mockupados do Storybook pelas funções ACF (`get_field()`, `the_field()`).
- [x] **Task 2.2: Implementar `single-episodio.php`**
  * *Descrição:* Montar a página de exibição de resumo do episódio. Integrar o átomo `embed-video` utilizando a URL do trailer cadastrado e renderizar as informações dinâmicas do Anime Pai através do campo de relacionamento ACF.
- [x] **Task 2.3: Implementar `archive-temporada.php`** (Implementado como `single-temporada.php` para post de estação)
  * *Descrição:* Criar o layout de grid sazonal (`organisms/grid-animes`) listando os animes da respectiva temporada com paginação de alta performance.
- [x] **Task 2.4: Implementar `single.php` (Notícias/Artigos)**
  * *Descrição:* Montar a página de leitura editorial utilizando o organismo `secao-artigo-unico` alimentado pelos dados nativos do WordPress (autor, data, tags, conteúdo do post) e acoplar a `secao-pos-artigo`.

---

### 🤖 Sprint 3: Automação de Conteúdo (Python & Stack Híbrida Groq/Sonnet)
> **Foco:** Desenvolver o script de back-office que raspa fontes estrangeiras, gera panoramas factuais com Groq (Gratuito) e redige os artigos humanizados finais com Claude 3.5 Sonnet.

- [x] **Task 3.1: Criar Módulo de Raspagem e Coleta Multilíngue**
  * *Descrição:* Desenvolver script Python 3.10+ para coletar artigos de portais estrangeiros de referência em anime/mangá (Japão, Coreia, EUA) via feed RSS ou scraping limpo (`BeautifulSoup`/`requests`).
- [x] **Task 3.2: Módulo de Panorama Factual (Groq API - Free Tier)**
  * *Descrição:* Configurar a biblioteca Groq Python utilizando modelos como `llama3-70b-8192` (no nível gratuito da API) para traduzir, filtrar ruídos e condensar a matéria estrangeira em um "Panorama Factual" limpo e em português de baixíssimo custo de tokens.
- [x] **Task 3.3: Módulo Editorial Humanizado (Claude 3.5 Sonnet API)**
  * *Descrição:* Conectar o panorama gerado pelo Groq à API do Claude 3.5 Sonnet para expandir os fatos em um artigo premium.
  * *Prompt Engineering:* Instruir o Sonnet a adotar um tom ágil, focado em público geek (sem clichês corporativos de IA), estruturar títulos semânticos (`H2`/`H3`), injetar links internos e formatar o post diretamente em HTML semântico limpo.
- [x] **Task 3.4: Conectar Publicador REST API & Validação de Duplicatas**
  * *Descrição:* Script faz verificação prévia no banco do WP via GET para evitar duplicidade de assunto e publica o artigo final via WP REST API (POST com *Application Passwords*), populando ACF de links externos, tags e imagem destacada.

---

### ⚡ Sprint 4: Integração de API Dinâmica no Frontend
> **Foco:** Fornecer dados em tempo real no cliente final para anular informações datadas sem impactar a velocidade do servidor.

- [x] **Task 4.1: Script de Atualização em Tempo Real (`hero-anime.js`)**
  * *Descrição:* Desenvolver lógica JS que, ao abrir a página do anime, faça um disparo assíncrono para a API pública Jikan MAL usando o `anime_id_mal` registrado na página.
- [x] **Task 4.2: Mecanismo de Cache Local no Cliente**
  * *Descrição:* Para mitigar o limite de taxa de requisições da Jikan API (HTTP 429), implementar cache via `localStorage` ou `sessionStorage` no navegador com expiração automática (TTL de 6 horas) para cada ID consultado.
- [x] **Task 4.3: Atualização de Pontuação e Status**
  * *Descrição:* Se a nota ou status de exibição retornados pela API forem diferentes do banco de dados, o JS atualiza de forma suave na tela o valor de `nota-mal` e `badge-status` com uma micro-animação.

---

### 💰 Sprint 5: Monetização, SEO Estrutural & Lançamento
> **Foco:** Habilitar a geração de receita, acessibilidade e indexação orgânica.

- [x] **Task 5.1: Integração de Anúncios AdSense no Componente `anuncio-adsense.php`**
  * *Descrição:* Configurar os blocos de anúncio assíncronos oficiais nos contêineres atômicos. Injetá-los dinamicamente no conteúdo dos posts baseando-se no tamanho do post (1 banner para posts curtos, até 3 para longos).
- [x] **Task 5.2: Ativar Banner de Aviso Adblock**
  * *Descrição:* Programar em `aviso-adblock.js` a detecção passiva de adblockers. Caso detectado, exibir o aviso atômico não intrusivo amigável sem bloquear a navegação, respeitando as regras do Google de interstitial não intrusivo.
- [x] **Task 5.3: Configurar Schema Markup Dinâmico (JSON-LD)**
  * *Descrição:* Configurar o PHP para imprimir esquemas de dados estruturados apropriados em cada CPT:
    * `Anime` -> `TVSeries` contendo rating, estúdio e elenco.
    * `Episódio` -> `TVEpisode` apontando para a série principal.
    * `Artigo/Review` -> `Article` ou `Review` com dados do autor e notas editoriais.
- [x] **Task 5.4: Enforçar Tags de Afiliados Automatizadas**
  * *Descrição:* Adicionar filtro de conteúdo no PHP (`the_content`) para buscar links externos apontando para parceiros (Shopee, Amazon, Mercado Livre) e injetar automaticamente o atributo `rel="sponsored" target="_blank"` para blindagem de SEO.

---

## 🏆 Definição de Pronto (Definition of Done)

Para que qualquer tarefa desta Fase 2 seja dada como concluída, ela deve passar pelos seguintes critérios de aceitação:

1. **Acessibilidade (WCAG 2.2 AA):** Elementos interativos devem manter foco claro via teclado (`:focus-visible`), contraste mínimo de texto de 4.5:1 e tags ARIA apropriadas.
2. **Performance (Core Web Vitals >= 90):** Imagens e embeds devem usar lazy loading nativo (`loading="lazy"`). Scripts JS devem carregar em modo `defer` ou `async` no rodapé.
3. **SEO Estrutural:** H1 único por página, imagens com `alt` dinâmicos preenchidos via banco de dados e links externos qualificados.
4. **Sem Bugs no PHP:** Todas as queries customizadas (`WP_Query`) devem ser seguidas de `wp_reset_postdata()`. Erros de PHP do tipo Warning/Notice devem estar zerados em ambiente de depuração.
