# Home Placeholder Carousel

**Tipo:** Molécula  
**Arquivo:** `molecules/home-placeholder-carousel.php`  
**CSS:** `molecules/home-placeholder-carousel.css`  
**Criado em:** 2026-05-27  
**Última atualização:** 2026-05-27  

## Descrição
Exibe uma seção de fallback (placeholder) elegante no topo da homepage do portal caso não existam artigos/notícias marcados como Destaque no banco de dados local.

## Variáveis CSS utilizadas
- `--neutral-800`
- `--neutral-900`
- `--brand-400`
- `--brand-500`
- `--border-radius-300`
- `--space-600`
- `--text-md-sm-size`
- `--text-md-lg-size`

## SEO aplicado
- Semântica HTML5 com container estruturado.
- Textos de internacionalização (i18n) configurados sob o text domain `geek-ao-cubo`.

## Responsividade
- Tipografia fluida usando `clamp()` para o título de forma a responder perfeitamente do mobile ao desktop sem quebras de layout.

## Exemplo de uso
```php
mm_render_component( 'molecules', 'home-placeholder-carousel' );
```
