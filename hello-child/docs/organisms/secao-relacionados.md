# secao-relacionados

**Tipo:** Organism  
**Arquivo PHP:** `organisms/secao-relacionados.php`  
**Arquivo CSS:** `organisms/secao-relacionados.css`  
**Depende de:** `molecules/relacionado-item`  
**Fonte de dados:** 🔵 MyAnimeList / Jikan API (`/anime/{id}/relations`)

---

## Descrição

Seção responsiva que exibe animes, mangás e outras mídias relacionadas, agrupados dinamicamente em PHP por seu tipo de relação (ex: "Sequência", "Adaptação", "Prequel", "História Paralela"). 

O organismo recebe um array plano de dados e cuida de agrupar, ordenar e renderizar cada sub-categoria sob um subtítulo estilizado com uma barra vertical laranja (`--color-primary`) à esquerda.

---

## Parâmetros PHP

| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `titulo` | `string` | `'Conteúdo Relacionado'` | Título principal do `<h2>` da seção |
| `items` | `array` | `[]` | Array plano de itens contendo dados de cada mídia relacionada |

### Estrutura de cada item em `items`

| Chave | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `anime_title` | `string` | ✅ Sim | Título da mídia relacionada |
| `anime_image` | `string` | ❌ Não | URL da imagem de capa (proporção 2:3) |
| `anime_url` | `string` | ❌ Não | Link para a página interna da mídia |
| `relation_type` | `string` | ❌ Não | Tipo de relação (ex: `'Sequência'`). Agrupa itens sob esse nome. |

---

## Responsividade

| Breakpoint | Comportamento |
|---|---|
| `< 48rem` (mobile) | Grid de 1 coluna por grupo. Cards compactos empilhados verticalmente. |
| `≥ 48rem` (tablet/desktop) | Grid de 2 colunas por grupo. Aproveitamento otimizado do espaço. |
| `≥ 75rem` (desktop largo) | Grid de 3 colunas por grupo. Visualização ultra-compacta e elegante. |

---

## Variáveis CSS utilizadas

- `--container-max`, `--space-300` a `--space-700`
- `--font-heading`, `--text-sm-size`, `--text-md-sm-size`, `--text-xs-size`
- `--neutral-100`, `--neutral-300`
- `--color-primary` (para o indicador visual de borda vertical)

---

## Exemplo de uso

```php
mm_render_component( 'organisms', 'secao-relacionados', array(
    'titulo' => 'Conteúdo Relacionado',
    'items'  => array(
        array(
            'anime_title'   => 'Fullmetal Alchemist: Brotherhood',
            'anime_image'   => 'https://cdn.myanimelist.net/images/anime/1223/96541.jpg',
            'anime_url'     => '/anime/fullmetal-alchemist-brotherhood/',
            'relation_type' => 'Sequência',
        ),
        array(
            'anime_title'   => 'Fullmetal Alchemist (Manga)',
            'anime_image'   => 'https://cdn.myanimelist.net/images/manga/3/25.jpg',
            'anime_url'     => '/manga/fullmetal-alchemist/',
            'relation_type' => 'Adaptação',
        ),
        array(
            'anime_title'   => 'Chibi Alchemist',
            'relation_type' => 'Spin-off',
        ),
    ),
) );
```
