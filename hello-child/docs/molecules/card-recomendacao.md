# card-recomendacao

**Tipo:** Molécula  
**Arquivo PHP:** `molecules/card-recomendacao.php`  
**Arquivo CSS:** `molecules/card-recomendacao.css`  
**Átomo utilizado:** `imagem-capa`  
**Fonte de dados:** 🔵 MyAnimeList / Jikan API (`/anime/{id}/recommendations`)  
**Criado em:** 2026-05-25

---

## Descrição

Card horizontal compacto para exibir um anime recomendado. Exibe thumbnail (proporção poster 2:3), título truncado em até 2 linhas e contador de recomendações com ícone de pessoas.

O card inteiro é clicável quando `anime_url` é fornecido (`<a>`). Sem URL, renderiza como `<div>`.

---

## Layout

```
┌─────────────────────────────────────────────┐
│ [thumb] Título do Anime Recomendado         │
│         👥 23 recomendações                 │
└─────────────────────────────────────────────┘
```

---

## Parâmetros PHP

| Parâmetro     | Tipo     | Obrigatório | Default | Descrição                                        |
|---------------|----------|-------------|---------|--------------------------------------------------|
| `anime_title` | `string` | ✅ Sim      | —       | Título do anime recomendado                      |
| `anime_image` | `string` | ❌ Não      | `''`    | URL da capa (fallback placeholder se ausente)    |
| `anime_url`   | `string` | ❌ Não      | `''`    | URL da página do anime (torna o card clicável)   |
| `rec_count`   | `int`    | ❌ Não      | `0`     | Número de recomendações (omitido se 0)           |

---

## Variáveis CSS utilizadas

| Token                 | Uso                                      |
|-----------------------|------------------------------------------|
| `--neutral-800`       | Background do card                       |
| `--neutral-700`       | Borda padrão                             |
| `--neutral-100`       | Cor do título                            |
| `--neutral-400`       | Cor do contador                          |
| `--neutral-500`       | Cor do ícone do contador                 |
| `--color-primary`     | Borda e título no hover                  |
| `--font-heading`      | Família do título                        |
| `--font-body`         | Família do contador                      |
| `--text-xs-size`      | Tamanho do título                        |
| `--text-xxs-size`     | Tamanho do contador                      |
| `--space-100/300/400` | Gaps e padding                           |
| `--border-radius-200` | Arredondamento do card                   |

---

## SEO & Acessibilidade

- `aria-label="Ver recomendação: {título}"` no `<a>` quando clicável
- `alt="Capa de {título}"` na imagem de capa
- Fallback placeholder do átomo `imagem-capa` quando imagem ausente
- Ícone SVG com `aria-hidden="true"` e `focusable="false"`

---

## Responsividade

| Breakpoint          | Comportamento                                      |
|---------------------|----------------------------------------------------|
| `> 30rem`           | Thumbnail 3.5rem, padding normal                   |
| `≤ 30rem (~480px)`  | Thumbnail 3rem, padding reduzido                   |

---

## Exemplos de uso

```php
// Card completo com todos os dados:
mm_render_component( 'molecules', 'card-recomendacao', array(
    'anime_title' => 'Fullmetal Alchemist: Brotherhood',
    'anime_image' => 'https://cdn.myanimelist.net/images/anime/1208/94745.jpg',
    'anime_url'   => home_url( '/anime/fullmetal-alchemist-brotherhood/' ),
    'rec_count'   => 142,
) );

// Card sem link e sem contador:
mm_render_component( 'molecules', 'card-recomendacao', array(
    'anime_title' => 'Attack on Titan',
    'anime_image' => 'https://cdn.myanimelist.net/images/anime/10/47347.jpg',
) );
```
