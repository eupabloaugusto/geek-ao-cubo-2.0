# secao-estatisticas

**Tipo:** Organismo  
**Arquivo:** `organisms/secao-estatisticas.php`  
**CSS:** `organisms/secao-estatisticas.css`  
**Criado em:** 2026-05-25  
**Última atualização:** 2026-05-25  

## Descrição

Seção de estatísticas para a página de detalhe do anime. Exibe blocos `stat-bloco` em trilho horizontal com scroll snap nativo (sem JavaScript), idêntico ao padrão de `secao-esteira-animes`. Ideal para exibir múltiplos conjuntos de dados lado a lado (ex: stats de diferentes fontes ou temporadas). Cabeçalho com título H2 e link opcional "Ver no MAL".

## Moléculas utilizadas

- `molecules/stat-bloco.php` — cada slide (rating-score grande + grid de rank/popularidade/membros)

## Parâmetros PHP

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|---|---|---|---|---|
| `titulo` | string | — | `'Estatísticas'` | Título da seção (H2) |
| `estatisticas` | array | ✅ | — | Array de arrays com parâmetros de cada `stat-bloco` |
| `ver_mais_url` | string | — | `''` | URL do link no cabeçalho (omitido se vazio) |
| `ver_mais_label` | string | — | `'Ver no MAL'` | Label customizável do link |

### Parâmetros de cada item (`stat-bloco`)

| Campo | Tipo | Descrição |
|---|---|---|
| `score` | string | Nota média (ex: `'9.37'`) |
| `score_label` | string | Label do score. Default: `'Média'` |
| `score_votes` | string | Nº de votantes (ex: `'1,9M votos'`) |
| `rank` | string | Posição no ranking (ex: `'#1'`) |
| `rank_label` | string | Label do rank. Default: `'Ranking'` |
| `popularity` | string | Posição em popularidade (ex: `'#3'`) |
| `pop_label` | string | Label. Default: `'Popularidade'` |
| `members` | string | Total de membros (ex: `'3,5M'`) |
| `members_label` | string | Label. Default: `'Membros'` |

## Variáveis CSS utilizadas

- `--neutral-100`, `--neutral-400` — título e link
- `--color-primary` — hover do link
- `--font-heading`, `--font-body` — tipografia
- `--text-sm-size`, `--text-md-sm-size`, `--text-xxs-size` — escala tipográfica
- `--space-100` a `--space-700` — espaçamentos
- `--container-max` — largura máxima do inner

## SEO aplicado

- `aria-label` na `<section>` com o título
- `aria-label` no link com contexto completo
- `rel="nofollow noopener"` + `target="_blank"` no link externo

## Responsividade

- **Mobile / Tablet:** slide `20rem`, padding-inline `--space-400`
- **Desktop (≥ 64rem):** slide `24rem`, padding-inline `--space-600`, título maior

## Exemplo de uso

```php
mm_render_component( 'organisms', 'secao-estatisticas', array(
    'titulo'       => 'Estatísticas',
    'ver_mais_url' => 'https://myanimelist.net/anime/5114/stats',
    'estatisticas' => array(
        array(
            'score'       => '9.37',
            'score_label' => 'Nota Média',
            'score_votes' => '1,9M votos',
            'rank'        => '#1',
            'popularity'  => '#3',
            'members'     => '3,5M',
        ),
        array(
            'score'        => '9.12',
            'score_label'  => 'Nota — Temporada 2',
            'rank'         => '#4',
            'popularity'   => '#7',
            'members'      => '2,1M',
        ),
    ),
) );
```
