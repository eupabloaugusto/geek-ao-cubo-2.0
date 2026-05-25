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

### 🧬 Moléculas (16 componentes)

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
| `card-recomendacao` | Card horizontal compacto de anime recomendado: thumbnail poster + título + contador de recomendações |
| `card-staff` | Card horizontal compacto de membro da equipe: avatar + nome + cargo (Diretor, Compositor, etc.) |
| `tags-artigo` | Linha de tags clicáveis no rodapé do conteúdo |

### 🧫 Organismos (13 componentes)

| Componente | Descrição |
|---|---|
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

### 🧬 Moléculas — Faltam 2 componentes

#### 🔴 Prioridade ALTA

> Nenhuma molécula de alta prioridade restante.

#### 🟡 Prioridade MÉDIA

| Componente | Descrição | Fonte |
|---|---|---|
| `relacionado-item` | Item de anime relacionado: thumbnail pequena + título + tipo de relação ("Sequência", "Prequel") | 🔵 |

#### 🟢 Prioridade BAIXA

| Componente | Descrição | Fonte |
|---|---|---|
| `review-card` | Card de review: autor + data + nota + texto + botão "ler mais" | 🔵 |

---

### 🧫 Organismos — Faltam 8 componentes

#### 🔴 Prioridade ALTA

| Componente | Descrição | Fonte |
|---|---|---|
| `hero-anime` | Hero da página de detalhe do anime: banner + título + score + badges de status + CTA "Adicionar à Lista" | 🔵 |

#### 🟡 Prioridade MÉDIA

| Componente | Descrição | Fonte |
|---|---|---|
| `secao-personagens` | Grid de `card-personagem-dublador` com título "Personagens e Dubladores" | 🔵 |
| `secao-relacionados` | Lista de `relacionado-item` agrupados por tipo de relação | 🔵 |
| `sidebar-anime-info` | Sidebar da página de anime: imagem + metadados (tipo, episódios, status, aired, studios, source, gêneros, duração, rating) | 🔵 |
| `secao-noticias-recentes` | Grade de `card-noticia` para listagem editorial (home ou category page) | 🟠 |

#### 🟢 Prioridade BAIXA

| Componente | Descrição | Fonte |
|---|---|---|
| `secao-staff` | Lista de `card-staff` com título "Equipe" | 🔵 |
| `secao-reviews` | Listagem de `review-card` com paginação | 🔵 |
| `secao-recomendacoes` | Grid de `card-recomendacao` com título "Recomendações" | 🔵 |
| `secao-estatisticas` | Bloco com `rating-bar` + distribuição de notas + membros por status | 🔵 |

---

## 📊 Resumo Geral

| Nível | Existem | Faltam | Total previsto |
|---|---|---|---|
| ⚛️ Átomos | 36 | 0 | 36 |
| 🧬 Moléculas | 16 | 2 | 18 |
| 🧫 Organismos | 13 | 9 | 22 |
| **Total** | **65** | **11** | **76** |

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

secao-personagens
  └── card-personagem-dublador
        └── avatar-personagem    [EXISTE ✅]

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
