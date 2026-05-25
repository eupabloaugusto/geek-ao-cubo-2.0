# Rótulo de Input (input-label)

**Tipo:** Átomo  
**Arquivo:** `atoms/input-label.php`  
**CSS:** `atoms/input-label.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Rótulo de Input (`input-label`) é o átomo responsável por exibir o título ou identificação de um campo de formulário, fornecendo clareza semântica e acessibilidade. É estilizado de forma minimalista em caixa alta (uppercase) com a fonte do Design Tokens para cabeçalhos (`Hanken Grotesk`).

## Variáveis CSS utilizadas
- `--neutral-400` / `--neutral-300` (cores padrão e ativa)
- `--font-heading` (fonte utilizada Hanken Grotesk)
- `--text-xxs-size` (tamanho de 12px)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['label']` | string | `''` | O texto visível do rótulo |
| `$args['for']` | string | `''` | O ID do input associado (essencial para acessibilidade) |
| `$args['class']` | string | `''` | Classes CSS adicionais |

## SEO e Acessibilidade
- Implementação correta do atributo `for` ligando semanticamente o rótulo ao campo de input correspondente, o que é um requisito essencial de acessibilidade para leitores de tela (WCAG).

## Responsividade
- O rótulo se adapta de forma fluida a qualquer grid de formulário.

## Exemplo de uso
```php
<?php 
mm_render_component('atoms', 'input-label', [
    'label' => 'Nome do Anime',
    'for'   => 'anime_name_input'
]); 
?>
```
