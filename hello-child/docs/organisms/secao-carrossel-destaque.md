# Seção de Carrossel de Destaques (`secao-carrossel-destaque`)

**Tipo:** Organismo  
**Arquivo:** [`organisms/secao-carrossel-destaque.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-carrossel-destaque.php)  
**CSS:** [`organisms/secao-carrossel-destaque.css`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-carrossel-destaque.css)  
**JS:** [`organisms/secao-carrossel-destaque.js`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-carrossel-destaque.js)  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

---

## 📂 Descrição

A **Seção de Carrossel de Destaques** (`secao-carrossel-destaque`) é o segundo organismo nobre de manchetes do portal Geek ao Cubo. Ela agrupa até **4 posts em Variação Destaque Horizontal (Hero)** rotacionando horizontalmente de forma extremamente fluida e interativa.

Seu diferencial técnico reside na adoção das melhores práticas de performance (Core Web Vitals >= 95) e acessibilidade (WCAG):
1. **Scroll Snap Nativo:** A rolagem de slides é governada puramente pelo navegador (`scroll-snap-type: x mandatory`). Isso delega a renderização para a GPU, resultando em taxa de quadros estável (60 FPS), suporte nativo a gestos touch/swipe em celulares e CLS nulo (zero layout shift).
2. **Scroll-Spy Integrado:** Um ouvinte JS monitora ativamente o trilho de rolagem física. Ao deslizar com o dedo no celular, o bolinha (dot) correspondente se ativa de forma 100% precisa.
3. **Autoplay Acessível:** O carrossel rotaciona automaticamente a cada 5 segundos, mas é pausado de forma instantânea quando o cursor do mouse passa por cima (hover) ou quando o usuário navega com a tecla `Tab` (foco), garantindo que leitores de tela e usuários de teclado tenham controle absoluto sobre o conteúdo.

---

## 🛠️ Componentes Utilizados

- **Átomos:**
  - [`atoms/carousel-dot.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/atoms/carousel-dot.php) (Pílulas indicadoras)
  - [`atoms/btn-nav-arrow.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/atoms/btn-nav-arrow.php) (Setas direcionais com micro-deslocamento)
- **Moléculas:**
  - [`molecules/carousel-nav.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/carousel-nav.php) (Dock unificado de controle)
- **Moléculas Auxiliares:**
  - [`molecules/card-noticia.php`](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/card-noticia.php) (Forçado no modificador `hero`)

---

## ⚙️ Parâmetros PHP

Ao invocar `mm_render_component( 'organisms', 'secao-carrossel-destaque', $args )`, passe as seguintes configurações:

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
| :--- | :--- | :--- | :--- | :--- |
| `$args['posts_carousel']` | `array` | **Sim** | *Vazio* | Matriz contendo até 4 vetores de dados de posts. Caso esteja vazio, a renderização é interrompida. |

---

## ⚡ Exemplo Prático de Uso

```php
<?php
// Mapeia os dados dos posts (loops ou WP_Query)
$posts_carrossel = array(
    array(
        'titulo'     => 'Demon Slayer: Arco do Treinamento dos Hashira ganha data oficial de estreia',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=1000&q=80',
        'categoria'  => 'Destaque',
        'resumo'     => 'Os caçadores de demônios entram na fase final de preparação. A Crunchyroll confirmou a exibição exclusiva com dublagens simultâneas.'
    ),
    array(
        'titulo'     => 'Solo Leveling: Episódio final quebra recordes de audiência mundial',
        'url'         => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=1000&q=80',
        'categoria'  => 'Anime',
        'resumo'     => 'O aclamado anime encerra seu primeiro arco consagrando-se como um dos maiores fenômenos mundiais do ano.'
    ),
    array(
        'titulo'     => 'Hunter x Hunter retorna com capítulos inéditos na revista Shonen Jump',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=1000&q=80',
        'categoria'  => 'Mangá',
        'resumo'     => 'Após um longo hiato, Yoshihiro Togashi retoma a publicação da obra mais aguardada pelos fãs de shonen.'
    ),
    array(
        'titulo'     => 'Jujutsu Kaisen: Terceira Temporada ganha teaser oficial eletrizante',
        'url'        => '#',
        'imagem_url' => 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=1000&q=80',
        'categoria'  => 'Novidades',
        'resumo'     => 'O aclamado arco do Jogo do Abate é oficialmente confirmado com animação da MAPPA.'
    )
);

// Renderiza o Carrossel Nobre de Manchetes na Home
mm_render_component( 'organisms', 'secao-carrossel-destaque', array(
    'posts_carousel' => $posts_carrossel
) );
?>
```

---

## ♿ SEO & Acessibilidade (WCAG)

* **Referenciais Semânticos:** Uso da tag `<section>` dotada de um rótulo semântico `aria-label="Carrossel de Notícias em Destaque"`.
* **Teclado & Foco:** Setas direcionais e bolinhas indicadoras utilizam elementos `<button>` nativos com `aria-label` descritivos ("Ir para o slide 1", "Próximo slide").
* **Indicativos de Estado:** O dot selecionado atualiza dinamicamente as tags de estado de leitor de tela `aria-current="true"` e a classe reativa `.is-active`.
* **Core Web Vitals Protegido (CLS Zero):** O container do trilho reserva a proporção física widescreen na tela antes das imagens carregarem, impedindo qualquer oscilação indesejada.
