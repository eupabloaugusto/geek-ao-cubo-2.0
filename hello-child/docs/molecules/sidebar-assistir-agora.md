# Card Assistir Agora (sidebar-assistir-agora)

**Tipo**: Molécula
**Arquivo**: `molecules/sidebar-assistir-agora.php`
**CSS**: `molecules/sidebar-assistir-agora.css`
**Criado em**: 2026-05-23
**Última atualização**: 2026-05-23

## Descrição
Card promocional lateral (CTA) para direcionar usuários a assistir o anime em canais oficiais. Combina imagem-capa como fundo, textos de cabeçalho, descrição da plataforma e btn-primary como ação.

## Átomos utilizados
- `imagem-capa` — Imagem de fundo com overlay
- `btn-primary` — Botão de ação CTA

## Variáveis CSS utilizadas
- `--color-primary`
- `--color-border`
- `--neutral-100`, `--neutral-300`, `--neutral-800`, `--neutral-900`
- `--text-xxs-size`, `--text-md-sm-size`
- `--font-heading`, `--font-body`
- `--font-weight-700`, `--font-weight-400`
- `--space-100`, `--space-200`, `--space-400`, `--space-500`
- `--border-radius-300`

## Parâmetros PHP
| Parâmetro | Tipo | Descrição |
|---|---|---|
| `$title` | string | Tag de ação (padrão: 'ASSISTA AGORA') |
| `$platform_name` | string | Nome da plataforma (padrão: 'Crunchyroll') |
| `$description` | string | Descrição da plataforma |
| `$image_url` | string | URL da imagem de fundo |
| `$stream_url` | string | URL do link de streaming |
| `$class` | string | Classes adicionais |

## SEO aplicado
- Alt text nas imagens
- Links de afiliado com rel="sponsored"

## Responsividade
- Mobile (375px): Largura total, padding adequado
- Desktop (1280px): Max-width de 340px no preview, layout fluido

## Exemplo de uso
```php
mm_render_component('molecules', 'sidebar-assistir-agora', [
    'title'         => 'ASSISTA AGORA',
    'platform_name' => 'Crunchyroll',
    'description'   => 'Temporadas completas com dublagem e legendas em português.',
    'image_url'     => 'https://geekaocubo.com.br/assets/capa-anime.jpg',
    'stream_url'    => '#'
]);
```
