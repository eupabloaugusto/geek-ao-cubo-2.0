# Barra Lateral (Sidebar)

**Tipo:** Organismo  
**Arquivo:** `organisms/sidebar.php`  
**CSS:** `organisms/sidebar.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
A Barra Lateral (`sidebar`) é o organismo encarregado de exibir widgets modulares e de engajamento na lateral direita das páginas de posts simples (`single.php`) ou de arquivos. Ela compila e gerencia a molécula de busca (`form-busca`), uma lista premium de destaques da temporada (compondo notas MAL e badges de status) e o bloco de anúncios publicitários (`anuncio-adsense`).

## Componentes Utilizados
- **Átomos:**
  - `atoms/nota-mal.php`
  - `atoms/badge-status.php`
  - `atoms/anuncio-adsense.php`
- **Moléculas:**
  - `molecules/form-busca.php`

## Variáveis CSS utilizadas
- `--space-600` (espaço entre widgets modulares)
- `--color-card-bg` / `--color-border` (fundo e borda dos widgets)
- `--border-radius-300` (arredondamento de 16px)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['class']` | string | `''` | Classes CSS adicionais para posicionamento externo |
| `$args['adsense_slot']` | string | `'9876543210'` | Identificador do slot de publicidade do AdSense |

## SEO e Acessibilidade
- Estruturação semântica com a tag HTML5 `<aside>` com a marcação de acessibilidade `role="complementary"` e o atributo `aria-label="Barra Lateral"`.
- Cada widget interno é isolado por uma tag `<section>` com cabeçalho de hierarquia coerente.

## Responsividade
- Em resoluções desktop (≥ 1024px), a barra lateral assume seu papel de coluna direita ao lado do grid principal.
- Em telas mobile (< 1024px), o layout flui perfeitamente, posicionando os widgets de forma empilhada sequencial abaixo do conteúdo principal, mantendo a responsividade do formulário de busca e do anúncio Adsense.

## Exemplo de uso
```php
<?php 
// Renderiza a barra lateral padrão nas páginas single
mm_render_component('organisms', 'sidebar'); 
?>
```
