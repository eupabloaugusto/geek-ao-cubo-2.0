# Badge de Horário

**Tipo:** Átomo  
**Arquivo:** `atoms/badge-horario.php`  
**CSS:** `atoms/badge-horario.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Badge de Horário exibe de forma compacta e premium o horário exato de transmissão de um episódio (ex: "18:00", "21:30"). Ele é projetado como uma pílula escura translúcida com glassmorphism, contendo um ícone de relógio SVG inline na cor primária laranja. Ele é comumente posicionado na parte superior das capas de anime nos carrosséis e listagens de episódios diários.

## Variáveis CSS utilizadas
- `--neutral-900` / `--neutral-100` (cores do fundo e do texto)
- `--color-primary` (para destacar o ícone de relógio)
- `--border-radius-200` (raio de arredondamento de 8px)
- `--icon-xs` (tamanho do ícone de 16px)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['horario']` | string | `''` | O horário a ser exibido. Se vazio, o componente aborta a renderização |
| `$args['class']` | string | `''` | Classes adicionais para customização ou posicionamento absoluto |

## SEO aplicado
- Atributo `title` explicativo automatizado no container (ex: "Episódio às 21:00") para acessibilidade e leitores de tela.
- Ocultamento semântico do relógio SVG usando `aria-hidden="true"`.

## Responsividade
- O badge se adapta fluidamente mantendo suas proporções ideais de toque.
- Conta com efeito glassmorphic (`backdrop-filter`) de desfoque de fundo que garante contraste perfeito, mesmo se posicionado sobre imagens de capa com fundos muito claros ou texturizados.

## Exemplo de uso
```php
<?php 
// Renderiza o horário simples
mm_render_component('atoms', 'badge-horario', [
    'horario' => '18:30'
]); 
?>
```
