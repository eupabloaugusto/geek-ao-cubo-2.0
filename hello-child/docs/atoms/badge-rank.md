# Badge de Ranking (badge-rank)

## Descrição

Átomo de badge especial de ranking com cor dourada para destaque. Usado para exibir rankings especiais (#1, Top 10) na página de detalhe do anime.

## Uso

```php
<?php
mm_render_component( 'atoms', 'badge-rank', array(
    'rank'    => '#1',
    'variant' => 'default'
) );
?>
```

## Parâmetros

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `rank` | string | `'#1'` | Texto do ranking (ex: "#1", "Top 10") |
| `variant` | string | `'default'` | Variação do badge: `default`, `top10` |
| `class` | string | `''` | Classe CSS adicional |
| `aria_label` | string | `'Ranking {rank}'` | Label de acessibilidade |

## Variáveis CSS

O componente utiliza as seguintes variáveis de design tokens:

- `--warning-400`, `--warning-500` - Cores de gradiente dourado
- `--warning-300` - Cor para variação Top 10
- `--brand-500` - Cor de gradiente secundária
- `--neutral-900` - Cor do texto
- `--font-heading` - Fonte do texto
- `--text-sm-size` - Tamanho da fonte
- `--text-xs-size` - Tamanho da fonte em mobile
- `--text-sm-weight` - Peso da fonte
- `--space-200`, `--space-400` - Espaçamento interno
- `--space-100`, `--space-300` - Espaçamento interno em mobile
- `--border-radius-200` - Raio da borda

## SEO

- `aria-label` descritivo para leitores de tela
- Texto em HTML para indexação

## Acessibilidade

- Suporta navegação por teclado
- Estado de foco visível
- `aria-label` apropriado para leitores de tela

## Responsividade

O componente é responsivo e se ajusta automaticamente:

- **Desktop/Tablet (≥ 768px):** Fonte padrão, padding completo
- **Mobile (< 768px):** Fonte menor, padding reduzido

## Variações

- **default:** Gradiente laranja/dourado padrão
- **top10:** Gradiente amarelo mais claro para Top 10

## Estados

- **Normal:** Gradiente dourado com sombra sutil
- **Hover:** Elevação sutil e sombra mais intensa
- **Focus:** Borda de foco com sombra

## Exemplo de Uso

```php
<!-- Badge padrão #1 -->
<?php 
mm_render_component( 'atoms', 'badge-rank', array(
    'rank'    => '#1',
    'variant' => 'default'
) );
?>

<!-- Badge Top 10 -->
<?php 
mm_render_component( 'atoms', 'badge-rank', array(
    'rank'    => 'Top 10',
    'variant' => 'top10'
) );
?>
```
