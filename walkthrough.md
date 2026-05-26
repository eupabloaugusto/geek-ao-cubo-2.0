# Walkthrough — Fundação, Componentes e Correções Globais da Página Inicial (v3.12.0)

Este documento apresenta a entrega das correções estruturais e otimizações de infraestrutura aplicadas na página inicial (**front-page.php**) e na casca global do tema filho **Geek ao Cubo**, eliminando todas as desconfigurações visuais e consolidando a estética premium do portal.

---

## 1. O que foi construído e corrigido

Solucionamos os problemas crônicos que estavam quebrando o layout da homepage e redefinimos o comportamento global do portal:

### A. Casca Global do Tema (Override de Cabeçalho e Rodapé)
- **[NEW] [header.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/header.php):** Criado o arquivo de cabeçalho na raiz do tema filho para sobrescrever a casca seca do parent theme. Ele agora injeta automaticamente a marcação HTML5 semântica e carrega o organismo premium `organisms/header` (com suporte a glassmorphism e logo SVG inline). Adicionado também a div `.header-spacer` para empurrar o conteúdo de forma responsiva.
- **[NEW] [footer.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/footer.php):** Criado o arquivo de rodapé na raiz do tema filho para carregar dinamicamente o organismo premium `organisms/footer` e chamar `wp_footer()` de forma a registrar scripts e dependências do WordPress com segurança.

### B. Ativação Global do Modo Escuro (Dark Mode)
- **[MODIFY] [style.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/style.css):** Atualizado o arquivo de identificação do tema filho com as propriedades de reset global do corpo (`body`). Aplicamos `background-color: var(--color-background) !important` e `color: var(--color-text) !important`, neutralizando por completo o fundo branco forçado pelo parent theme `hello-elementor` e garantindo o modo escuro unificado em todas as páginas do portal!

### C. Otimização de Performance e Prevenção de FOUC
- **[MODIFY] [functions.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/functions.php):** Migrado o enfileiramento de `front-page.css` de dentro do template físico para a chamada oficial na action `wp_enqueue_scripts` (usando a verificação condicional `is_front_page()`). Isso evita o fenômeno de Flash of Unstyled Content (FOUC) e carrega os estilos no cabeçalho.
- **[MODIFY] [front-page.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/front-page.php):** Removido a chamada inline de `wp_enqueue_style` mantendo o template 100% seco (DRY).

### D. Refinamento de Componentes (Single-slide Carousel Nav)
- **[MODIFY] [secao-carrossel-destaque.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-carrossel-destaque.php):** Adicionada a lógica condicional no PHP para **ocultar** os botões de navegação (setas e dots indicadores) caso haja apenas 1 post no carrossel de destaques (como o post padrão "Hello world!" inicial), otimizando a usabilidade e a estética do topo.

### E. Primeira Seção da Homepage: Carrossel de Destaques
- **[MODIFY] [front-page.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/front-page.php):** Injetamos a seção `secao-carrossel-destaque` como a primeira e principal área de conteúdo da página inicial. Ela executa dinamicamente a consulta de destaques editoriais (`mm_query_posts_destaque()`) e renderiza os cards correspondentes ou exibe os fallbacks caso não existam artigos locais.
- **[MODIFY] [footer.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/footer.php):** Restauramos a renderização incondicional do organismo de rodapé (`organisms/footer`), assegurando a estrutura semântica padrão em todas as páginas, já que a homepage agora conta com conteúdo interativo de destaque.

### F. Correção Crítica da Interface (Molécula `card-noticia.php`)
- **[MODIFY] [card-noticia.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/card-noticia.php):** Revertemos a refatoração excessiva aplicada anteriormente no card de notícias. O componente voltou a usar as classes nativas do design system e Storybook (`card-noticia__eyebrow`, `card-noticia__author`, `card-noticia__date`, etc.) e seus respectivos SVGs inline de alta definição. Isso corrigiu a ausência total de textos à direita nas manchetes hero do carrossel, restaurando com perfeição absoluta a diagramação planejada no Figma e Storybook.

### G. Diretriz Anti-Quebra Visiva (`preserve-design-structure.md`)
- **[NEW] [preserve-design-structure.md](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/docs/preserve-design-structure.md):** Criamos um manual técnico rígido de conduta para desenvolvimento e IA. Esse documento bloqueia de forma definitiva modificações cegas que alterem a assinatura de classes HTML BEM e tags homologadas no Storybook de componentes estáveis, estabelecendo diretrizes claras sobre o acoplamento estrito entre folhas de estilo CSS e marcações PHP para preservar a integridade total do design premium.
- **[MODIFY] [README.md](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/docs/README.md):** Vinculamos a nova diretriz de preservação na documentação principal do projeto sob a seção 5.

### H. Reformulação Completa da Sidebar (`sidebar.php` / `sidebar.css`)
- **[MODIFY] [sidebar.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/sidebar.php):** Removemos por completo os widgets estáticos de caixa de pesquisa e destaques da temporada. Substituímos esses blocos por um widget moderno e dinâmico (`sidebar-widget-clean`) que lista 4 cards de notícias em Variação Lista Horizontal (`card-noticia--list`). O widget executa dinamicamente a consulta centralizada de artigos recentes (`mm_query_noticias_recentes()`).
- **[MODIFY] [sidebar.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/sidebar.css):** Adicionamos a classe `.sidebar-widget-clean` com propriedades flex transparentes para conter os cards de lista que já possuem fundo e bordas próprias, impedindo o efeito indesejado de bordas duplas e mantendo a leveza visual.

### I. Reconfiguração da Seção de Destaques (`secao-destaque` Organism & Styles)
- **[MODIFY] [secao-destaque.php](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-destaque.php):** Modificada a ordem do HTML DOM para declarar o card principal de destaque (`secao-destaque__main`) **primeiro**. Isto garante que, em layouts móveis ou leitores de tela (A11y/WCAG), o Destaque Principal apareça no topo de forma natural. Atualizamos a assinatura para receber `$posts_grid` (grade de 4 cards) com fallback dinâmico para retrocompatibilidade com `$posts_sidebar`.
- **[MODIFY] [secao-destaque.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-destaque.css):** Total reestruturação com CSS Grid no desktop (`min-width: 64rem`). O Destaque Principal é posicionado no lado direito (`grid-column: 2; height: 100%`) ocupando 60% da tela (3fr) e criamos uma grade `.secao-destaque__grid` no lado esquerdo (`grid-column: 1; grid-row: 1`) ocupando 40% da tela para organizar 4 cards em Variação Grid (`card-noticia--grid`) em duas colunas de 1fr cada (proporção final clássica `1fr 1fr 3fr`).
- **[MODIFY] [storybook.html](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/storybook.html):** Atualizada a vitrine estática sob o identificador `#secao-destaque-preview` para exibir com perfeição absoluta o novo design: 1 card Hero vertical no lado direito (60% / 3fr) e a grade 2x2 de 4 cards Grid no lado esquerdo (40% / 1fr + 1fr).
- **[MODIFY] [secao-destaque.md](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/docs/organisms/secao-destaque.md):** Atualizada toda a documentação do organismo detalhando a nova especificação de proporções `1fr 1fr 3fr`, parâmetros PHP, regras semânticas WCAG e comportamento fluido de colapso responsivo.
### J. Ajustes Globais de Espaçamento e Correção de Corte dos Cards/Setas (Fase 4 - Layout & Gaps)
- **[MODIFY] [trilho-infinito.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/trilho-infinito.css):** Adicionada máscara com degradê horizontal lateral (`::before`/`::after`) no trilho de rolagem para suavizar o corte de cards nas bordas. Mapeadas as setas de navegação de `-1rem` para `0` (garantindo que fiquem nos limites do trilho) e ajustado o `z-index` para ficarem acima do degradê.
- **[MODIFY] [secao-esteira-animes.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-esteira-animes.css):** Removido o `overflow: hidden` do container da seção para evitar corte de setas, sombras e efeitos no hover dos cards. Removido também `margin-bottom` residual.
- **[MODIFY] [secao-novos-episodios.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-novos-episodios.css):** Removido `overflow: hidden` da seção.
- **[MODIFY] [secao-recomendacoes.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-recomendacoes.css):** Removido `overflow: hidden` da seção.
- **[MODIFY] [secao-estatisticas.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-estatisticas.css):** Removido `overflow: hidden` da seção.
- **[MODIFY] [secao-carrossel-destaque.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/organisms/secao-carrossel-destaque.css):** Removida a propriedade `margin-bottom` para delegar o distanciamento ao container pai (`.home-page`) via `gap`.
- **[MODIFY] [front-page.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/front-page.css):** Adicionado `display: flex`, `flex-direction: column` e `gap` ao `.home-page` (resolvendo o espaçamento entre o Hero Carrossel e a grade principal de forma centralizada por gap). Removida a propriedade `overflow: hidden` de `.home-page__main`.

### K. Alinhamento Global do Design System (Design Tokens nos Templates Single e Componentes)
- **[MODIFY] [single-anime.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/single-anime.css):** Substituído o token não padronizado `--container-max-width` pelo token homologado `--container-max`.
- **[MODIFY] [single-temporada.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/single-temporada.css):** Substituídas as variáveis não padronizadas de tipografia (ex: `--font-family-headings`, `--font-size-sm`, `--font-size-md`, `--font-size-xl`), de container (`--container-max-width`) e de bordas (`--border-radius-lg`, `--border-radius-sm`) pelos respectivos tokens reais do Design System (`--font-heading`, `--text-xxs-size`, `--text-xs-size`, `--text-md-sm-size`, `--container-max`, `--border-radius-300`, `--border-radius-100`).
- **[MODIFY] [single-episodio.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/single-episodio.css):** Substituídos os tokens obsoletos de fontes, bordas e container pelos equivalentes oficiais do design-tokens (`--font-heading`, `--text-xs-size`, `--text-sm-size`, `--text-xxs-size`, `--border-radius-300`, `--border-radius-200`, `--container-max`).
- **[MODIFY] [single-review.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/single-review.css):** Corrigido o uso de variáveis inválidas como `--color-error`, `--color-success` e valores de fonte/borda herdados obsoletos. Mapeados agora para os tokens semânticos e de cores oficiais como `--error-500`, `--success-500`, `--color-primary-accessible` e a respectiva escala de textos/bordas.
- **[MODIFY] [aviso-adblock.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/atoms/aviso-adblock.css):** Limpeza de fallbacks incorretos de cores neutras (`#9CA3AF` e `#F3F4F6`), fallbacks manuais de margens e correção do mapeamento de `--text-md-size` para o tamanho real desejado (`--text-sm-size`).
- **[MODIFY] [banner-anuncio-editorial.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/atoms/banner-anuncio-editorial.css):** Substituído o valor hex `#0d0e11` pela variável global correspondente `--neutral-900` e a fonte customizada de 14px pela variável `--text-xxs-size`.
- **[MODIFY] [sidebar-assistir-agora.css](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/molecules/sidebar-assistir-agora.css):** Substituída a fonte estática de 14px pela variável `--text-xxs-size`.

---

## 2. O que foi Testado e Verificado

- **Validação do Design System nos Singles:** Todos os templates de postagem única (`single-anime`, `single-temporada`, `single-episodio`, `single-review`) renderizam agora sob as diretrizes estritas de fontes (`--font-heading`/`--font-body`), larguras de container máximas (`--container-max`) e escala de arredondamentos e tamanhos de fonte do design tokens.
- **Validação de Cores Semânticas de Alertas:** Os blocos de alerta (pros/contras e spoilers) utilizam as cores exatas correspondentes do design tokens (`--error-500` / `--success-500`), mantendo a acessibilidade de contraste visual e consistência de cores semânticas em todo o portal.
- **Validação de Modo Escuro Global:** Toda a homepage renderiza agora sob o fundo `#0D0E11` com fontes e textos em perfeita harmonia contrastante.
- **Validação de Override:** As chamadas de `get_header()` e `get_footer()` em todas as páginas carregam com perfeição o logotipo SVG otimizado e os menus dinâmicos.
- **Visualização do Carrossel de Destaque:** O carrossel de notícias principais renderiza perfeitamente logo abaixo do cabeçalho como a primeira seção da homepage, respeitando as margens e grid sem transbordar.
- **Exibição Correta do Rodapé:** O rodapé é exibido com sucesso no final da página inicial, completando a estrutura visual com perfeição.
- **Correção da Interface do Card Hero:** Validamos que todo o conteúdo textual do card de notícia (categoria, título de manchete, excerpt descritivo, autor e data) é impresso de forma correta e estilizada sobre a área direita do layout flex da variação hero.
- **Validação de Documentação:** Confirmamos que os guias e referências foram perfeitamente copilados e disponibilizados nas localizações designadas para consultas futuras.
- **Validação da Sidebar:** As manchetes de lista horizontal na barra lateral renderizam e fluem sem quebras, integrando posts dinamicamente com links e imagens de capa de forma excelente.
- **Validação Visual da Seção de Destaque:** Inspecionamos a nova estrutura no Storybook e validamos que o Destaque Principal vertical é renderizado no lado direito e as duas colunas com 2 cards Grid cada são dispostas na esquerda, com alinhamento perfeito na base e topo.
- **Validação Responsiva da Grade:** Confirmamos que, em resoluções tablet (entre 640px e 1024px), o destaque se posiciona no topo e a grade permanece em 2 colunas abaixo dele. Em smartphones (< 640px), toda a grade colapsa em 1 única coluna vertical de alta usabilidade e legibilidade de capas 16:9.
- **Validação de Setas e Degradês:** As setas de navegação do trilho horizontal (`trilho-infinito`) agora estão posicionadas de forma limpa nas bordas internas (em `left/right: 0`), sem serem recortadas em resoluções desktop e móvel. A transição dos cards nas extremidades do trilho está suavizada por um degradê horizontal dinâmico com a cor de fundo local.
- **Validação de Espaçamento por Gaps:** O espaçamento entre todas as seções principais da página inicial agora é unicamente controlado pela propriedade `gap` do container pai (`.home-page` e `.home-page__main`), sem o uso de `margin-bottom` ou `margin-top` redundantes nas seções individuais.

