# hero-anime

**Tipo:** Organismo  
**Arquivo PHP:** `organisms/hero-anime.php`  
**Arquivo CSS:** `organisms/hero-anime.css`  
**Átomos utilizados:** `imagem-capa`, `badge-status`, `badge-genero`, `nota-mal`, `btn-primary`, `btn-secondary`  
**Fonte de dados:** 🔵 MyAnimeList / Jikan API (`/anime/{id}`)  
**Criado em:** 2026-05-25

---

## Descrição

Hero principal da página de detalhe do anime. Ocupa a largura total da página com backdrop desfocado e gradiente escuro. Exibe poster, título, score MAL, gêneros, meta informações e CTAs.

---

## Layout

```
Desktop (≥ 768px)
┌─────────────────────────────────────────────────────────────────────┐
│ [backdrop blur + gradiente escuro]                                  │
│                                                                     │
│   [poster 14rem]  │  [AIRING] [TV]                                  │
│                   │  Título do Anime em Destaque                    │
│                   │  título original em japonês                     │
│                   │  ★ 9.10  no MyAnimeList                         │
│                   │  [Ação] [Aventura] [Fantasia]                   │
│                   │  Tipo  Episódios  Estúdio  Ano  Temp.  Dur  Rating │
│                   │  Sinopse completa visível em desktop...         │
│                   │  [Assistir Agora]  [Adicionar à Lista]          │
└─────────────────────────────────────────────────────────────────────┘

Mobile (< 768px)
┌───────────────────────────────┐
│ [backdrop blur + gradiente]   │
│      [poster 8rem, centralizado] │
│ [AIRING] [TV]                 │
│ Título do Anime               │
│ título japonês                │
│ ★ 9.10  no MyAnimeList        │
│ [Ação] [Aventura] [Fantasia]  │
│ Tipo  Eps  Sinopse (4 linhas) │
│ [Assistir Agora]              │
│ [Adicionar à Lista]           │
└───────────────────────────────┘
```

---

## Parâmetros PHP

| Parâmetro         | Tipo            | Obrigatório | Default | Descrição                                               |
|-------------------|-----------------|-------------|---------|----------------------------------------------------------|
| `titulo`          | `string`        | ✅ Sim      | —       | Título do anime (H1 da página)                          |
| `titulo_japones`  | `string`        | ❌ Não      | `''`    | Título original em japonês                              |
| `imagem_backdrop` | `string`        | ❌ Não      | `''`    | URL da imagem de fundo desfocada (fallback: poster)     |
| `imagem_poster`   | `string`        | ❌ Não      | `''`    | URL da capa poster 2:3                                  |
| `nota`            | `string`        | ❌ Não      | `''`    | Nota MAL ex: "8.74"                                     |
| `status`          | `string`        | ❌ Não      | `''`    | `'airing'`, `'completed'` ou `'upcoming'`               |
| `tipo`            | `string`        | ❌ Não      | `''`    | `'TV'`, `'Movie'`, `'OVA'`, `'ONA'`, `'Special'`        |
| `episodios`       | `int`           | ❌ Não      | `0`     | Número de episódios (omitido se 0)                      |
| `duracao`         | `string`        | ❌ Não      | `''`    | Ex: `"24 min/ep"`                                       |
| `studio`          | `string\|array` | ❌ Não      | `''`    | Nome ou `['name' => '...', 'url' => '...']`             |
| `ano`             | `int`           | ❌ Não      | `0`     | Ano de início (omitido se 0)                            |
| `temporada`       | `string`        | ❌ Não      | `''`    | Ex: `"Primavera 2003"`                                  |
| `classificacao`   | `string`        | ❌ Não      | `''`    | Ex: `"PG-13"`, `"R+"`                                   |
| `generos`         | `array`         | ❌ Não      | `[]`    | Strings ou `['name' => '...', 'url' => '...']`          |
| `sinopse`         | `string`        | ❌ Não      | `''`    | Sinopse — aceita HTML seguro via `wp_kses_post()`       |
| `url_assistir`    | `string`        | ❌ Não      | `''`    | URL do botão "Assistir Agora"                           |
| `url_lista`       | `string`        | ❌ Não      | `''`    | URL do botão "Adicionar à Lista"                        |

---

## Variáveis CSS utilizadas

| Token                   | Uso                                     |
|-------------------------|-----------------------------------------|
| `--neutral-900`         | Background base e gradiente overlay     |
| `--neutral-100..400`    | Cores de texto em diferentes hierarquias|
| `--color-primary`       | Borda do meta info, link de estúdio     |
| `--font-heading`        | Título principal                        |
| `--font-body`           | Todos os textos secundários             |
| `--text-md-lg-size`     | Título (desktop)                        |
| `--text-md-sm-size`     | Título (mobile, mínimo do clamp)        |
| `--text-xs/xxs-size`    | Meta, sinopse, badges                   |
| `--space-*`             | Gaps, paddings internos                 |
| `--border-radius-200`   | Poster, meta info box                   |
| `--container-max`       | Largura máxima do inner (80rem)         |

---

## SEO & Schema

- `<section itemscope itemtype="https://schema.org/TVSeries">` — schema TV
- `itemprop="name"` no título H1
- `itemprop="alternateName"` no título japonês
- `itemprop="description"` na sinopse
- `aria-label="Detalhes do anime: {título}"` na section
- `alt="Capa oficial do anime {título}"` no poster

---

## Responsividade

| Breakpoint        | Comportamento                                                   |
|-------------------|-----------------------------------------------------------------|
| `< 48rem`         | flex-column; poster centralizado (8rem); sinopse 4 linhas clamp|
| `≥ 48rem (~768px)`| flex-row; poster 12rem alinhado ao topo; meta 3 colunas; sinopse completa |
| `≥ 64rem (~1024px)`| poster 14rem; título full size; meta 4 colunas               |

---

## Exemplo de uso

```php
mm_render_component( 'organisms', 'hero-anime', array(
    'titulo'          => 'Fullmetal Alchemist: Brotherhood',
    'titulo_japones'  => '鋼の錬金術師 FULLMETAL ALCHEMIST',
    'imagem_backdrop' => 'https://cdn.myanimelist.net/images/anime/1208/94745l.jpg',
    'imagem_poster'   => 'https://cdn.myanimelist.net/images/anime/1208/94745.jpg',
    'nota'            => '9.10',
    'status'          => 'completed',
    'tipo'            => 'TV',
    'episodios'       => 64,
    'duracao'         => '24 min/ep',
    'studio'          => array( 'name' => 'Bones', 'url' => home_url( '/studio/bones/' ) ),
    'ano'             => 2009,
    'temporada'       => 'Primavera 2009',
    'classificacao'   => 'PG-13',
    'generos'         => array( 'Ação', 'Aventura', 'Fantasia', 'Drama' ),
    'sinopse'         => 'Após perder seus corpos...',
    'url_assistir'    => 'https://www.crunchyroll.com/fullmetal-alchemist-brotherhood',
    'url_lista'       => home_url( '/lista/?add=fma-brotherhood' ),
) );
```
