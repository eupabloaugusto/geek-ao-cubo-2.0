# Formulário de Busca (form-busca)

**Tipo:** Molécula  
**Arquivo:** `molecules/form-busca.php`  
**CSS:** `molecules/form-busca.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Formulário de Busca (`form-busca`) é a molécula padrão do portal para captação de pesquisas de usuários. Ela une em uma única linha o campo de input de busca com ícone de lupa (`input-busca`) e o botão de disparo de envio primário (`btn-primary`).

## Átomos utilizados
- `atoms/input-busca.php`
- `atoms/btn-primary.php` (estilização integrada em tag submit)

## Variáveis CSS utilizadas
- `--space-200` (espaço horizontal de 8px entre os controles)
- `--border-radius-200` (arredondamento sincronizado)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['placeholder']` | string | `'Buscar animes...'` | O texto interno do campo de busca |
| `$args['class']` | string | `''` | Classes CSS adicionais |

## SEO e Acessibilidade
- Utilização da tag `<form>` semântica com o atributo `role="search"`.
- O botão submit de disparo conta com o atributo `aria-label="Pesquisar"` auxiliando leitores de tela na identificação da ação.
- O valor do input recupera dinamicamente a busca ativa usando a função nativa do WordPress `get_search_query()`.

## Responsividade
- O formulário se expande a `width: 100%` com um limite confortável de `max-width: 600px` (ideal para ser acoplado no Header ou na Sidebar).
- Utiliza alinhamento flexível `align-items: stretch` que garante que o input e o botão tenham alturas perfeitamente idênticas em qualquer resolução.

## Exemplo de uso
```php
<?php 
// Renderiza o formulário de busca padrão do blog
mm_render_component('molecules', 'form-busca'); 
?>
```
