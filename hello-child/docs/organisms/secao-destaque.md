# Seção de Destaque Home (`secao-destaque`)

**Tipo:** Organismo  
**Arquivo:** [`organisms/secao-destaque.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-destaque.php)  
**CSS:** [`organisms/secao-destaque.css`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-destaque.css)  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-26  

---

## 📂 Descrição

A **Seção de Destaque** (`secao-destaque`) é o organismo principal de manchetes localizado no topo da homepage do blog Geek ao Cubo. Sua estrutura adota um layout balanceado e moderno no desktop:

- **Lado Direito (60%):** Apresenta o post principal nobre em um **Card Destaque Vertical** (`card-noticia--hero-vertical`). Ele exibe uma imagem widescreen exuberante com a tipografia de manchete e metadados posicionados abaixo, esticando-se perfeitamente para preencher o espaço vertical e alinhar o rodapé na base via `margin-top: auto`.
- **Lado Esquerdo (40%):** Apresenta uma grade de duas colunas com 2 cards em cada (totalizando **4 Cards em Variação Grid** - `card-noticia--grid`). Cada card de grid exibe uma mídia widescreen compacta no topo e textos bem distribuídos abaixo.


### ♿ SEO Estratégico & Ordenação Acessível (A11y/WCAG)

Pensando em **acessibilidade** e em **desempenho móvel (Mobile-First)**, o componente segue um padrão inteligente:
1. No HTML DOM, o **Card Principal de Destaque** (`secao-destaque__main`) é declarado **primeiro**.
2. Em smartphones e leitores de tela, o destaque principal é lido e exibido naturalmente no topo, com a grade de posts secundários aparecendo logo abaixo.
3. Em desktops (`min-width: 64rem`), o CSS Grid inverte visualmente as posições usando posicionamento explícito de colunas (`grid-column: 2` para o Destaque e `grid-column: 1` para a Grade), mantendo o markup acessível e semanticamente impecável.

---

## 🛠️ Componentes Utilizados

- **Moléculas:**
  - [`molecules/card-noticia.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/card-noticia.php) (Variante `hero-vertical` no Destaque e `grid` na lateral esquerda).

---

## ⚙️ Parâmetros PHP

Ao invocar `mm_render_component( 'organisms', 'secao-destaque', $args )`, passe as seguintes configurações:

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
| :--- | :--- | :--- | :--- | :--- |
| `$args['post_hero']` | `array` | **Sim** | *Vazio* | Vetor contendo os dados do post principal em Destaque. Caso esteja vazio, o componente aborta a renderização. |
| `$args['posts_grid']` | `array` | Não | `[]` | Matriz contendo até 4 vetores de dados para os posts em formato Grid na lateral esquerda. |
| `$args['posts_sidebar']` | `array` | Não | `[]` | Fallback retrocompatível para `$posts_grid` caso o componente seja invocado de forma antiga. |

---

## ⚡ Exemplo Prático de Uso

```php
<?php
// Mapeamento dos loops ou WP_Query no seu template (ex: front-page.php)
$post_hero = array(
    'titulo'     => 'Demon Slayer: Filme Trilogia do Castelo Infinito ganha nova arte e confirma estreia mundial para 2026',
    'url'        => 'https://modomaratona.com/anime/demon-slayer-castelo-infinito',
    'imagem_url' => 'https://images.unsplash.com/photo-1580477667995-2b94f01c9516?w=1000&q=80',
    'categoria'  => 'Destaque',
    'autor'      => 'Redação',
    'data'       => 'há 1 hora',
    'resumo'     => 'A Ufotable impressionou fãs com um pôster oficial focado no confronto épico e final contra Muzan Kibutsuji dentro do Castelo Infinito.'
);

$posts_grid = array(
    array(
        'titulo'     => 'Solo Leveling: Episódio final quebra recordes absolutos de audiência',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&q=80',
        'categoria'  => 'Anime',
        'data'       => 'há 3 horas'
    ),
    array(
        'titulo'     => 'Hunter x Hunter retorna com capítulos inéditos na Shonen Jump',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=600&q=80',
        'categoria'  => 'Mangá',
        'data'       => 'há 6 horas'
    ),
    array(
        'titulo'     => 'Chainsaw Man: Filme do Arco de Reze tem trailer de tirar o fôlego divulgado',
        'url'         => '#',
        'imagem_url'  => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&q=80',
        'categoria'   => 'Novidades',
        'data'        => 'há 12 horas'
    ),
    array(
        'titulo'     => 'Jujutsu Kaisen: Terceira Temporada ganha teaser eletrizante do Jogo do Abate',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=600&q=80',
        'categoria'  => 'Destaque',
        'data'       => 'há 1 dia'
    )
);

// Renderiza a Seção de Destaque nobre na homepage
mm_render_component( 'organisms', 'secao-destaque', array(
    'post_hero'  => $post_hero,
    'posts_grid' => $posts_grid
) );
?>
```

---

## ♿ Acessibilidade (WCAG)

* **Semântica HTML5 Nativa:** Encapsulado em um bloco `<section>` contendo a marcação de acessibilidade `aria-label="Notícias em Destaque"` de modo a fornecer um ponto de referência claro na árvore de acessibilidade do navegador.
* **Leitura Unificada:** Cada card atua como uma única tag de âncora `<a>` com `aria-label` próprio dinâmico, poupando usuários de leitores de tela de cliques repetidos.
* **Core Web Vitals Protegido (Prevenção de CLS):** O grid define alturas naturais no desktop sob as proporções físicas de mídia, garantindo zero oscilações de layout Shift na renderização progressiva de imagens.

---

## 📱 Comportamento Responsivo

* **Desktop (> 1024px):** Layout grid horizontal `2fr 3fr` (40% / 60%) com vão de `32px` (`var(--space-500)`). A grade esquerda 2x2 com cards Grid (`card-noticia--grid`) se alinha simetricamente com a base e o topo do Hero vertical direito (resultando na proporção clássica de 3fr para o destaque e 1fr + 1fr para a grade de cards).

* **Tablets (640px - 1024px):** O layout de duas colunas colapsa para **coluna única**, colocando o Hero no topo e a grade de cards Grid logo abaixo. A grade interna de cards Grid **mantém** as 2 colunas horizontais, garantindo ótimo aproveitamento de tela.
* **Smartphones (< 640px):** A grade interna colapsa para **1 única coluna vertical**, empilhando todos os cards individualmente, evitando qualquer compressão física das fotos widescreen e facilitando o toque em telas mobile.
