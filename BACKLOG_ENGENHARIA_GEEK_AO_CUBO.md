# Backlog de Engenharia — Geek ao Cubo (Portal completo)

> Backlog executável, derivado de `PLANO_DE_ENGENHARIA_GEEK_AO_CUBO.md`.  
> Foco: completar navegação e domínios faltantes (**Mangá** e **Dublador**) mantendo Atomic Design, SEO e performance.

---

## Convenções (obrigatórias)

- **Atomic Design**: tudo em `atoms/` → `molecules/` → `organisms/`; templates só orquestram.
- **Sem CSS inline / sem classes utilitárias** (BEM estrito).
- **Tokens**: 100% dos valores visuais via `design-tokens.css`.
- **Docs**: componente novo/alterado → doc em `geek-ao-cubo/docs/` + registro em `geek-ao-cubo/docs/changelog.md` + atualização do `storybook.html` (quando aplicável).
- **SEO**: H1 único, breadcrumbs quando fizer sentido, alt obrigatório, schema JSON-LD.
- **Integrações externas**: resilientes (rate limit 429, cache, fallback sem quebrar página).

---

## Sprint 0 — Preparação de domínio (decisões e contratos)

### Task 0.1 — Definir slugs e URLs canônicas
- **Objetivo**: padronizar rotas para evitar 404 e inconsistência de navegação.
- **Decisões**:
  - Catálogo Anime: `/animes/` (já existe)
  - Catálogo Mangá: `/mangas/`
  - Dubladores: `/dubladores/` (arquivo) e `/dublador/<nome>/` (single) ou `/dubladores/<nome>/` (preferível manter plural)
- **Entregáveis**:
  - Documento de rotas (seção adicionada ao changelog ou doc `docs/README.md`).
- **DoD**:
  - Rotas definidas e usadas de forma consistente em header/footer/breadcrumbs.

### Task 0.2 — Contrato de dados (ACF) para Mangá e Dublador
- **Objetivo**: definir campos mínimos e o que é “fonte de verdade” (WP vs Jikan).
- **Entregáveis**:
  - Especificação dos campos ACF (tabela) + tipos (NUMERIC/TEXT/URL/Relationship/Repeater).
- **DoD**:
  - Campos definidos com naming consistente (`manga_*`, `dublador_*`).

---

## Sprint 1 — Mangá (modelo + catálogo)

### Task 1.1 — Criar CPT `manga`
- **Arquivos**:
  - `geek-ao-cubo/includes/cpt-manga.php` (novo)
  - `geek-ao-cubo/functions.php` (incluir o arquivo no loader)
- **Requisitos**:
  - `public => true`, `has_archive => true`, suporte a `title`, `thumbnail`, `editor` (se aplicável)
  - rewrite slug alinhado ao Sprint 0
- **DoD**:
  - CPT aparece no admin e archive resolve sem 404 após flush.

### Task 1.2 — ACF: grupo de campos de `manga` (Local JSON)
- **Arquivos**:
  - `geek-ao-cubo/acf-json/group_manga.json` (novo)
- **Campos mínimos**:
  - `manga_id_mal` (NUMERIC)
  - `manga_sinopse`
  - `manga_imagem_capa_url`
  - `manga_nota_mal`, `manga_rank`, `manga_popularidade`, `manga_membros` (NUMERIC quando fizer sentido)
  - `manga_status_publicacao` (select/taxonomia)
  - `manga_ano_inicio`, `manga_ano_fim`
  - `manga_anime_relacionado` (Relationship opcional → `anime`)
- **DoD**:
  - Campos aparecem no editor do CPT `manga` e export JSON versionado.

### Task 1.3 — Helpers de query para catálogo de mangá
- **Arquivos**:
  - `geek-ao-cubo/includes/cpt-helpers.php` (extender)
- **Requisitos**:
  - `mm_query_mangas_por_letra()` (espelhar `mm_query_animes_por_letra`)
  - `mm_get_letras_ativas_catalogo_manga()` (transient próprio)
- **DoD**:
  - Helpers retornam queries consistentes e performáticas (no_found_rows quando aplicável).

### Task 1.4 — Template `archive-manga.php`
- **Arquivos**:
  - `geek-ao-cubo/archive-manga.php` (novo)
  - `geek-ao-cubo/archive-manga.css` (novo, se necessário)
- **Reuso recomendado**:
  - `organisms/barra-filtros`, `organisms/barra-filtros-mobile`, `organisms/lista-catalogo`
- **DoD**:
  - Navegação e filtro básico funcionam com GET, sem duplicar componentes.

### Task 1.5 — Navegação: linkar “Mangás” no header/drawer/footer
- **Arquivos**:
  - `geek-ao-cubo/organisms/header.php`
  - `geek-ao-cubo/organisms/navigation-drawer.php`
  - `geek-ao-cubo/organisms/footer.php`
- **DoD**:
  - Link interno aponta para o archive correto, sem hardcode incorreto.

---

## Sprint 2 — Mangá (página de detalhe + integração Jikan)

### Task 2.1 — Template `single-manga.php`
- **Arquivos**:
  - `geek-ao-cubo/single-manga.php` (novo)
  - `geek-ao-cubo/single-manga.css` (novo)
- **Conteúdo mínimo**:
  - Hero (novo `organisms/hero-manga` OU versão genérica/derivada)
  - Relações (quando existir `manga_anime_relacionado`)
  - Seções editoriais (opcional): recomendações, reviews (se aplicável)
- **DoD**:
  - Página renderiza com dados ACF mesmo sem Jikan (fallback).

### Task 2.2 — Integração Jikan para mangá (backend)
- **Arquivos**:
  - `geek-ao-cubo/includes/cpt-helpers.php`
- **Requisitos**:
  - Função `mm_get_jikan_manga_data($mal_id, $endpoint)` ou generalizar handler para `anime|manga`
  - Cache/transient + tratamento de 429/timeout conforme padrão
- **DoD**:
  - Dados dinâmicos não quebram página e respeitam rate limit.

### Task 2.3 — Schema/SEO para mangá
- **Arquivos**:
  - `geek-ao-cubo/includes/seo-schema.php` (extender)
- **Requisitos**:
  - JSON-LD tipo `Book` (ou `CreativeWork`) com `aggregateRating` quando disponível
- **DoD**:
  - Schema válido e coerente com o conteúdo.

---

## Sprint 3 — Dublador (modelo + página)

### [x] Task 3.1 — Criar CPT `dublador`
- **Arquivos**:
  - `geek-ao-cubo/includes/cpt-dublador.php` (novo)
  - `geek-ao-cubo/functions.php` (incluir no loader)
- **DoD**:
  - CPT aparece no admin e single resolve sem 404.

### [x] Task 3.2 — ACF: grupo de campos de `dublador` (Local JSON)
- **Arquivos**:
  - `geek-ao-cubo/acf-json/group_dublador.json` (novo)
- **Campos mínimos**:
  - `dublador_id_mal` (NUMERIC)
  - `dublador_nome`
  - `dublador_imagem_url`
  - `dublador_bio`
  - `dublador_links_externos` (repeater: label + url)
  - `dublador_idiomas` (repeater ou taxonomy)
- **DoD**:
  - Campos disponíveis e JSON versionado.

### [x] Task 3.3 — Template `single-dublador.php`
- **Arquivos**:
  - `geek-ao-cubo/single-dublador.php` (novo)
  - `geek-ao-cubo/single-dublador.css` (novo)
- **Conteúdo mínimo**:
  - Header “pessoa” (avatar + nome + links)
  - Seção “Animes em que participou” (grid com `card-anime`) — inicialmente via relacionamento manual ou sync
- **DoD**:
  - Página útil mesmo sem dados completos (fallback elegante).

### [x] Task 3.4 — Integração Jikan para dublador (people)
- **Arquivos**:
  - `geek-ao-cubo/includes/cpt-helpers.php`
- **Requisitos**:
  - Fetch do endpoint de `people`
  - Cache e fallback
  - Estratégia: **não depender 100% de live call** (preferir persistência ACF quando possível)
- **DoD**:
  - Dados retornam com contrato estável para o template.

---

## Sprint 4 — Ligação dublador ↔ anime (internal linking + UX)

### [x] Task 4.1 — Link interno no card de personagem/dublador
- **Arquivos**:
  - `geek-ao-cubo/molecules/card-personagem-dublador.php`
- **Requisitos**:
  - Se existir post `dublador` correspondente ao `dublador_id_mal`, linkar internamente
  - Caso contrário, fallback para a busca interna (novo comportamento definido)
- **DoD**:
  - Links sempre válidos, com `rel` adequado quando externo.

### [x] Task 4.2 — Estratégia de “matching” (id MAL → post WP)
- **Arquivos**:
  - `geek-ao-cubo/includes/cpt-helpers.php`
- **Requisitos**:
  - Helper `mm_get_local_dublador_by_mal_id($id)` (espelhar o de anime)
- **DoD**:
  - Matching consistente e performático.

### [x] Task 4.3 — Extras Implementados (UX & Monetização)
- **Filtros Responsivos SEO Friendly**: Adicionado componente de pílulas `filtro-pills` (A-Z, Recentes, Relevância) integrado dinamicamente à paginação.
- **Anúncios Nativo no Grid**: Injeção de anúncios camuflados na listagem de animes do dublador sem quebrar layout.

---

## Sprint 5 — Qualidade, SEO e documentação (fechamento)

### Task 5.1 — Atualizar Storybook e docs de novos componentes
- **Arquivos**:
  - `geek-ao-cubo/storybook.html`
  - `geek-ao-cubo/docs/*`
  - `geek-ao-cubo/docs/changelog.md`
- **DoD**:
  - Tudo documentado, previews consistentes.

### Task 5.2 — Smoke test de navegação (portal)
- **Checklist**:
  - Home → catálogo anime → single anime
  - Home → catálogo mangá → single mangá
  - Single anime → dublador → single dublador
  - Search (quando existir) encontra anime/mangá/review
- **DoD**:
  - Sem 404 inesperado, sem warnings/notice, e pages renderizam com fallback.

---

## Definition of Done (global)

- Sem alterações que quebrem BEM/Atomic Design.
- `design-tokens.css` permanece a fonte única da verdade visual.
- Documentação e changelog atualizados para qualquer componente/rota nova.
- Integrações resilientes (429/timeout) e cache funcionando.

