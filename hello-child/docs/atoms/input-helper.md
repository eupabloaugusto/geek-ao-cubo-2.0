# Texto Auxiliar / Mensagem de Erro (input-helper)

**Tipo:** Átomo  
**Arquivo:** `atoms/input-helper.php`  
**CSS:** `atoms/input-helper.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Texto Auxiliar / Mensagem de Erro (`input-helper`) é o átomo encarregado de fornecer suporte textual informativo ou validações de erros imediatamente abaixo de um input de formulário.

## Variáveis CSS utilizadas
- `--neutral-500` (cor para mensagens informativas)
- `--error-400` (cor coral para alertas de erro de validação)
- `--text-xxs-size` (tamanho de 12px)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['text']` | string | `''` | O texto de instrução ou erro |
| `$args['is_error']` | boolean | `false` | Se verdadeiro, converte a aparência para erro e injeta atributos de acessibilidade |
| `$args['class']` | string | `''` | Classes CSS adicionais |

## SEO e Acessibilidade
- Quando `$args['is_error']` está ativo, o átomo renderiza o atributo `role="alert"` e `aria-live="assertive"`, forçando os leitores de tela a notificar imediatamente o usuário deficiente visual sobre o erro ocorrido.

## Responsividade
- Totalmente fluido e compatível com layouts de formulários de coluna simples ou múltipla.

## Exemplo de uso
```php
<?php 
// 1. Mensagem de ajuda comum
mm_render_component('atoms', 'input-helper', [
    'text' => 'Digite pelo menos 3 caracteres para a busca'
]); 

// 2. Alerta de erro de preenchimento
mm_render_component('atoms', 'input-helper', [
    'text'     => 'Este campo de e-mail é obrigatório.',
    'is_error' => true
]);
?>
```
