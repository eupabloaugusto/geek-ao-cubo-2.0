# secao-reviews

**Tipo:** Organismo  
**Arquivo:** `organisms/secao-reviews.php`  
**CSS:** `organisms/secao-reviews.css`  
**Criado em:** 2026-05-25  
**Última atualização:** 2026-05-25  

## Descrição

Seção de avaliações de usuários para a página de detalhe do anime. Renderiza uma lista de cards `review-card` com cabeçalho (título H2 + contador de reviews) e botão opcional "Ver mais reviews" linkando para a fonte externa (ex: MyAnimeList). Sem JavaScript próprio — o expand/collapse de cada card vem automaticamente do `review-card.js` enfileirado via `mm_render_component`.

Em desktop largo (≥ 75rem), a lista passa para grade de 2 colunas para melhor aproveitamento do espaço.

## Moléculas utilizadas

- `molecules/review-card.php` — cada item da lista de avaliações

## Parâmetros PHP

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|---|---|---|---|---|
| `titulo` | string | — | `'Reviews'` | Título da seção (H2) |
| `reviews` | array | ✅ | — | Array de arrays com os parâmetros de cada `review-card` |
| `total_count` | int | — | `0` | Total de reviews para exibir no pill do cabeçalho |
| `max_reviews` | int | — | `6` | Número máximo de cards renderizados |
| `ver_mais_url` | string | — | `''` | URL do botão "Ver mais reviews" (omitido se vazio) |
| `ver_mais_label` | string | — | `'Ver todas as reviews'` | Label customizável do botão |

## Variáveis CSS utilizadas

- `--neutral-100`, `--neutral-400`, `--neutral-700`, `--neutral-800` — títulos, pills e bordas
- `--font-heading`, `--font-body` — tipografia
- `--text-sm-size`, `--text-md-sm-size`, `--text-xxs-size` — escala tipográfica
- `--space-100` a `--space-700` — espaçamentos e padding
- `--border-radius-100` — pill do contador
- `--container-max` — largura máxima do inner
- `--icon-xs` — ícone do botão

## SEO aplicado

- Schema.org `ItemList` na `<section>` + `ListItem` em cada item da lista
- `meta name` com o título da seção via `itemprop="name"`
- `aria-label` na seção com o título
- `aria-label` no contador com texto descritivo completo ("X avaliações no total")
- `rel="nofollow noopener"` + `target="_blank"` no botão externo

## Responsividade

- **Mobile (375px):** lista em coluna única, padding `--space-400`
- **Desktop (≥ 64rem):** padding-inline `--space-600`, título maior (`--text-md-sm-size`)
- **Desktop largo (≥ 75rem):** lista passa para grade de 2 colunas

## Exemplo de uso

```php
mm_render_component( 'organisms', 'secao-reviews', array(
    'titulo'       => 'Reviews',
    'total_count'  => 2341,
    'max_reviews'  => 6,
    'ver_mais_url' => 'https://myanimelist.net/anime/5114/reviews',
    'reviews'      => array(
        array(
            'reviewer_name'   => 'Tanaka_kun',
            'reviewer_avatar' => 'https://cdn.myanimelist.net/images/userimages/123456.jpg',
            'reviewer_url'    => 'https://myanimelist.net/profile/Tanaka_kun',
            'review_date'     => '25 mai. 2026',
            'review_score'    => '9.0',
            'review_text'     => '<p>Uma obra-prima do gênero...</p>',
            'review_url'      => 'https://myanimelist.net/reviews.php?id=123456',
        ),
        array(
            'reviewer_name' => 'AnonReviewer',
            'review_text'   => '<p>Texto da review...</p>',
            'review_score'  => '7.5',
        ),
    ),
) );
```
