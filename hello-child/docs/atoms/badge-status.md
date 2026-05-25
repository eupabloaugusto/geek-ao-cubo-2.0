# Badge de Status

**Tipo:** Átomo  
**Arquivo:** `atoms/badge-status.php`  
**CSS:** `atoms/badge-status.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O badge de status é um pequeno indicador visual em formato de pílula que exibe a condição atual de transmissão de um anime. Possui três estados principais integrados: Em exibição, Finalizado e Em breve.

## Variáveis CSS utilizadas
- `--success-600` / `--success-500` (para o estado `airing`)
- `--neutral-300` / `--neutral-400` (para o estado `completed`)
- `--brand-400` (para o estado `upcoming`)
- `--text-xxs-size` (tamanho da fonte de 12px)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['status']` | string | `'completed'` | O estado lógico do badge: `'airing'`, `'completed'`, ou `'upcoming'` |
| `$args['label']` | string | *Dinâmico* | Se fornecido, substitui o texto automático correspondente ao status |
| `$args['class']` | string | `''` | Classes CSS adicionais |

## SEO aplicado
- Semântica pura e legibilidade garantida para leitores de tela.
- Micro-badge de texto sem interrupção de fluxo semântico.

## Responsividade
- O componente é autônomo, adaptando-se perfeitamente à largura do container ou mantendo seu tamanho intrínseco.
- Inclui animação infinita de pulsação (`@keyframes mm-badge-pulse`) no indicador de exibição simultânea (`airing`), capturando a atenção do usuário para conteúdos em lançamento.

## Exemplo de uso
```php
<?php 
// Renderiza o badge de Solo Leveling (atualmente em exibição)
mm_render_component('atoms', 'badge-status', [
    'status' => 'airing'
]); 

// Renderiza o badge de Frieren (finalizado)
mm_render_component('atoms', 'badge-status', [
    'status' => 'completed'
]); 
?>
```
