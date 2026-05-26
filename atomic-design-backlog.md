# 🧬 Atomic Design Backlog — Geek ao Cubo

> Mapeamento completo do sistema de design. Inventário do que **existe** e lista priorizada do que **falta**, baseado nas referências da **Crunchyroll News** (página de artigo) e **MyAnimeList/Jikan** (página de detalhe de anime).

---

## ✅ Inventário — O que já EXISTE

### ⚛️ Átomos (35 componentes)

| Componente | Descrição |
|---|---|
| `anuncio-adsense` | Bloco de anúncio Google AdSense |
| `avatar-personagem` | Avatar circular de personagem com fallback elegante |
| `aviso-adblock` | Aviso para usuários com AdBlock |
| `badge-categoria` | Tag editorial colorida ("NOTÍCIAS", "ANÁLISE", "GUIA"). Diferente de `badge-genero` — é editorial, não de gênero |
| `badge-genero` | Badge de gênero do anime (ação, romance, etc.) |
| `badge-horario` | Badge de horário de exibição |
| `badge-rank` | Badge de ranking de anime (#1, Top 10) para destaques |
| `badge-status` | Badge de status (airing, upcoming, finished) |
| `banner-anuncio-editorial` | Banner de promoção editorial interno (ex: "Novos animes Primavera 2026") |
| `breadcrumb-item` | Item individual da trilha de navegação com separador |
| `btn-busca-trigger` | Botão que abre o modal de busca |
| `btn-hamburger` | Botão hamburguer do menu mobile |
| `btn-nav-arrow` | Seta de navegação da esteira |
| `btn-primary` | Botão primário (CTA principal) |
| `btn-secondary` | Botão secundário (ação alternativa) |
| `carousel-dot` | Ponto de navegação de carrossel |
| `drawer-link` | Link do menu drawer |
| `drawer-overlay` | Overlay de fundo do drawer |
| `drawer-sub-link` | Sub-link do menu drawer |
| `embed-video` | Wrapper responsivo para embed de YouTube/trailer com placeholder |
| `icone-externo-link` | Ícone + label para links externos de referência |
| `imagem-capa` | Imagem de capa do anime com lazy load |
| `input-busca` | Campo de busca completo |
| `input-busca-compact` | Campo de busca compacto |
| `input-helper` | Texto auxiliar de campo de formulário |
| `input-label` | Label de campo de formulário |
| `meta-autor` | Avatar circular do autor + nome linkado |
| `meta-data` | Data de publicação formatada com ícone |
| `nav-link` | Link de navegação principal |
| `nota-mal` | Nota do MyAnimeList com estrela |
| `progress-bar` | Barra de progresso visual de status de exibição/votação |
| `rating-bar` | Barra horizontal de distribuição de votos e notas (1-10) |
| `rating-score` | Nota grande em destaque (ex: "8.74") com label contextual |
| `stat-numero` | Number de destaque com label (ex: "1.2M membros", "Rank #3") |
| `tag-artigo` | Tag clicável no rodapé do artigo (plana, sem cor de status) |
| `logo` | Logotipo da marca em 5 variantes: `horizontal-01`, `horizontal-02`, `wordmark`, `icone-quadrado`, `icone-simples`. SVG inline, responsivo, com link opcional |

### 🧬 Moléculas (19 componentes)

| Componente | Descrição |
|---|---|
| `autor-profile-box` | Box de bio do autor: avatar grande + nome + descrição curta |
| `breadcrumb` | Trilha de navegação completa (combina `breadcrumb-item` com separadores) |
| `card-anime` | Card de anime para esteira (thumbnail + badges + nota) |
| `card-noticia` | Card de notícia/artigo (thumbnail + categoria + título + meta) |
| `card-noticia-relacionada` | Card horizontal especial no corpo do artigo: miniatura + categoria + título + data |
| `card-personagem-dublador` | Card horizontal duplo: personagem (esq.) + voice actor (dir.) com avatares, nome e role |
| `card-personagem` | Card de personagem: avatar + nome + papel (Principal/Secundário) |
| `carousel-nav` | Navegação com dots do carrossel |
| `form-busca` | Formulário de busca completo |
| `form-field` | Campo de formulário completo (label + input + helper) |
| `meta-artigo-header` | Bloco de metadados do topo do artigo: categoria + autor + data |
| `sidebar-assistir-agora` | Card promocional lateral (CTA) para direcionar usuários a assistir em canais oficiais |
| `stat-bloco` | Bloco de estatísticas: nota grande + número de membros + rank |
| `card-recomendacao` | Card vertical de anime recomendado: thumbnail poster 2:3 + título + contador de recomendações (estilo card-anime) |
| `relacionado-item` | Card horizontal compacto de anime relacionado: thumbnail 4rem 2:3 + tipo de relação (laranja) + título |
| `card-staff` | Card horizontal compacto de membro da equipe: avatar + nome + cargo (Diretor, Compositor, etc.) |
| `tags-artigo` | Linha de tags clicáveis no rodapé do conteúdo |
| `review-card` | Card de avaliação de usuário: avatar + nome + data + nota MAL + texto com expand/collapse + link opcional para review completa |
| `trilho-infinito` | Wrapper reutilizável de scroll horizontal infinito: 2 átomos `btn-nav-arrow` + trilho scroll-snap + JS de loop infinito (clonagem) + drag-to-scroll |

### 🧫 Organismos (24 componentes)

| Componente | Descrição |
|---|---|
| `secao-noticias-recentes` | Grade de `card-noticia` em layout editorial: destaque hero + grid 3 colunas, botão "Ver Mais" |
| `secao-reviews` | Lista de `review-card` com cabeçalho (título + pill de contagem), limite configurável e botão "Ver mais reviews" opcional |
| `secao-staff` | Grade de `card-staff` agrupada por cargo (role_group) com subtítulo H3 + barra laranja, limite por grupo e botão opcional |
| `secao-recomendacoes` | Trilho horizontal scroll snap de `card-recomendacao` com cabeçalho H2 + link "Ver todas" opcional |
| `secao-estatisticas` | Trilho horizontal scroll snap de `stat-bloco` (score + rank + popularidade + membros), mesmo padrão que `secao-esteira-animes` |
| `secao-novos-episodios` | Carrossel horizontal de `card-anime` com `badge-horario` visível. Título dinâmico gerado pelo PHP (ex: "Novos Episódios — Domingo"). Usa `trilho-infinito` |
| `sidebar-anime-info` | Sidebar da página de anime: imagem + metadados (tipo, episódios, status, aired, studios, source, gêneros, duração, rating) |
| `secao-relacionados` | Lista de `relacionado-item` agrupados dinamicamente por tipo de relação em grades responsivas |
| `secao-personagens` | Grade de `card-personagem` (pôster 2:3): scroll horizontal mobile, grid auto-fill tablet/desktop |
| `secao-dubladores` | Grade de `card-personagem-dublador`: grid 4 cols no desktop, scroll horizontal no mobile/tablet |
| `hero-anime` | Hero da página de detalhe: backdrop desfocado + poster + título H1 + score MAL + gêneros + meta grid + sinopse + CTAs. Schema.org TVSeries |
| `barra-filtros` | Barra de filtros por categoria/gênero |
| `footer` | Rodapé principal do site com links semânticos descritivos |
| `form-bloqueado` | Formulário bloqueado (login required) |
| `header` | Cabeçalho principal do site |
| `navigation-drawer` | Menu lateral mobile |
| `search-modal` | Modal de busca global |
| `secao-artigo-unico` | Template completo da página de artigo: breadcrumbs + meta-header + corpo + tags + autor-bio |
| `secao-carrossel-destaque` | Carrossel principal da home |
| `secao-destaque` | Seção de destaque editorial |
| `secao-esteira-animes` | Esteira horizontal de cards de anime |
| `secao-leia-tambem` | Grade/lista de `card-noticia` com título "Leia também" (detalhe pós-artigo) |
| `secao-pos-artigo` | Seção responsiva pós-artigo: "Leia também" + "Assistir Agora" (sidebar em desktop, inline em mobile) |
| `sidebar` | Sidebar genérica |

---

## ❌ O que FALTA — Backlog Priorizado

> **Fontes:** 🟠 = Crunchyroll News (artigo) | 🔵 = MyAnimeList/Jikan (detalhe de anime) | 🟣 = Ambos

---

### ⚛️ Átomos — Faltam 0 componentes

Todos os 36 componentes atômicos estão implementados no projeto físico.


---

### 🧬 Moléculas — Faltam 0 componentes

Todas as 19 moléculas estão implementadas no projeto físico.

---

### 🧫 Organismos — Faltam 0 componentes

Todos os 24 organismos estão implementados no projeto físico.

---

## 📊 Resumo Geral

| Nível | Existem | Faltam | Total previsto |
|---|---|---|---|
| ⚛️ Átomos | 36 | 0 | 36 |
| 🧬 Moléculas | 19 | 0 | 19 |
| 🧫 Organismos | 24 | 0 | 24 |
| **Total** | **79** | **0** | **79** |

---

## 🗺️ Mapa de Dependências

> Para construir um componente, seus filhos precisam existir primeiro.

```
secao-artigo-unico              [EXISTE ✅]
  ├── meta-artigo-header        [EXISTE ✅]
  │     ├── badge-categoria      [EXISTE ✅]
  │     ├── meta-autor           [EXISTE ✅]
  │     └── meta-data            [EXISTE ✅]
  ├── breadcrumb                [EXISTE ✅]
  │     └── breadcrumb-item      [EXISTE ✅]
  ├── embed-video                [EXISTE ✅]
  ├── card-noticia-relacionada   [EXISTE ✅]
  ├── tags-artigo               [EXISTE ✅]
  │     └── tag-artigo           [EXISTE ✅]
  └── autor-profile-box          [EXISTE ✅]

secao-pos-artigo                [EXISTE ✅]
  ├── secao-leia-tambem         [EXISTE ✅]
  │     └── card-noticia         [EXISTE ✅]
  └── sidebar-assistir-agora    [EXISTE ✅]
        ├── imagem-capa          [EXISTE ✅]
        └── btn-primary          [EXISTE ✅]

hero-anime
  ├── imagem-capa                [EXISTE ✅]
  ├── badge-status               [EXISTE ✅]
  ├── badge-genero               [EXISTE ✅]
  ├── rating-score               [EXISTE ✅]
  └── btn-primary                [EXISTE ✅]

sidebar-anime-info
  ├── imagem-capa                [EXISTE ✅]
  ├── stat-bloco
  │     ├── rating-score         [EXISTE ✅]
  │     └── stat-numero          [EXISTE ✅]
  └── badge-genero               [EXISTE ✅]

secao-dubladores                [EXISTE ✅]
  └── card-personagem-dublador  [EXISTE ✅]
        └── avatar-personagem    [EXISTE ✅]

secao-personagens               [EXISTE ✅]
  └── card-personagem           [EXISTE ✅]

secao-relacionados              [EXISTE ✅]
  └── relacionado-item          [EXISTE ✅]
        └── imagem-capa          [EXISTE ✅]

sidebar-anime-info              [EXISTE ✅]
  ├── imagem-capa                [EXISTE ✅]
  └── stat-bloco                [EXISTE ✅]
        ├── rating-score         [EXISTE ✅]
        └── stat-numero          [EXISTE ✅]

secao-noticias-recentes         [EXISTE ✅]
  └── card-noticia               [EXISTE ✅]

secao-estatisticas
  ├── rating-bar                 [EXISTE ✅]
  └── stat-numero                [EXISTE ✅]
```

---

## 🚀 Ordem de Construção Recomendada

### Sprint 1 — Fundação para Artigos
1. `badge-categoria` (Átomo)
2. `meta-autor` + `meta-data` (Átomos)
3. `breadcrumb-item` → `breadcrumb` (Átomo → Molécula)
4. `meta-artigo-header` (Molécula)
5. `embed-video` (Átomo)
6. `tag-artigo` → `tags-artigo` (Átomo → Molécula)
7. `card-noticia-relacionada` (Molécula)
8. `autor-profile-box` (Molécula)
9. `secao-artigo-unico` + `secao-pos-artigo` (Organismos)

### Sprint 2 — Página de Detalhe do Anime
1. `rating-score` + `stat-numero` + `badge-rank` (Átomos)
2. `stat-bloco` (Molécula)
3. `hero-anime` (Organismo)
4. `sidebar-anime-info` (Organismo)
5. `avatar-personagem` → `card-personagem-dublador` → `secao-personagens` (Átomo → Molécula → Organismo)
6. `relacionado-item` → `secao-relacionados` (Átomo → Organismo)
7. `rating-bar` → `secao-estatisticas` (Átomo → Organismo)

### Sprint 3 — Conteúdo Editorial Extra
1. `card-staff` → `secao-staff`
2. `review-card` → `secao-reviews`
3. `card-recomendacao` → `secao-recomendacoes`
4. `secao-noticias-recentes`
