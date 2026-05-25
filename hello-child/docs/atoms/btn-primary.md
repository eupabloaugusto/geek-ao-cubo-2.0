# Botão Primário

**Tipo:** Átomo  
**Arquivo:** `atoms/btn-primary.php`  
**CSS:** `atoms/btn-primary.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O botão primário é o principal componente de Call-to-Action (CTA) do blog. Ele é utilizado para ações de alta relevância, como redirecionar o usuário para ver mais episódios ou links externos patrocinados.

## Variáveis CSS utilizadas
- `--color-primary` (para o background padrão)
- `--brand-600` (para o estado de hover)
- `--neutral-100` (para a cor de texto e foco)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['label']` | string | `'Ver mais'` | O texto visível do botão |
| `$args['url']` | string | `'#'` | O endereço URL para o link |
| `$args['target']` | string | `'_self'` | O alvo do link (ex: `_blank`) |
| `$args['class']` | string | `''` | Classes adicionais para customização secundária |
| `$args['id']` | string | `''` | Identificador HTML único do botão |
| `$args['is_affiliate']` | boolean | `false` | Se ativo, adiciona `rel="sponsored"` para SEO |

## SEO aplicado
- Suporte a `rel="sponsored"` automatizado para conformidade com links de afiliados.
- Suporte a `rel="noopener noreferrer"` para links externos (`_blank`).
- Marcação semântica com tag `<a>` pura e classes BEM.

## Responsividade
- O botão se ajusta fluidamente de forma interna.
- Utiliza micro-animações suaves (`transition` de 0.3s com `cubic-bezier`) para dar excelente feedback no clique e hover.

## Exemplo de uso
```php
<?php 
mm_render_component('atoms', 'btn-primary', [
    'label'        => 'Comprar na Shopee',
    'url'          => 'https://shope.ee/...',
    'target'       => '_blank',
    'is_affiliate' => true
]); 
?>
```
