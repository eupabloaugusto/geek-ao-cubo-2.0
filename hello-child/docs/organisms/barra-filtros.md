# Painel de Filtros e Busca de Animes (barra-filtros)

**Tipo:** Organismo  
**Arquivo:** `organisms/barra-filtros.php`  
**CSS:** `organisms/barra-filtros.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Painel de Filtros e Busca de Animes (`barra-filtros`) é o organismo encarregado de prover filtragem de dados avançada e pesquisas textuais em tempo real na parte superior de listagens de animes (arquivos, temporadas e catálogos gerais). Ela integra de forma modular 4 instâncias da molécula de campo de formulário (`form-field`) e o botão de disparo de envio.

## Componentes Utilizados
- **Moléculas:**
  - `molecules/form-field.php` (utilizadas sem labels superiores para criar um visual minimalista e plano)
- **Átomos:**
  - `atoms/input-busca.php` (embutido via form-field)
  - `atoms/btn-primary.php` (estilização integrada em tag submit)

## Variáveis CSS utilizadas
- `--space-300` (espaço horizontal de 16px entre colunas)
- `--neutral-800` / `--neutral-900` (gradiente de fundo escuro premium)
- `--border-radius-300` (arredondamento de 16px)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['class']` | string | `''` | Classes CSS adicionais para posicionamento externo |

## SEO e Acessibilidade
- Utilização da tag `<form>` semântica com o atributo `role="search"`.
- O botão de disparo (`submit`) possui rotulagem clara.
- Recupera dinamicamente a pesquisa textual ativa no banco via `get_search_query()`.
- Ocultamento de ícones SVG meramente ilustrativos via `aria-hidden="true"`.

## Responsividade
- **Desktop (≥ 768px):** Exibição em linha horizontal compacta, ocupando espaço proporcional e alinhamento vertical central (`align-items: flex-end`). O campo de busca ganha mais peso horizontal (`flex: 1.8`).
- **Tablet (480px - 768px):** Distribuição inteligente em grade flexível de 2 colunas. A busca expande-se para 100% de largura no topo.
- **Mobile (< 480px):** Empilhamento vertical total de 100% de todos os campos para maximizar a área de toque em smartphones.

## Exemplo de uso
```php
<?php 
// Renderiza o painel de filtros padrão no topo dos arquivos de animes
mm_render_component('organisms', 'barra-filtros'); 
?>
```
