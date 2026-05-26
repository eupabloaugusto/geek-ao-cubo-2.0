# secao-noticias-recentes

**Tipo:** Organism  
**Arquivo PHP:** `organisms/secao-noticias-recentes.php`  
**Arquivo CSS:** `organisms/secao-noticias-recentes.css`  
**Depende de:** `molecules/card-noticia`  
**Fonte de dados:** 🟠 Crunchyroll News / Feed do WordPress

---

## Descrição

Seção editorial de alto impacto para listagem de notícias, artigos ou novidades do blog (geralmente utilizada na homepage ou em arquivos de categorias).

Utiliza o layout dinâmico **Destaque + Grade**:
1. **Destaque Principal (Full Width):** O primeiro artigo recebido no array de notícias (`$noticias[0]`) é destacado horizontalmente em tamanho gigante (`card-noticia--hero`), dividindo a capa (60% de largura) e os dados textuais (40%) no desktop.
2. **Grade Secundária (3 Colunas):** Os artigos seguintes são distribuídos abaixo do destaque em um grid CSS responsivo uniforme de 3 colunas, usando a variação vertical clássica (`card-noticia--grid`).
3. **Botão Ver Mais:** Um botão secundário centralizado no rodapé da seção, renderizado se um link amigável `$ver_mais_url` for fornecido.

---

## Parâmetros PHP

| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `titulo` | `string` | `'Notícias Recentes'` | Título do `<h2>` principal da seção |
| `noticias` | `array` | `[]` | Array plano de notícias (cada item com as propriedades aceitas pelo `card-noticia`) |
| `ver_mais_url` | `string` | `''` | Link do botão "Ver mais notícias" (se vazio, oculta o botão) |

### Propriedades esperadas em cada item do array `noticias`

| Chave | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `titulo` | `string` | ✅ Sim | Título do artigo |
| `url` | `string` | ✅ Sim | Link do artigo |
| `imagem_url` | `string` | ✅ Sim | URL da imagem de capa widescreen (proporção 16:9) |
| `categoria` | `string` | ❌ Não | Nome da categoria / badge de eyebrow |
| `autor` | `string` | ❌ Não | Nome do redator do artigo |
| `data` | `string` | ❌ Não | Data amigável da publicação (ex: `"há 2 horas"`) |
| `resumo` | `string` | ❌ Não | Texto auxiliar (excerpt) do artigo |

---

## Responsividade

- **Desktop (≥ 64rem):** O Destaque exibe em formato horizontal dividido. O Grid exibe 3 colunas de notícias com espaçamento de `var(--space-600)`.
- **Tablet (≥ 48rem):** O Destaque se comporta de forma flex-row ou vertical. O Grid exibe 2 colunas.
- **Mobile (< 48rem):** Tanto o Destaque quanto o Grid colapsam de forma fluida para 1 coluna vertical simples.

---

## Variáveis CSS utilizadas

- `--container-max`, `--space-200` a `--space-700`
- `--font-heading`
- `--text-sm-size`, `--text-md-sm-size`
- `--neutral-100`

---

## Exemplo de uso

```php
mm_render_component( 'organisms', 'secao-noticias-recentes', array(
    'titulo'       => 'Últimas Notícias',
    'ver_mais_url' => home_url( '/noticias/' ),
    'noticias'     => array(
        array(
            'titulo'     => 'Solo Leveling: Episódio final quebra recordes absolutos de audiência mundial',
            'url'        => '#',
            'imagem_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=800&q=80',
            'categoria'  => 'Anime',
            'autor'      => 'Pablo Augusto',
            'data'       => 'há 2 horas',
            'resumo'     => 'O aclamado anime encerra seu primeiro arco consagrando-se como um dos maiores fenômenos mundiais de streaming do ano.'
        ),
        array(
            'titulo'     => 'Hunter x Hunter retorna com capítulos inéditos na Shonen Jump na próxima semana',
            'url'        => '#',
            'imagem_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500&q=80',
            'categoria'  => 'Mangá',
            'autor'      => 'Redação',
            'data'       => 'há 5 horas'
        ),
        array(
            'titulo'     => 'Demon Slayer: Treinamento dos Hashiras recebe data de estreia com dublagem confirmada',
            'url'        => '#',
            'imagem_url' => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=500&q=80',
            'categoria'  => 'Anime',
            'autor'      => 'Redação',
            'data'       => 'há 1 dia'
        ),
        array(
            'titulo'     => 'My Hero Academia revela teaser exclusivo e design de novo vilão do filme',
            'url'        => '#',
            'imagem_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=500&q=80',
            'categoria'  => 'Filmes',
            'autor'      => 'Pablo Augusto',
            'data'       => 'há 2 dias'
        ),
    ),
) );
```
