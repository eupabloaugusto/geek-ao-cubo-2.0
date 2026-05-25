# Molécula: Card de Notícia (`card-noticia`)

O **Card de Notícia** é uma molécula do design system projetada especificamente para feeds de notícias, reviews de episódios, recomendações e artigos em destaque na homepage. É fortemente inspirada no visual moderno e focado em mídia da Crunchyroll News, perfeitamente alinhada aos design tokens de alta fidelidade do portal Geek ao Cubo.

---

## 📂 Estrutura de Arquivos

* **Componente (PHP):** [`molecules/card-noticia.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/card-noticia.php)
* **Estilos (CSS):** [`molecules/card-noticia.css`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/card-noticia.css)
* **Visual Vitrine:** [`storybook.html#card-noticia-preview`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/storybook.html#card-noticia-preview)

---

## ⚙️ Parâmetros do Componente (PHP)

Ao utilizar a função `mm_render_component('molecules', 'card-noticia', $args)`, você pode configurar os seguintes parâmetros:

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
| :--- | :--- | :--- | :--- | :--- |
| `titulo` | `string` | **Sim** | *Vazio* | Título principal do artigo/notícia (limita-se a 2-3 linhas via CSS). |
| `url` | `string` | **Sim** | `#` | URL de redirecionamento para a página cheia do artigo. |
| `imagem_url` | `string` | **Sim** | *Vazio* | Link da imagem de capa (widescreen horizontal). |
| `categoria` | `string` | Não | `Geral` | Badge superior (eyebrow) em caixa alta (ex: "ANIME", "NOVIDADES"). |
| `autor` | `string` | Não | `Redação` | Autor do artigo, prefixado automaticamente por "por". |
| `data` | `string` | Não | *Vazio* | Data ou tempo de publicação amigável (ex: "há 2 horas"). |
| `resumo` | `string` | Não | *Vazio* | Texto auxiliar de introdução/excerpt (limita-se a 2 linhas). |
| `variacao` | `string` | Não | `grid` | Seletor de layout: `'grid'` (vertical), `'list'` (horizontal compacto), `'hero'` (destaque horizontal gigante) ou `'hero-vertical'` (destaque vertical de alto impacto). |

---

## 🛠️ Padrões BEM (Borda, Elemento, Modificador)

O CSS é estruturado rigorosamente sob a convenção BEM, permitindo alta isolação de escopo:

* `.card-noticia`: Bloco principal encapsulador (tag `<a>`).
  * `.card-noticia--grid` [Modificador]: Versão vertical compactada e elegante da variação de Destaque.
  * `.card-noticia--list` [Modificador]: Versão horizontal compactada com imagem à esquerda e informações centralizadas verticalmente à direita. Colapsa para layout vertical em celulares (`max-width: 768px`).
  * `.card-noticia--hero` [Modificador]: Destaque Principal Horizontal Gigante com split lateral (imagem 60% e conteúdo 40%). Colapsa para vertical em tablets e celulares (`max-width: 992px`).
  * `.card-noticia--hero-vertical` [Modificador]: Destaque Principal Vertical de altíssimo impacto (imagem 100% no topo, conteúdo 100% abaixo), ideal para colunas independentes e para a Seção de Destaque. Colapsa de forma fluida em tablets e celulares (`max-width: 992px`).
  * `.card-noticia__media-wrapper`: Container da imagem de capa widescreen. Força proporção rígida de `16/9` e impede vazamentos (`overflow: hidden`).
  * `.card-noticia__image`: Elemento `<img>` que possui efeito zoom suave no hover (`scale(1.04)`).
  * `.card-noticia__content`: Container interno do conteúdo textual que usa um espaçamento flexbox unificado (`gap: var(--space-300)` global, otimizado para `var(--space-200)` na variação Grid) para garantir alinhamento harmônico de suas informações. Na variação Grid, o rodapé de metadados remove `margin-top: auto` para manter todo o bloco agrupado e centralizado no meio da div.
  * `.card-noticia__eyebrow`: Badge superior laranja (`var(--color-primary)`) em negrito e caixa alta.
  * `.card-noticia__title`: Título em negrito e fonte heading (`var(--font-heading)`) que muda para a cor primária laranja no hover do card.
  * `.card-noticia__excerpt`: Parágrafo de descrição cinza com contraste controlado.
  * `.card-noticia__meta`: Rodapé de metadados ("por Autor • Data").

---

## ⚡ Exemplo Prático de Integração no Código

### 1. Variação Grid (Padrão para Feeds Principais)
```php
mm_render_component('molecules', 'card-noticia', [
    'titulo'      => 'Solo Leveling: Episódio final quebra recordes absolutos de audiência',
    'url'         => get_permalink(),
    'imagem_url'  => get_the_post_thumbnail_url(null, 'medium_large'),
    'categoria'   => 'Anime',
    'resumo'      => 'O aclamado anime encerra seu primeiro arco consagrando-se como um dos maiores fenômenos mundiais do ano.'
]);
```

### 2. Variação List (Compacto para Sidebar/Widgets de Recomendação)
```php
mm_render_component('molecules', 'card-noticia', [
    'titulo'      => 'Hunter x Hunter retorna com capítulos inéditos na Shonen Jump',
    'url'         => get_permalink(),
    'imagem_url'  => get_the_post_thumbnail_url(null, 'thumbnail'),
    'categoria'   => 'Mangá',
    'variacao'    => 'list'
]);
```

### 3. Variação Hero Horizontal (Destaque Principal com Split)
```php
mm_render_component('molecules', 'card-noticia', [
    'titulo'      => 'Demon Slayer: Arco do Treinamento dos Hashira ganha data oficial de estreia no Brasil',
    'url'         => get_permalink(),
    'imagem_url'  => get_the_post_thumbnail_url(null, 'full'),
    'categoria'   => 'Destaque',
    'resumo'      => 'Os caçadores de demônios entram na fase final de preparação. A Crunchyroll confirmou a exibição exclusiva com transmissão diária em áudio original e dublagens simultâneas.',
    'variacao'    => 'hero'
]);
```

### 4. Variação Hero Vertical (Destaque Principal em Coluna)
```php
mm_render_component('molecules', 'card-noticia', [
    'titulo'      => 'Demon Slayer: Arco do Treinamento dos Hashira ganha data oficial de estreia no Brasil',
    'url'         => get_permalink(),
    'imagem_url'  => get_the_post_thumbnail_url(null, 'full'),
    'categoria'   => 'Destaque',
    'resumo'      => 'Os caçadores de demônios entram na fase final de preparação. A Crunchyroll confirmou a exibição exclusiva com transmissão diária em áudio original e dublagens simultâneas.',
    'variacao'    => 'hero-vertical'
]);
```

---

## ♿ Acessibilidade (WCAG) & SEO Estratégico

* **Navegação Semântica:** O card é estruturado sob uma tag `<a>` que encapsula o conteúdo de mídia e textual, gerando um único ponto focal robusto para leitores de tela.
* **ARIA-Label Descritivo:** A tag `<a>` principal calcula automaticamente o atributo `aria-label` incluindo o título do artigo (`aria-label="Leia mais sobre: [Título]"`), impedindo redundâncias sonoras ou leituras incompletas do fluxo visual.
* **Capa Alt Automatizado:** A imagem de capa é injetada com `alt="Capa de: [Título]"` de forma automática, garantindo conformidade total sem dependência de inserções manuais falhas no painel de controle.
* **CLS Protegido (Cumulative Layout Shift):** O container `.card-noticia__media-wrapper` estabelece a proporção widescreen usando `aspect-ratio: 16 / 9`, garantindo que o espaço na tela seja reservado antes do carregamento completo da imagem da rede, blindando os Core Web Vitals do blog.
