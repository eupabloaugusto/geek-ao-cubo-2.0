# Barra de Progresso (progress-bar)

## Descrição

Átomo de barra de progresso genérica para exibir percentuais (usuários, estatísticas, etc.). Com suporte a label opcional e exibição de percentual.

## Uso

```php
<?php
mm_render_component( 'atoms', 'progress-bar', array(
    'percentage'   => 75,
    'label'        => 'Usuários assistindo',
    'show_label'   => true,
    'show_percent' => true
) );
?>
```

## Parâmetros

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `percentage` | int | `0` | Percentual de progresso (0-100) |
| `label` | string | `''` | Texto do label da barra |
| `show_label` | boolean | `true` | Se deve exibir o label |
| `show_percent` | boolean | `true` | Se deve exibir o percentual |
| `class` | string | `''` | Classe CSS adicional |
| `aria_label` | string | `'Progresso: {percentage}%'` | Label de acessibilidade |

## Variáveis CSS

O componente utiliza as seguintes variáveis de design tokens:

- `--neutral-700` - Cor de fundo da track
- `--neutral-100` - Cor do percentual
- `--neutral-400` - Cor do label
- `--color-primary` - Cor inicial do gradiente
- `--brand-400` - Cor final do gradiente
- `--font-body` - Fonte do label
- `--font-heading` - Fonte do percentual
- `--text-xs-size` - Tamanho do label
- `--text-sm-size` - Tamanho do percentual
- `--font-weight-700` - Peso do label
- `--space-200` - Espaçamento entre elementos
- `--border-radius-100` - Raio da borda

## SEO

- Atributo `role="progressbar"` para semântica
- Atributos ARIA (`aria-valuenow`, `aria-valuemin`, `aria-valuemax`) para acessibilidade
- `aria-label` descritivo para leitores de tela

## Acessibilidade

- Suporta navegação por teclado
- Atributos ARIA completos para leitores de tela
- Transição suave para animação de progresso
- Validação de percentual (0-100)

## Responsividade

O componente é responsivo e se ajusta automaticamente:

- **Desktop/Tablet (≥ 768px):** Fonte padrão
- **Mobile (< 768px):** Fonte menor para label e percentual

## Estados

- **Normal:** Gradiente laranja na barra de progresso
- **Animação:** Transição suave de 0.6s ao mudar o percentual

## Exemplo de Uso

```php
<!-- Barra de progresso com label e percentual -->
<?php 
mm_render_component( 'atoms', 'progress-bar', array(
    'percentage'   => 75,
    'label'        => 'Usuários assistindo',
    'show_label'   => true,
    'show_percent' => true
) );
?>

<!-- Barra de progresso apenas com percentual -->
<?php 
mm_render_component( 'atoms', 'progress-bar', array(
    'percentage'   => 50,
    'show_label'   => false,
    'show_percent' => true
) );
?>

<!-- Barra de progresso apenas visual -->
<?php 
mm_render_component( 'atoms', 'progress-bar', array(
    'percentage'   => 90,
    'show_label'   => false,
    'show_percent' => false
) );
?>
```
