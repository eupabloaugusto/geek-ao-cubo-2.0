# Seção de Destaque Home (`secao-destaque`)

**Tipo:** Organismo  
**Arquivo:** [`organisms/secao-destaque.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-destaque.php)  
**CSS:** [`organisms/secao-destaque.css`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-destaque.css)  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

---

## 📂 Descrição

A **Seção de Destaque** (`secao-destaque`) é o organismo principal de manchetes localizado no topo da homepage do blog Geek ao Cubo. Sua estrutura adota uma proporção áurea moderna de 60/40 no desktop:

- **Lado Esquerdo (60%):** Apresenta o post principal nobre em um **Card Destaque Vertical** (`card-noticia--hero-vertical`). Ele exibe uma imagem widescreen exuberante com a tipografia de manchete e metadados posicionados abaixo, esticando-se perfeitamente para preencher o espaço vertical e alinhar o rodapé na base via `margin-top: auto`.
- **Lado Direito (40%):** Apresenta um stack de **3 Cards em Variação Lista** (`card-noticia--list`) empilhados de forma vertical com vão uniforme. Cada card de lista exibe uma mídia horizontal pequena à esquerda e textos centralizados verticalmente à direita.

### 🔄 Design para Rotação / Carrossel Interativo

O organismo foi estruturado com total modularidade CSS BEM pensando na expansibilidade para um carrossel rotativo interativo:
1. Como os cards compartilham a mesma estrutura atômica (`card-noticia`), qualquer um dos cards da lista lateral direita pode assumir a posição de Destaque à esquerda.
2. A transição visual é governada puramente por classes CSS. O card que se move para a esquerda ganha o modificador `.card-noticia--hero-vertical` (e o layout de coluna vertical gigante), enquanto os demais assumem o modificador de row horizontal compacta `.card-noticia--list`.

---

## 🛠️ Componentes Utilizados

- **Moléculas:**
  - [`molecules/card-noticia.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/card-noticia.php) (Variante `hero-vertical` no Destaque e `list` na lateral direita).

---

## ⚙️ Parâmetros PHP

Ao invocar `mm_render_component( 'organisms', 'secao-destaque', $args )`, passe as seguintes configurações:

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
| :--- | :--- | :--- | :--- | :--- |
| `$args['post_hero']` | `array` | **Sim** | *Vazio* | Vetor contendo os dados do post em Destaque. Caso esteja vazio, o componente aborta a renderização. |
| `$args['posts_sidebar']` | `array` | Não | `[]` | Matriz contendo até 3 vetores de dados para os posts secundários na lateral direita. |

---

## ⚡ Exemplo Prático de Uso

```php
<?php
// Mapeamento dos loops ou WP_Query no seu template (ex: front-page.php)
$post_hero = array(
    'titulo'     => 'Solo Leveling: Episódio final quebra recordes absolutos de audiência mundial',
    'url'        => 'https://modomaratona.com/anime/solo-leveling-final-records',
    'imagem_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=1000&q=80',
    'categoria'  => 'Destaque',
    'autor'      => 'Redação',
    'data'       => 'há 1 hora',
    'resumo'     => 'O aclamado anime encerra seu primeiro arco consagrando-se como um dos maiores fenômenos mundiais do ano.'
);

$posts_sidebar = array(
    array(
        'titulo'     => 'Hunter x Hunter retorna com capítulos inéditos na Shonen Jump',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=600&q=80',
        'categoria'  => 'Lista Horizontal',
        'data'       => 'há 6 horas'
    ),
    array(
        'titulo'     => 'Jujutsu Kaisen: Terceira Temporada ganha teaser oficial eletrizante',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=600&q=80',
        'categoria'  => 'Widget Lateral',
        'data'       => 'há 12 horas'
    ),
    array(
        'titulo'     => 'Demon Slayer: Arco do Treinamento dos Hashira ganha data oficial de estreia',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&q=80',
        'categoria'  => 'Recomendações',
        'data'       => 'há 1 dia'
    )
);

// Renderiza a Seção de Destaque nobre na homepage
mm_render_component( 'organisms', 'secao-destaque', array(
    'post_hero'     => $post_hero,
    'posts_sidebar' => $posts_sidebar
) );
?>
```

---

## ♿ SEO Estratégico & Acessibilidade (WCAG)

* **Semântica HTML5 Nativa:** Encapsulado em um bloco `<section>` contendo a marcação de acessibilidade `aria-label="Notícias em Destaque"` de modo a fornecer um ponto de referência claro na árvore de acessibilidade do navegador.
* **Leitura Unificada:** Cada card atua como uma única tag de âncora `<a>` com `aria-label` próprio dinâmico, poupando usuários de leitores de tela de cliques repetidos em mídias, títulos ou badges isolados.
* **Core Web Vitals Protegido (Prevenção de CLS):** O grid define alturas naturais no desktop sob as proporções físicas de mídia, garantindo zero oscilações de layout Shift na renderização progressiva de imagens da rede.

---

## 📱 Comportamento Responsivo

* **Desktop (> 1024px):** Layout grid horizontal áureo `1.6fr 1fr` (60% / 40%) com vão livre premium de `32px` (`var(--space-500)`). Os 3 cards de lista se alinham simetricamente com a base e o topo do Hero vertical.
* **Tablets (768px - 1024px):** A grade de duas colunas colapsa de forma limpa para **coluna vertical única**, mantendo o Hero nobre no topo com largura completa e a pilha de listas logo abaixo, preservando proporções perfeitas sem deformidades físicas.
* **Smartphones (< 768px):** A stack lateral colapsa os cards de lista (`card-noticia--list`) de formato horizontal (row) para o formato vertical (column) clássico de grids, eliminando compressões esmagadoras nas fotos e facilitando o toque em touch-screen móvel.
