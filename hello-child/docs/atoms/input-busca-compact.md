# Input de Busca Compacto (input-busca-compact)

## Descrição

Átomo de input de busca simplificado para uso no header. Diferente do `form-busca`, este componente não possui botão de submit integrado. Quando clicado (estado readonly), abre o modal de busca completo (`search-modal`) através da classe `.js-open-search-modal`.

## Uso

```php
<?php
mm_render_component( 'atoms', 'input-busca-compact', array(
    'placeholder' => 'Pesquisar...',
    'class'       => 'header__search-input',
    'readonly'    => true
) );
?>
```

## Parâmetros

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `placeholder` | string | `'Pesquisar...'` | Texto de placeholder do input |
| `class` | string | `''` | Classe CSS adicional para o input |
| `readonly` | boolean | `true` | Se o input deve ser readonly (para abrir modal) |
| `name` | string | `'s'` | Nome do atributo name do input |
| `id` | string | `''` | ID do input |
| `aria_label` | string | `'Abrir pesquisa'` | Label de acessibilidade |

## Variáveis CSS

O componente utiliza as seguintes variáveis de design tokens:

- `--neutral-800` - Cor de fundo do input
- `--color-border` - Cor da borda
- `--border-radius-200` - Raio da borda
- `--neutral-100` - Cor do texto
- `--neutral-500` - Cor do placeholder
- `--neutral-400` - Cor do ícone
- `--color-primary` - Cor de foco/hover
- `--font-body` - Fonte do texto
- `--text-sm-size` - Tamanho da fonte
- `--space-400` - Espaçamento interno
- `--space-500` - Espaçamento interno
- `--icon-sm` - Tamanho do ícone

## SEO

- O input possui `aria-label` para acessibilidade
- Quando readonly, o label indica "Abrir pesquisa" para clarificar a ação
- O ícone de lupa é decorativo (`aria-hidden="true"`)

## Acessibilidade

- Suporta navegação por teclado
- Estado claro de foco com borda e sombra
- `aria-label` apropriado para leitores de tela
- Ícone decorativo marcado como `aria-hidden="true"`

## Responsividade

O componente é responsivo e se ajusta automaticamente:

- **Desktop/Tablet (≥ 768px):** Altura de 3rem (48px)
- **Mobile (< 768px):** Altura de 2.5rem (40px), fonte menor

## Estados

- **Normal:** Fundo `--neutral-800`, borda sutil
- **Hover:** Borda mais clara, fundo levemente mais claro
- **Focus:** Borda `--color-primary`, sombra de foco
- **Readonly:** Cursor pointer, comportamento de clique para abrir modal

## Integração com Modal

Para funcionar corretamente com o `search-modal`:

1. O input deve ter a classe `js-open-search-modal`
2. O input deve ser `readonly`
3. O `search-modal.js` deve estar carregado na página
4. O modal deve ser renderizado na página (geralmente junto com o header)

## Exemplo de Integração no Header

```php
<!-- No organisms/header.php -->
<div class="header__search">
    <?php 
    mm_render_component( 'atoms', 'input-busca-compact', array(
        'placeholder' => __( 'Pesquisar...', 'hello-elementor-child' ),
        'class'       => 'header__search-input',
        'readonly'    => true
    ) );
    ?>
</div>

<!-- Renderizar modal junto com o header -->
<?php 
mm_render_component( 'organisms', 'search-modal' );
?>
```
