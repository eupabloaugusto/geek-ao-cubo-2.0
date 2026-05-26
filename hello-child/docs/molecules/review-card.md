# review-card

**Tipo:** Molécula  
**Arquivo:** `molecules/review-card.php`  
**CSS:** `molecules/review-card.css`  
**JS:** `molecules/review-card.js`  
**Criado em:** 2026-05-25  
**Última atualização:** 2026-05-25  

## Descrição

Card de avaliação de usuário para a seção de reviews da página de detalhe do anime. Exibe o avatar do revisor, nome, data, nota MAL e o texto completo da review — truncado por padrão com expand/collapse in-page via JavaScript. Inclui link opcional para a review completa (exibição permanente quando `review_url` é fornecido).

## Átomos utilizados

- `atoms/avatar-personagem.php` — avatar circular do revisor com fallback de silhueta
- `atoms/nota-mal.php` — badge de nota com ícone de estrela amarelo

## Parâmetros PHP

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|---|---|---|---|---|
| `reviewer_name` | string | ✅ | — | Nome do revisor |
| `reviewer_avatar` | string | — | `''` | URL do avatar do revisor |
| `reviewer_url` | string | — | `''` | URL do perfil do revisor (`rel="nofollow"`) |
| `review_date` | string | — | `''` | Data da review (ex: "25 mai. 2026") |
| `review_score` | string | — | `''` | Nota ex: "8.5" — alimenta `nota-mal` |
| `review_text` | string | ✅ | — | Texto da review (HTML permitido: `p`, `br`, `strong`, `em`) |
| `review_url` | string | — | `''` | Link para review completa (`nofollow noopener`, `target="_blank"`) |
| `max_chars` | int | — | `300` | Limite de caracteres antes do corte |

## Variáveis CSS utilizadas

- `--neutral-800`, `--neutral-700`, `--neutral-600` — fundo e bordas do card
- `--neutral-400`, `--neutral-300`, `--neutral-100` — textos e data
- `--color-primary`, `--brand-400` — botão toggle e hover
- `--font-heading`, `--font-body` — tipografia
- `--text-xs-size`, `--text-xxs-size` — escala tipográfica
- `--space-100` a `--space-400` — espaçamentos
- `--border-radius-100`, `--border-radius-200` — raios de borda
- `--icon-xs` — tamanho dos ícones SVG

## SEO aplicado

- Schema.org `Review` no `<article>` com `author` (Person), `reviewRating` (Rating com `ratingValue` e `bestRating`), `reviewBody` e `datePublished`
- `rel="nofollow"` no link do perfil do revisor
- `rel="nofollow noopener"` + `target="_blank"` no link externo da review
- `aria-expanded` no botão toggle — atualizado dinamicamente pelo JS
- `aria-controls` apontando para o `id` do corpo da review

## Comportamento JS (review-card.js)

- Procura todos os `.review-card[data-expandable="true"] .review-card__toggle`
- Click no botão faz toggle da classe `.review-card--expanded` no `<article>` pai
- **Colapsado:** `max-height: 6.5rem` + gradient fade na base do texto
- **Expandido:** `max-height` removido via classe, seta rotaciona 180°, label muda para "Ler menos"
- `aria-expanded` é atualizado a cada toggle
- CSS controla toda a animação — JS apenas alterna classes e atributos ARIA

## Responsividade

- **Mobile (375px):** padding `--space-300`, gap `--space-200`, avatar `2rem`
- **Desktop (1280px):** padding `--space-400`, avatar `40px` (via parâmetro PHP)

## Exemplo de uso

```php
mm_render_component( 'molecules', 'review-card', array(
    'reviewer_name'   => 'Tanaka_kun',
    'reviewer_avatar' => 'https://cdn.myanimelist.net/images/userimages/123456.jpg',
    'reviewer_url'    => 'https://myanimelist.net/profile/Tanaka_kun',
    'review_date'     => '25 mai. 2026',
    'review_score'    => '9.0',
    'review_text'     => '<p>Uma obra-prima do gênero. A direção de arte combina perfeitamente com a trilha sonora épica, criando uma experiência audiovisual raramente vista nos últimos anos. A evolução dos personagens é consistente e emocionante do início ao fim.</p>',
    'review_url'      => 'https://myanimelist.net/reviews.php?id=123456',
    'max_chars'       => 300,
) );
```
