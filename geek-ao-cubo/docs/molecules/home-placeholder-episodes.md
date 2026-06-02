# Home Placeholder Episodes

**Tipo:** Molécula  
**Arquivo:** `molecules/home-placeholder-episodes.php`  
**CSS:** `molecules/home-placeholder-episodes.css`  
**Criado em:** 2026-05-27  
**Última atualização:** 2026-05-27  

## Descrição
Exibe uma grade de cards de fallback (placeholder) simulando novos episódios em formato de esqueleto (skeleton), fornecendo um design premium de estado vazio enquanto a importação da API do MAL (Jikan) ou scripts locais de carga não são executados.

## Variáveis CSS utilizadas
- `--neutral-800`
- `--neutral-700`
- `--neutral-900`
- `--border-radius-200`
- `--space-300`
- `--brand-500`

## SEO aplicado
- Marcação semântica limpa.
- Textos de internacionalização (i18n) configurados sob o text domain `geek-ao-cubo`.

## Responsividade
- Grid fluido responsivo usando `auto-fit` e `minmax` para colapsar automaticamente em resoluções mobile ou expandir em desktop.

## Exemplo de uso
```php
mm_render_component( 'molecules', 'home-placeholder-episodes' );
```
