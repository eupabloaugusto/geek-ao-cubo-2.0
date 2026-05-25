# Aviso Adblock

**Tipo:** Átomo  
**Arquivo:** `atoms/aviso-adblock.php`  
**CSS:** `atoms/aviso-adblock.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Aviso Adblock é um banner fixo e não intrusivo que solicita amigavelmente ao leitor a desativação de bloqueadores de anúncios (Adblock). Ele foi projetado em conformidade com as diretrizes do Google contra popups intrusivos e integra dois botões atômicos: um para ensinar a desativar e outro para guiar o usuário à nossa imune vitrine de afiliados.

## Átomos utilizados
- `atoms/btn-primary.php`
- `atoms/btn-secondary.php`

## Variáveis CSS utilizadas
- `--neutral-800` / `--neutral-900` (gradiente de fundo)
- `--color-primary` / `--brand-700` (gradiente da barra esquerda e ícone)
- `--color-border` (borda neutra fina de 1px)
- `--font-heading` (para o título H4)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['class']` | string | `''` | Classes CSS adicionais para customização externa ou posicionamento |

## SEO aplicado
- Elemento com marcação semântica `role="alert"` e `aria-live="polite"` informando acessibilidade para leitores de tela sem bloquear a navegação.
- Sem popups agressivos (evita punições de SEO do Google por interstitials intrusivos desde 2017).

## Responsividade
- O preenchimento (`padding`) e as margens adaptam-se fluidamente através de `clamp()`.
- Em telas mobile (< 480px), os botões de ação empilham verticalmente ocupando 100% de largura para otimizar o clique em telas de toque (Mobile-Friendly).

## Exemplo de uso
```php
<?php 
// Renderiza o aviso de adblock (geralmente inserido em posições fixas, sidebar ou rodapé)
mm_render_component('atoms', 'aviso-adblock'); 
?>
```
