# trilho-infinito

**Tipo:** Molécula  
**Arquivo:** `molecules/trilho-infinito.php`  
**CSS:** `molecules/trilho-infinito.css`  
**JS:** `molecules/trilho-infinito.js`  
**Criado em:** 2026-05-25  

## Descrição

Wrapper reutilizável de scroll horizontal infinito. Combina dois átomos `btn-nav-arrow` (prev/next) com um trilho rolável que executa infinite loop via clonagem de slides. Compartilhado por todos os organismos com scroll horizontal sempre ativo (`secao-esteira-animes`, `secao-recomendacoes`, `secao-estatisticas`).

## Átomos utilizados

- `atoms/btn-nav-arrow.php` — botões de seta prev e next (glassmorphism, touch target 44px)

## Parâmetros PHP

| Parâmetro | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `track_html` | string | ✅ | HTML pré-renderizado dos slides. Cada slide deve ter `.js-trilho__slide`. |
| `class` | string | — | Classe(s) extra(s) para o wrapper `.trilho-infinito` |
| `track_class` | string | — | Classe(s) extra(s) para o div `.trilho-infinito__track` |

## Classes JS (esperadas no markup)

| Classe | Elemento |
|---|---|
| `.js-trilho` | container/wrapper — inicializa a instância |
| `.js-trilho__track` | div rolável horizontal |
| `.js-trilho__slide` | cada slide individual |
| `.js-trilho__prev` | botão seta anterior |
| `.js-trilho__next` | botão seta próxima |
| `.js-trilho__clone` | slides clonados (adicionados pelo JS, aria-hidden) |

## Funcionalidades do JS

1. **Scroll infinito** — clona todos os slides antes e depois do set real; teleporte invisível ao cruzar os limites
2. **Setas** — `scrollBy` 75% da largura visível do trilho
3. **Drag-to-scroll** — mousedown/move/up com threshold de 8px; bloqueia cliques acidentais em links

## Como usar nos organismos

```php
// 1. Captura o HTML dos slides via output buffering
ob_start();
foreach ( $items as $item ) :
    echo '<div class="secao-x__slide js-trilho__slide">';
    mm_render_component( 'molecules', 'card-x', (array) $item );
    echo '</div>';
endforeach;
$track_html = ob_get_clean();

// 2. Passa para a molécula
mm_render_component( 'molecules', 'trilho-infinito', array(
    'track_html'  => $track_html,
    'class'       => 'secao-x__wrapper',
    'track_class' => 'secao-x__track',
) );
```

O organismo deve ter `position: relative` no inner container e gap/padding definidos via CSS no `secao-x__track`.

## Responsividade

- **< 30rem:** setas ocultas via CSS (viewport muito estreito)
- **≥ 30rem:** setas visíveis e ativas
- Scroll snap nativo em todos os breakpoints

## Variáveis CSS utilizadas

Nenhuma — usa classes do átomo `btn-nav-arrow` que referencia os design tokens.
