# Card de Personagem (card-personagem)

**Tipo:** Molécula  
**Arquivo:** `molecules/card-personagem.php`  
**CSS:** `molecules/card-personagem.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-24  

## Descrição

Card premium em formato de **pôster cinematográfico** (proporção 2:3) para exibição de personagens de anime na página de detalhe. A imagem do personagem cobre 100% do card como fundo; um overlay gradiente tricamadas garante legibilidade máxima do texto; um painel fixo na base exibe o badge de papel e o nome. Possui alinhamento estrito à esquerda em todos os breakpoints (incluindo mobile e tablet), espaçamento generoso de `--space-300` entre o badge e o texto, nome em japonês/Kanji opcional, e suporte a link direto para a página do personagem.

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
- **Desktop (> 768px):** Nome usa `var(--text-sm-size)`, painel usa padding `var(--space-400)` e a inicial do fallback usa `var(--text-xl-size)`.
- **Mobile/Tablet (≤ 768px):** Nome ajustado para `var(--text-xs-size)`, painel para padding `var(--space-300)` e inicial do fallback para `var(--text-md-lg-size)` via media queries (sem funções `clamp` ou cálculos complexos de viewport).

## Variantes de badge por papel
| Valor de `role` | Classe gerada | Aparência |
|---|---|---|
| `Principal` / `Main` | `card-personagem--main` | Badge laranja sólido `--brand-500` |
| `Secundário` / `Supporting` | `card-personagem--supporting` | Badge cinza translúcido glassmorphic |
| Qualquer outro | `card-personagem--other` | Badge neutro translúcido |

## Hover
- Card sobe `translateY(-6px)` + `scale(1.015)`
- Imagem interna aplica `scale(1.08)` suave
- Borda e box-shadow com glow laranja
- Painel sobe `translateY(-3px)`
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
