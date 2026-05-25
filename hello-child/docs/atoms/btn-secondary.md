# Botão Secundário

**Tipo:** Átomo  
**Arquivo:** `atoms/btn-secondary.php`  
**CSS:** `atoms/btn-secondary.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O botão secundário é utilizado para ações de menor relevância ou fluxos de retorno, como retornar à página anterior, limpar filtros ou realizar ações complementares.

## Variáveis CSS utilizadas
- `--neutral-800` (background padrão)
- `--neutral-700` (background hover)
- `--neutral-400` (borda hover)
- `--color-border` (borda padrão)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['label']` | string | `'Voltar'` | O texto visível do botão |
| `$args['url']` | string | `'#'` | O endereço URL para o link |
| `$args['target']` | string | `'_self'` | O alvo do link (ex: `_blank`) |
| `$args['class']` | string | `''` | Classes adicionais para customização |
| `$args['id']` | string | `''` | Identificador HTML único |

## SEO aplicado
- HTML semântico BEM com isolamento completo.
- Suporte automático para links externos seguros.

## Responsividade
- Totalmente fluido e responsivo.
- Micro-animações em sincronia com o restante do design system.

## Exemplo de uso
```php
<?php 
mm_render_component('atoms', 'btn-secondary', [
    'label' => 'Voltar para Categoria',
    'url'   => '/animes/acao/'
]); 
?>
```
