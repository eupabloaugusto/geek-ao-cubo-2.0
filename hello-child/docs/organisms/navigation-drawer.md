# Menu Lateral Off-Canvas (Navigation Drawer)

**Tipo:** Organismo  
**Arquivo:** `organisms/navigation-drawer.php`  
**CSS:** `organisms/navigation-drawer.css`  
**JS:** `organisms/navigation-drawer.js`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Menu Lateral Off-Canvas (`navigation-drawer`) é o organismo responsável pela navegação móvel e painel de ações offcanvas do site. Ele se comporta como uma gaveta que desliza da lateral esquerda e fornece uma interface de altíssima fidelidade com foco em acessibilidade e facilidade de clique no celular (touch-friendly targets).

Integra de forma coesa a barra de busca do site, links de navegação simples, acordeões com sublinks expansíveis e links de redes sociais no rodapé.

## Componentes Utilizados
- **Átomos:**
  - `atoms/drawer-overlay.php` (fundo com blur)
  - `atoms/btn-hamburger.php` (botão de fechar/alternar)
  - `atoms/drawer-link.php` (links primários / dropdown)
  - `atoms/drawer-sub-link.php` (links secundários/dot)
- **Moléculas:**
  - `molecules/form-busca.php` (busca integrada)

## Variáveis CSS Utilizadas
- `--neutral-900` (cor de fundo do painel)
- `--neutral-800` (borda e detalhes de divisórias)
- `--color-primary` (destaque ativo dos links e ícone de dropdown)
- `--space-500` / `--space-600` (paddings do painel e corpo)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['is_open']` | bool | `false` | Se o drawer deve iniciar aberto |
| `$args['logo_text']` | string | `get_bloginfo('name')` | Texto da logo exibido no cabeçalho do drawer |
| `$args['search_enabled']` | bool | `true` | Exibe ou esconde a barra de busca no topo |
| `$args['menu_items']` | array | `[]` | Lista aninhada de itens do menu (conforme exemplo de uso) |

## SEO e Acessibilidade (A11y)
- Estruturação semântica com a tag HTML5 `<aside>` com a marcação de acessibilidade `role="navigation"` e atributo `aria-label="Menu de Navegação"`.
- Atributos `aria-hidden="true/false"` e `aria-expanded="true/false"` gerenciados dinamicamente via JavaScript conforme estado do painel e dos acordeões.
- **Delegação de Eventos:** Escuta global no `document` para suportar cabeçalhos ou botões hamburger renderizados tardiamente/dinamicamente pelo Elementor.
- **Carregamento Global:** Renderizado de forma unificada no rodapé de todas as páginas através do gancho `wp_footer` no arquivo `functions.php`.
- Fechamento intuitivo do painel ao pressionar a tecla `Escape` ou clicar fora do menu lateral (backdrop).
- Trava do scroll do `body` (`overflow: hidden`) enquanto o drawer está ativo, garantindo foco visual exclusivo do usuário e fluidez na rolagem do painel.

## Responsividade
- O drawer é projetado de forma fluida usando `width: clamp(280px, 85vw, 340px)`. Isso assegura o encaixe perfeito desde telas de celulares pequenos (320px) até tablets ou desktops.
- Transições aceleradas por hardware via propriedades CSS (`transform: translateX()`) para garantir performance de renderização de 60fps em aparelhos móveis.

## Exemplo de uso
```php
<?php 
// Renderiza o drawer lateral móvel estruturado
mm_render_component('organisms', 'navigation-drawer', [
    'is_open'        => false,
    'logo_text'      => 'MODO MARATONA',
    'search_enabled' => true,
    'menu_items'     => [
        [
            'label'     => 'Início',
            'url'       => '/',
            'is_active' => true
        ],
        [
            'label'        => 'Animes',
            'has_dropdown' => true,
            'is_open'      => false,
            'sublinks'     => [
                ['label' => 'Temporada Atual', 'url' => '/temporada'],
                ['label' => 'Calendário', 'url' => '/calendario']
            ]
        ],
        [
            'label' => 'Notícias Geek',
            'url'   => '/noticias'
        ]
    ]
]); 
?>
```
