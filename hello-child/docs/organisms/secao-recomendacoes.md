# secao-recomendacoes

**Tipo:** Organismo  
**Arquivo:** `organisms/secao-recomendacoes.php`  
**CSS:** `organisms/secao-recomendacoes.css`  
**Criado em:** 2026-05-25  
**Última atualização:** 2026-05-25  

## Descrição

Seção de animes recomendados para a página de detalhe do anime. Exibe cards `card-recomendacao` em trilho horizontal com scroll snap nativo (sem JavaScript). O cabeçalho inclui título H2 e link opcional "Ver todas" à direita. Sem limite de cards — todos os itens do array são renderizados no trilho.

## Moléculas utilizadas

- `molecules/card-recomendacao.php` — cada card (poster 2:3 + contador de recomendações + título)

## Parâmetros PHP

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|---|---|---|---|---|
| `titulo` | string | — | `'Recomendações'` | Título da seção (H2) |
| `recomendacoes` | array | ✅ | — | Array de arrays com parâmetros de cada `card-recomendacao` |
| `ver_mais_url` | string | — | `''` | URL do link "Ver todas" no cabeçalho (omitido se vazio) |
| `ver_mais_label` | string | — | `'Ver todas'` | Label customizável do link |

### Campos do item de recomendação

| Campo | Tipo | Descrição |
|---|---|---|
| `anime_title` | string | ✅ Título do anime (obrigatório) |
| `anime_image` | string | URL da capa (opcional — fallback se ausente) |
| `anime_url` | string | URL da página do anime |
| `rec_count` | int | Número de recomendações (omitido no card se 0) |

## Variáveis CSS utilizadas

- `--neutral-100`, `--neutral-400` — título e link
- `--color-primary` — hover do link
- `--font-heading`, `--font-body` — tipografia
- `--text-sm-size`, `--text-md-sm-size`, `--text-xxs-size` — escala tipográfica
- `--space-100` a `--space-700` — espaçamentos e padding
- `--container-max` — largura máxima do inner

## SEO aplicado

- `aria-label` na `<section>` com o título
- `aria-label` no link "Ver todas" com contexto completo
- `rel="nofollow noopener"` + `target="_blank"` no link externo

## Responsividade

- **Mobile / Tablet:** trilho com scroll horizontal nativo, padding-inline `--space-400`
- **Desktop (≥ 64rem):** padding-inline `--space-600`, título maior

## Exemplo de uso

```php
mm_render_component( 'organisms', 'secao-recomendacoes', array(
    'titulo'        => 'Recomendações',
    'ver_mais_url'  => 'https://myanimelist.net/anime/5114/userrecs',
    'recomendacoes' => array(
        array(
            'anime_title' => 'Attack on Titan',
            'anime_image' => 'https://cdn.myanimelist.net/images/anime/1000/110531.jpg',
            'anime_url'   => '/anime/shingeki-no-kyojin/',
            'rec_count'   => 1842,
        ),
        array(
            'anime_title' => 'Demon Slayer',
            'anime_image' => 'https://cdn.myanimelist.net/images/anime/1286/99889.jpg',
            'anime_url'   => '/anime/kimetsu-no-yaiba/',
            'rec_count'   => 967,
        ),
        // ... mais recomendações
    ),
) );
```
