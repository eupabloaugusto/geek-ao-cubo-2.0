# Header Responsivo (Header)

**Tipo:** Organismo  
**Arquivo:** `organisms/header.php`  
**CSS:** `organisms/header.css`  
**JS:** `organisms/header.js`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Header Responsivo é o topo fixo (sticky) do portal **modomaratona.com**. Possui design glassmorphism com fundo semitransparente e desfoque de fundo (`backdrop-filter: blur(12px)`), e encolhe suavemente no scroll. Integra a logo SVG inline, menu de navegação centralizado (desktop) e barra de busca à direita (tablet/desktop).

## Componentes Utilizados
- **Átomos:**
  - `atoms/btn-hamburger.php` (gatilho do menu mobile/tablet)
  - `atoms/nav-link.php` (links de navegação horizontal no desktop)
  - `atoms/input-busca-compact.php` (barra de busca compacta que abre o modal)

## Layout por Breakpoint

| Breakpoint | Hamburger | Logo | Nav | Busca |
|---|---|---|---|---|
| Mobile (< 768px) | ✅ Esquerda | Centralizada (absoluta) | ❌ Oculta | ❌ Oculta |
| Tablet (768–1023px) | ✅ Esquerda | Esquerda | ❌ Oculta | ✅ Compacta (direita) |
| Desktop (≥ 1024px) | ❌ Oculto | Esquerda | ✅ Centralizado | ✅ Expandida (direita) |

## Comportamento Shrink on Scroll
Quando o usuário rola mais de **20px**, o JS adiciona a classe `.header--scrolled`:
- Altura reduz de `80px` → `64px`
- Fundo torna-se mais opaco (`rgba(13, 14, 17, 0.96)`)
- Sombra suave aparece abaixo do header
- SVG do logo encolhe de `28px` → `22px` de altura

## Parâmetros PHP

| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['search_enabled']` | bool | `true` | Exibe ou esconde a barra de busca no header |
| `$args['logo_text']` | string | `get_bloginfo('name')` | Texto fallback caso o SVG não exista |
| `$args['menu_items']` | array | `[]` | Lista de links para o menu desktop |

## Estrutura de `menu_items`
```php
'menu_items' => [
    ['label' => 'Início',       'url' => '/',          'is_active' => true],
    ['label' => 'Animes',       'url' => '/animes'],
    ['label' => 'Temporada',    'url' => '/temporada'],
    ['label' => 'Calendário',   'url' => '/calendario'],
]
```

## SEO e Acessibilidade (A11y)
- Tag semântica `<header role="banner">` única por página.
- Logo com `aria-label` descritivo para leitores de tela.
- Nav com `role="navigation"` e `aria-label="Navegação Principal"`.
- Hamburger com `aria-expanded` atualizado dinamicamente pelo JS.

## Espaçador de Página
Para evitar que o conteúdo fique oculto abaixo do header fixo, use a div espaçadora logo após o componente:
```html
<div class="header-spacer"></div>
```

## Exemplo de uso
```php
<?php 
mm_render_component('organisms', 'header', [
    'search_enabled' => true,
    'menu_items'     => [
        ['label' => 'Início',     'url' => '/',          'is_active' => true],
        ['label' => 'Animes',     'url' => '/animes'],
        ['label' => 'Temporada',  'url' => '/temporada'],
        ['label' => 'Calendário', 'url' => '/calendario'],
        ['label' => 'Notícias',   'url' => '/noticias'],
    ]
]);
?>
<div class="header-spacer"></div>
```
