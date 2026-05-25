# Logo

**Tipo:** Átomo  
**Arquivo:** `atoms/logo.php`  
**CSS:** `atoms/logo.css`  
**Criado em:** 2026-05-25  
**Última atualização:** 2026-05-25  

## Descrição
Exibe o logotipo do site em uma das 5 variantes oficiais, injetando o SVG inline para máximo controle via CSS e performance (sem requisição HTTP extra). Usado no header, footer e qualquer contexto que precise de identidade visual da marca.

## Variantes disponíveis

| Variante | Arquivo SVG | Dimensões originais | Uso recomendado |
|---|---|---|---|
| `horizontal-01` | `img/logos/logo-horizontal-01.svg` | 349×65px | Contextos claros / versão alternativa |
| `horizontal-02` | `img/logos/logo-horizontal-02.svg` | 352×69px | **Header principal** (padrão) |
| `wordmark` | `img/logos/logo-wordmark.svg` | 275×36px | Rodapés, créditos, contextos compactos |
| `icone-quadrado` | `img/logos/logo-icone-quadrado.svg` | 69×69px | Avatar, thumbnail, Open Graph |
| `icone-simples` | `img/logos/logo-icone-simples.svg` | 62×69px | Marca d'água, ícone monocromático |

## Variáveis CSS utilizadas
- `--font-heading`
- `--color-text`
- `--border-radius-100`
- `--text-md-sm-size`

## Parâmetros PHP

| Parâmetro | Tipo | Default | Descrição |
|---|---|---|---|
| `$variante` | string | `'horizontal-02'` | Qual das 5 variantes renderizar |
| `$link` | bool | `true` | Envolve em `<a>` linkando para a home |
| `$url` | string | `home_url('/')` | URL de destino do link |
| `$class` | string | `''` | Classes CSS adicionais no elemento raiz |
| `$alt` | string | nome do site | Texto alternativo de acessibilidade |

## SEO aplicado
- `role="img"` + `aria-label` no wrapper — leitores de tela identificam o logo
- SVG inline tem `aria-hidden="true"` — evita leitura dupla
- Link com `aria-label` descritivo ("Geek ao Cubo — Página Inicial")
- Fallback de texto visível caso o SVG não seja encontrado

## Responsividade
- Tamanhos controlados com `clamp()` entre 375px (mobile) e 1280px (desktop)
- `horizontal-02`: `clamp(8.5rem, 20vw, 12.5rem)` de largura
- Ícones: `clamp(2rem, 5vw, 3rem)` de largura e altura

## Exemplo de uso

```php
// Header principal — variante padrão horizontal escura
mm_render_component( 'atoms', 'logo', array(
    'variante' => 'horizontal-02',
    'link'     => true,
    'url'      => home_url( '/' ),
) );

// Ícone quadrado sem link (ex: Open Graph, avatar)
mm_render_component( 'atoms', 'logo', array(
    'variante' => 'icone-quadrado',
    'link'     => false,
) );

// Wordmark no footer
mm_render_component( 'atoms', 'logo', array(
    'variante' => 'wordmark',
    'link'     => true,
    'url'      => home_url( '/' ),
    'class'    => 'footer__logo',
) );
```
