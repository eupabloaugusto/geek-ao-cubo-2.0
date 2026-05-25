# Ícone de Link Externo (icone-externo-link)

## Descrição

Átomo de ícone + label para links externos (ANN, Wiki, etc.). Indica visualmente que o link abre em nova aba/janela através do ícone de seta externa.

## Uso

```php
<?php
mm_render_component( 'atoms', 'icone-externo-link', array(
    'label'     => 'MyAnimeList',
    'url'       => 'https://myanimelist.net/anime/123',
    'target'    => '_blank',
    'rel'       => 'noopener noreferrer'
) );
?>
```

## Parâmetros

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `label` | string | `''` | Texto do label do link |
| `url` | string | `'#'` | URL do link externo |
| `icon_name` | string | `'external'` | Nome do ícone (reservado para uso futuro) |
| `target` | string | `'_blank'` | Target do link (padrão: nova aba) |
| `rel` | string | `'noopener noreferrer'` | Atributo rel para segurança |
| `class` | string | `''` | Classe CSS adicional |
| `aria_label` | string | `'Abrir {label} em nova aba'` | Label de acessibilidade |

## Variáveis CSS

O componente utiliza as seguintes variáveis de design tokens:

- `--neutral-400` - Cor do texto normal
- `--neutral-100` - Cor do texto em hover
- `--neutral-600` - Cor da borda em hover
- `--color-primary` - Cor do ícone em hover e foco
- `--font-body` - Fonte do texto
- `--text-xs-size` - Tamanho da fonte
- `--font-weight-400` - Peso da fonte
- `--space-200` - Espaçamento entre ícone e label
- `--space-300` - Padding horizontal
- `--border-radius-100` - Raio da borda
- `--icon-xs` - Tamanho do ícone

## SEO

- Links externos usam `target="_blank"` e `rel="noopener noreferrer"` para segurança
- `aria-label` descreve a ação de abrir em nova aba
- Ícone marcado como `aria-hidden="true"` (decorativo)

## Acessibilidade

- Suporta navegação por teclado
- Estado claro de foco com borda e sombra
- `aria-label` apropriado para leitores de tela
- Ícone decorativo marcado como `aria-hidden="true"`
- `rel="noopener noreferrer"` para segurança em links externos

## Responsividade

O componente é responsivo e se ajusta automaticamente:

- **Desktop/Tablet (≥ 768px):** Fonte `var(--text-xs-size)`, padding padrão
- **Mobile (< 768px):** Fonte `var(--text-xxs-size)`, padding reduzido

## Estados

- **Normal:** Cor `--neutral-400`, fundo transparente
- **Hover:** Cor `--neutral-100`, fundo sutil, ícone `--color-primary`
- **Focus:** Borda `--color-primary`, sombra de foco

## Exemplo de Uso

```php
<!-- Link para MyAnimeList -->
<?php 
mm_render_component( 'atoms', 'icone-externo-link', array(
    'label' => 'MyAnimeList',
    'url'   => 'https://myanimelist.net/anime/123'
) );
?>

<!-- Link para Anime News Network -->
<?php 
mm_render_component( 'atoms', 'icone-externo-link', array(
    'label' => 'Anime News Network',
    'url'   => 'https://www.animenewsnetwork.com/encyclopedia/anime.php?id=123'
) );
?>

<!-- Link para Wikipedia -->
<?php 
mm_render_component( 'atoms', 'icone-externo-link', array(
    'label' => 'Wikipedia',
    'url'   => 'https://en.wikipedia.org/wiki/Anime_Name'
) );
?>
```
