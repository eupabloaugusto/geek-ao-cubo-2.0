# Nota MAL (MyAnimeList)

**Tipo:** Átomo  
**Arquivo:** `atoms/nota-mal.php`  
**CSS:** `atoms/nota-mal.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
A Nota MAL exibe o score numérico oficial do MyAnimeList para o anime correspondente. Ela possui uma inteligência integrada:
- **Notas ≥ 5.0**: São estilizadas na cor de aviso padrão (**amarela/dourada**) com base no token `--warning`.
- **Notas < 5.0**: Recebem automaticamente a classe modificadora `.nota-mal--error`, renderizando em tom de status de erro (**vermelho/coral**) com base no token `--error`.

## Variáveis CSS utilizadas
- `--warning-400` / `--warning-300` (dourado padrão para notas boas)
- `--error-400` / `--error-300` (coral para notas baixas < 5.0)
- `--font-heading` (fonte utilizada Hanken Grotesk)
- `--icon-xs` (tamanho do ícone de 16px)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['nota']` | string | `'N/A'` | A nota do anime (ex: `'8.92'`). Se nulo, cai para `'N/A'` |
| `$args['class']` | string | `''` | Classes CSS adicionais para alinhamento |

## SEO aplicado
- Atributo `title` explicativo no container para acessibilidade.
- Ícone SVG inline com `aria-hidden="true"` para evitar poluição em leitores de tela.

## Responsividade
- Totalmente fluido e intrínseco.
- Micro-animação suave de rotação de 15 graus e escala na estrela (`transform: rotate(15deg) scale(1.1)`) quando o container sofre hover, adicionando interatividade.

## Exemplo de uso
```php
<?php 
// Renderiza nota de Shingeki no Kyojin
mm_render_component('atoms', 'nota-mal', [
    'nota' => '9.13'
]); 
?>
```
