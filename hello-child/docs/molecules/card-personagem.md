# Card de Personagem (card-personagem)

**Tipo:** Molécula  
**Arquivo:** `molecules/card-personagem.php`  
**CSS:** `molecules/card-personagem.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-25  

## Descrição

Card premium em formato de **pôster cinematográfico** (proporção 2:3) para exibição de personagens de anime. A estrutura possui o nome e dados do personagem do lado de fora (abaixo da imagem), enquanto o badge de papel (Principal / Secundário) fica posicionado de forma absoluta sobreposta na imagem. Garante alinhamento estrito à esquerda em todos os breakpoints e uma excelente micro-interação no hover, onde a imagem e o pôster elevam com transição 3D suave, enquanto o texto ganha um acento de cor de forma estática e elegante.

## Átomos utilizados
_Nenhum átomo importado via `mm_render_component`. CSS e markup embutidos._

## Variáveis CSS utilizadas
- `--brand-500` — acento laranja para badge Principal e glow de hover
- `--brand-900` — parada de gradiente do fallback
- `--neutral-100`, `--neutral-200` — texto claro
- `--neutral-400`, `--neutral-500` — texto secundário e letra inicial do fallback
- `--neutral-700`, `--neutral-800`, `--neutral-900` — fundos e fallback
- `--color-border` — borda sutil inicial
- `--font-heading` — nome do personagem e letra inicial
- `--font-body` — badge e nome em japonês
- `--font-weight-400`, `--font-weight-700` — pesos tipográficos
- `--text-xxs-size` — badge e nome em japonês
- `--text-xs-size` — nome do personagem (teto do clamp)
- `--space-300`, `--space-400` — gaps e padding do painel
- `--border-radius-100` — badge
- `--border-radius-300` — card

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['name']` | string | `''` | Nome do personagem (obrigatório para renderizar) |
| `$args['name_kanji']` | string | `''` | Nome em japonês/kanji (opcional, aparece abaixo do nome) |
| `$args['image_url']` | string | `''` | URL da imagem de capa do personagem |
| `$args['role']` | string | `''` | Papel: `'Principal'`, `'Secundário'` ou outro |
| `$args['url']` | string | `''` | URL da página do personagem. Se fornecida, o card vira `<a>` |
| `$args['class']` | string | `''` | Classe CSS adicional |
| `$args['aria_label']` | string | `'Personagem: {name}'` | Label de acessibilidade |

## SEO aplicado
- Imagem com `alt` descritivo: _"Imagem do personagem {name}"_
- Atributo `aria-label` contextual no wrapper
- Wrapper torna-se `<a>` semântico quando `url` é fornecida
- `loading="lazy"` e `decoding="async"` na imagem
- Nome do personagem em `<p>` visível para indexação, alinhado à esquerda

## Responsividade
- Card cresce/encolhe conforme a largura da coluna (ideal em grade `auto-fill`).
- **Desktop (> 768px):** Nome usa `var(--text-sm-size)` e a inicial do fallback usa `var(--text-xl-size)`.
- **Mobile/Tablet (≤ 768px):** Nome ajustado para `var(--text-xs-size)`, badge aproximada das bordas para `var(--space-200)` e inicial do fallback para `var(--text-md-lg-size)` via media queries (sem funções `clamp` ou cálculos complexos de viewport).

## Variantes de badge por papel
| Valor de `role` | Classe gerada | Aparência |
|---|---|---|
| `Principal` / `Main` | `card-personagem--main` | Badge laranja sólido `--brand-500` |
| `Secundário` / `Supporting` | `card-personagem--supporting` | Badge cinza translúcido glassmorphic |
| Qualquer outro | `card-personagem--other` | Badge neutro translúcido |

## Hover
- Apenas o pôster (`.card-personagem__poster`) sobe `translateY(-6px)` + `scale(1.015)`
- Imagem interna aplica `scale(1.08)` suave
- Borda e box-shadow com glow laranja ao redor do pôster
- Nome do personagem muda de cor para `--brand-300` de forma estática
- Nome em japonês/kanji aumenta opacity para 1.0

## Exemplo de uso
```php
<?php
// Card Principal com link e nome em japonês
mm_render_component( 'molecules', 'card-personagem', array(
    'name'        => 'Tanjiro Kamado',
    'name_kanji'  => '竈門 炭治郎',
    'image_url'   => 'https://exemplo.com/tanjiro.webp',
    'role'        => 'Principal',
    'url'         => '/personagem/tanjiro-kamado/',
) );

// Card Secundário sem link
mm_render_component( 'molecules', 'card-personagem', array(
    'name'      => 'Zenitsu Agatsuma',
    'image_url' => 'https://exemplo.com/zenitsu.webp',
    'role'      => 'Secundário',
) );

// Card sem imagem (fallback com inicial)
mm_render_component( 'molecules', 'card-personagem', array(
    'name' => 'Nezuko Kamado',
    'role' => 'Principal',
) );
?>
```
