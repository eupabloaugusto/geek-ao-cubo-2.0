# Changelog — modomaratona.com

Todas as alterações notáveis, adições e exclusões de componentes do projeto serão documentadas neste arquivo de forma contínua.

## [3.4.0] — 2026-05-25

### Adicionado
- **Organismo `secao-novos-episodios`** (`organisms/secao-novos-episodios.php` / `.css`): carrossel horizontal de `card-anime` com badge de horário (`badge-horario`) visível em cada capa. Título gerado dinamicamente pelo PHP via `date('w')` (ex: "Novos Episódios — Domingo"). Usa `trilho-infinito` para scroll infinito com setas. Documentação em `docs/organisms/secao-novos-episodios.md`.

---

## [3.3.0] — 2026-05-25

### Adicionado
- **Molécula `trilho-infinito`** (`molecules/trilho-infinito.php` / `.css` / `.js`): wrapper reutilizável de scroll horizontal infinito. Combina 2 átomos `btn-nav-arrow` (prev/next) com trilho scroll-snap. Scroll infinito via clonagem de slides, drag-to-scroll desktop e setas com `scrollBy` 75%. Documentação em `docs/molecules/trilho-infinito.md`.

### Modificado
- **`secao-esteira-animes`** — migrado para usar `trilho-infinito`; classes `js-esteira-*` substituídas por `js-trilho-*`; `secao-esteira-animes.js` esvaziado/deprecado.
- **`secao-recomendacoes`** — adicionado `trilho-infinito` (setas + scroll infinito). CSS de scroll/scrollbar removido (delegado à molécula).
- **`secao-estatisticas`** — adicionado `trilho-infinito` (setas + scroll infinito). CSS de scroll/scrollbar removido (delegado à molécula).

---

## [3.2.0] — 2026-05-25

### Adicionado
- **Organismo `secao-estatisticas`** (`organisms/secao-estatisticas.php` & `.css`): seção de estatísticas do anime com trilho horizontal scroll snap nativo, padrão idêntico ao `secao-esteira-animes`. Cada slide é um `stat-bloco` (score + rank + popularidade + membros). Slide `20rem` mobile → `24rem` desktop. Sem JS. Documentação em `docs/organisms/secao-estatisticas.md`.
- **`storybook.html`** — adicionado organismo `secao-estatisticas` na seção Organismos.

---

## [3.1.0] — 2026-05-25

### Adicionado
- **Organismo `secao-recomendacoes`** (`organisms/secao-recomendacoes.php` & `.css`): seção de animes recomendados em trilho horizontal com scroll snap nativo. Cabeçalho com H2 + link opcional "Ver todas". Cards `card-recomendacao` (poster 2:3 + contador + título). Sem JS. Documentação em `docs/organisms/secao-recomendacoes.md`.
- **`storybook.html`** — adicionado organismo `secao-recomendacoes` na seção Organismos.
- **Marco:** com este componente, o projeto atinge **76/76 componentes planejados ✅**.

---

## [3.0.0] — 2026-05-25

### Adicionado
- **Organismo `secao-staff`** (`organisms/secao-staff.php` & `.css`): seção de equipe de produção da página de detalhe do anime. Agrupa `card-staff` dinamicamente por cargo via campo `role_group`, com subtítulo H3 com barra laranja por grupo, limite configurável por grupo (`$max_per_group`, padrão 6) e botão "Ver equipe completa" opcional. Grade responsiva: 1 col mobile → 2 cols tablet → 3 cols desktop. Sem JS. Documentação em `docs/organisms/secao-staff.md`.
- **`storybook.html`** — adicionado organismo `secao-staff` na seção Organismos.

---

## [2.9.0] — 2026-05-25

### Adicionado
- **Organismo `secao-reviews`** (`organisms/secao-reviews.php` & `.css`): seção de avaliações de usuários para a página de detalhe do anime. Lista de `review-card` com cabeçalho (H2 + pill de contagem total), limite configurável via `$max_reviews` (padrão 6) e botão "Ver mais reviews" opcional. Desktop largo (≥ 75rem): grade de 2 colunas. Schema.org `ItemList`. Sem JS próprio. Documentação em `docs/organisms/secao-reviews.md`.
- **`storybook.html`** — adicionado organismo `secao-reviews` na seção Organismos.

---

## [2.8.0] — 2026-05-25

### Adicionado
- **Molécula `review-card`** (`molecules/review-card.php`, `.css`, `.js`): Card de avaliação de usuário para a seção de reviews da página de detalhe do anime. Exibe avatar circular do revisor (`avatar-personagem`), nome (clicável se `reviewer_url` fornecido), data, nota MAL (`nota-mal`) e texto da review com expand/collapse in-page via JS. Suporta link externo opcional para a review completa. Schema.org `Review` com `author`, `reviewRating` e `reviewBody`. Documentação em `docs/molecules/review-card.md`.
- **`storybook.html`** — adicionada molécula `review-card` na seção Moléculas com 3 variantes: card completo, card sem avatar e card sem nota.

---

## [2.7.0] — 2026-05-25

### Adicionado
- **Organismo `secao-noticias-recentes`** (`organisms/secao-noticias-recentes.php` & `.css`): layout editorial contendo o primeiro artigo destacado como card-noticia--hero ( horizontal split 60/40) e os artigos seguintes em uma grade de 3 colunas de card-noticia--grid. Botão opcional "Ver mais notícias" na base. Documentação em `docs/organisms/secao-noticias-recentes.md`.

---

## [2.6.0] — 2026-05-25

### Adicionado
- **Organismo `sidebar-anime-info`** (`organisms/sidebar-anime-info.php` & `.css`): barra lateral (sidebar) contendo poster 2:3, bloco integrado de estatísticas do MyAnimeList (stat-bloco) e listagem vertical detalhada de metadados. Documentação em `docs/organisms/sidebar-anime-info.md`.

---

## [2.5.0] — 2026-05-25

### Adicionado
- **Organismo `secao-relacionados`** (`organisms/secao-relacionados.php` & `.css`): exibe animes, mangás e mídias relacionadas agrupadas dinamicamente por seu tipo de relação (ex: Sequência, Prequel, Adaptação) em grades responsivas (1 col no mobile, 2 col no tablet, 3 col no desktop largo). Documentação em `docs/organisms/secao-relacionados.md`.

---

## [2.4.0] — 2026-05-25

### Alterado
- **Molécula `card-personagem`** (`molecules/card-personagem.php` & `.css`): restruturado layout do card. O nome e nome em Kanji agora ficam fora e abaixo do poster. O badge de papel (Principal/Secundário) continua sobreposto na imagem (dentro do poster). A elevação 3D, sombras e bordas do hover agora se aplicam apenas ao poster de imagem, mantendo a tipografia perfeitamente estática com um leve acento de cor. Documentação em `docs/molecules/card-personagem.md`.

---

## [2.3.0] — 2026-05-25

### Adicionado
- **Organismo `secao-personagens`** (`organisms/secao-personagens.php` & `.css`): grade de cards cinematográficos de personagem (`card-personagem`). Mobile `< 48rem`: scroll horizontal snap, cards `7rem`. Tablet `≥ 48rem`: grid `auto-fill minmax(8rem, 1fr)`. Desktop `≥ 64rem`: grid `auto-fill minmax(9rem, 1fr)`. Documentação em `docs/organisms/secao-personagens.md`.

---

## [2.2.0] — 2026-05-25

### Adicionado
- **Organismo `secao-dubladores`** (`organisms/secao-dubladores.php` & `.css`): seção de voice actors. Desktop: grid 4 colunas. Tablet/Mobile: scroll horizontal com `scroll-snap`. Documentação em `docs/organisms/secao-dubladores.md`.

### Alterado
- **Molécula `card-personagem-dublador`** — redesign completo (v2.2.0): layout unificado focado no dublador. Avatar circular `5rem` (mobile) / `4.5rem` (desktop). Mobile/tablet: `flex-column` centralizado. Desktop (≥ 64rem): `flex-row`. Novos parâmetros: `character_name`, `episodios`, `ano_inicio`, `ano_fim`. Linha de meta `519 episódios • 2000–2024` gerada automaticamente. Schema.org `Person`.

---

## [2.1.0] — 2026-05-25

### Adicionado
- **Organismo `hero-anime`** (`organisms/hero-anime.php` & `.css`): Hero principal da página de detalhe do anime. Backdrop desfocado + gradiente + poster + info completa (badges, título H1, score MAL, gêneros, meta grid, sinopse, CTAs). Schema.org `TVSeries`. Mobile-first: flex-column → flex-row ≥ 48rem.
- **Documentação `docs/organisms/hero-anime.md`**: Manual técnico completo com parâmetros, schema, tokens CSS, responsividade e exemplo de uso.

---

## [2.0.0] — 2026-05-25

### Adicionado
- **Molécula `relacionado-item`** (`molecules/relacionado-item.php` & `.css`): Card horizontal compacto para exibir animes relacionados. Layout `[thumbnail 4rem 2:3] [tipo de relação em laranja uppercase + título 2 linhas]`. Clicável via `anime_url` opcional. Hover com `translateX` + borda laranja.
- **Documentação `docs/molecules/relacionado-item.md`**: Manual técnico com parâmetros, tabela de tipos de relação Jikan API, tokens CSS e exemplos.

---

## [1.9.0] — 2026-05-25

### Adicionado
- **Molécula `card-recomendacao`** (`molecules/card-recomendacao.php` & `.css`): Card horizontal compacto para exibir animes recomendados. Layout `[thumbnail poster 2:3] [título + contador de recomendações com ícone]`. O card inteiro é clicável via `anime_url` opcional. Reutiliza o átomo `imagem-capa`. Responsivo com thumbnail menor em `≤ 30rem`.
- **Documentação `docs/molecules/card-recomendacao.md`**: Manual técnico com parâmetros, tokens CSS e exemplos.

---

## [1.8.0] — 2026-05-25

### Adicionado
- **Molécula `card-staff`** (`molecules/card-staff.php` & `.css`): Card horizontal compacto para exibir membros da equipe de produção. Layout `[avatar] [nome + cargo]`. O card inteiro é clicável via `staff_url` opcional. Suporta fallback de avatar e truncamento de texto. Responsivo com modo compacto em `≤ 30rem`.
- **Documentação `docs/molecules/card-staff.md`**: Manual técnico com parâmetros, tokens CSS e exemplos.

---

## [1.7.0] — 2026-05-25

### Adicionado
- **Molécula `card-personagem-dublador`** (`molecules/card-personagem-dublador.php` & `.css`): Card horizontal compacto estilo MAL clássico para exibir personagem (esq.) e voice actor (dir.) juntos. Cada lado é individualmente clicável via link opcional. Suporta badge de role colorido (Principal = laranja, Secundário = cinza), fallback de avatar sem imagem e truncamento automático de nomes longos.
- **Documentação `docs/molecules/card-personagem-dublador.md`**: Manual técnico com tabela de parâmetros, tokens CSS utilizados e exemplos de uso.

### Corrigido
- **`card-personagem-dublador` — Responsividade**: Adicionados dois breakpoints ao CSS. `≤ 30rem (480px)`: modo compacto — avatar reduzido para 40px (via `!important` para sobrescrever inline style do PHP), padding e gap apertados, nome em `--text-xxs-size`. `≤ 22.5rem (360px)`: modo empilhado — layout passa para `flex-direction: column`, separador vira linha horizontal, lado VA realinha à esquerda.

---

## [1.6.0] — 2026-05-25

### Adicionado
- **Átomo logo** (`atoms/logo.php` & `atoms/logo.css`): Componente de logotipo com suporte a 5 variantes oficiais da marca (`horizontal-01`, `horizontal-02`, `wordmark`, `icone-quadrado`, `icone-simples`). Injeta SVG inline para controle total via CSS e zero requisição HTTP extra. Suporta link, URL customizada, classes adicionais e fallback de texto.
- **Pasta `img/logos/`**: Diretório oficial de logos no tema (`hello-child/img/logos/`), contendo os 5 SVGs com nomes padronizados (`logo-horizontal-01.svg`, `logo-horizontal-02.svg`, `logo-wordmark.svg`, `logo-icone-quadrado.svg`, `logo-icone-simples.svg`).
- **Documentação `docs/atoms/logo.md`**: Manual técnico com tabela de variantes, parâmetros PHP, regras de SEO/acessibilidade e exemplos de uso.

### Alterado
- **Organismo `header.php`**: Substituído o embed direto do SVG (`file_get_contents` de `Novos-arquivos/`) pelo átomo `logo` com variante `horizontal-02`, seguindo a arquitetura Atomic Design do projeto.
- **`storybook.html`**: Adicionada seção de preview do átomo `logo` exibindo as 5 variantes com nomes e descrições.

### Corrigido
- **Organismo `secao-esteira-animes` — Scroll Infinito**: Reescrito `organisms/secao-esteira-animes.js` para implementar loop infinito via técnica de clonagem dupla (triple-buffer). Ao atingir o último card, a esteira teleporta de forma invisível para o início do set real (e vice-versa ao ir para trás), eliminando o travamento anterior. O teleporte usa `scrollBehavior: auto` + `scrollSnapType: none` por um tick para evitar qualquer flash ou re-snap visível. Setas nunca mais ficam desabilitadas. Atualizada `docs/organisms/secao-esteira-animes.md`.

### Processado (Novos-arquivos)
- `Logo geek ao cubo 01.svg` → `img/logos/logo-horizontal-01.svg`
- `Logo geek ao cubo 02.svg` → `img/logos/logo-horizontal-02.svg`
- `Logo geek ao cubo 03.svg` → `img/logos/logo-wordmark.svg`
- `Logo geek ao cubo 04.svg` → `img/logos/logo-icone-quadrado.svg`
- `Logo geek ao cubo 05.svg` → `img/logos/logo-icone-simples.svg`

---

## [1.5.3] — 2026-05-24

### Adicionado
- **Árvore Dinâmica de Componentes no Storybook**: Menu em árvore interativo na barra lateral do `storybook.html` que organiza e aninha dinamicamente todos os componentes (Átomos, Moléculas e Organismos) em tempo real sob suas respectivas categorias, com recuos e linhas conectivas estilo diretório.
- **Sistema de Busca Reativa e Instantânea**: Adicionado campo de pesquisa premium na barra lateral do Storybook que realiza buscas instantâneas no nome do componente, descrição e nome do arquivo PHP. O sistema oculta dinamicamente seções e sub-menus vazios e expande as categorias de forma inteligente.
- **Realce de Correspondências (Text Highlight)**: Lógica JS robusta que destaca em amarelo/laranja suave os termos coincidentes com a pesquisa ativa dentro dos títulos e descrições dos componentes via tags `<mark>` dinâmicas.
- **Scroll-Spy Baseado em Viewport**: Rastreamento de rolagem suave com `IntersectionObserver` que auto-ajusta e acende a árvore de sub-links na barra lateral de acordo com o componente ativo visualizado na tela, integrado com atualização dinâmica de hash URL.
- **Dashboard de Estatísticas (Stats Grid)**: Painel de contagem de componentes glassmorphic de alta fidelidade inserido no cabeçalho do Storybook, refletindo com paridade total as contagens reais do backlog de Design (35 Átomos, 15 Moléculas, 11 Organismos, totalizando 61 Componentes Ativos).

### Corrigido
- **Remoção de Duplicidades no Storybook**: Excluído o bloco de visualização duplicado e obsoleto da molécula `sidebar-assistir-agora-preview` (linhas 4054-4095) que continha classes legadas de botões, resolvendo erros de paridade de ID.
- **Fidelidade de SEO e Schema.org**: Ajustada a marcação estática dos previews dos componentes `breadcrumb-item` e `breadcrumb` no `storybook.html` para incorporar de forma 100% fiel os atributos Schema.org de Microdata (`itemscope`, `itemtype="https://schema.org/ListItem"`, `itemprop="item"`, `itemprop="name"`, e `meta position`) idênticos às assinaturas do tema WordPress.
- **Fidelidade no badge-horario**: Padronizado o markup do badge-horario standalone e aninhado no `card-anime` para incluir o ícone SVG de relógio e a tag de texto `.badge-horario__text` em conformidade com o átomo original PHP.

## [1.5.2] — 2026-05-24

### Corrigido
- **Molécula sidebar-assistir-agora**: Neutralizados e sobrescritos com precisão todos os estilos herdados do átomo `imagem-capa` (`aspect-ratio`, `box-shadow`, `border`, `border-radius`, e transições de hover) quando o mesmo é utilizado como fundo promocional do card CTA. Adicionado `pointer-events: none` na imagem de fundo para evitar que o mouse acione o hover do próprio átomo e cause quebras de layout.
- **Vitrine Storybook**: Adicionado o preview interativo completo da molécula `sidebar-assistir-agora` no `storybook.html`, incluindo a renderização correta do fundo promocional e os exemplos de código em PHP, completando o showcase visual.
- **Átomo tag-artigo**: Normalizada a extração de parâmetros no PHP (`atoms/tag-artigo.php`) para aceitar tanto a chave `'tag'` quanto a `'name'`. Isso corrigiu em definitivo o bug onde a molécula `tags-artigo` não exibia as tags (devido ao mismatch de parâmetros e anulação do componente por valor nulo).

## [1.5.1] — 2026-05-24

### Alterado
- **Molécula card-personagem**: Reconstrução completa seguindo os mais altos padrões de UX/UI design, no formato de pôster de cinema vertical (aspect-ratio 2:3).
- **Ajustes UX/UI no card-personagem**: Aumentado o espaçamento entre o badge e o texto (`gap` para `space-300`), alinhados todos os textos do painel estritamente à esquerda em todos os breakpoints e mantido o suporte ao nome em japonês/Kanji (`name_kanji`) conforme solicitação de refinamento de interface.
- **CSS do card-personagem**: Estilização premium com painel inferior com efeito glassmorphic, overlay gradiente tricamadas para legibilidade, badges reativas de papel (Principal/Secundário) por variante, efeitos de hover (zoom de imagem, elevação, brilho/glow laranja) e foco de acessibilidade para navegação via teclado.
- **Eliminação de Clamps e Cálculos Dinâmicos**: Removidas todas as funções `clamp` e cálculos dinâmicos de viewport (`vw`) para tamanho de fontes e preenchimento de caixas no arquivo `molecules/card-personagem.css`. O componente agora adere estritamente aos design tokens originais do projeto (`var(--text-sm-size)`, `var(--text-xs-size)`, `var(--text-xxs-size)`, etc.), utilizando media queries CSS padrão para gerenciar a escala em telas menores, evitando burlas de design.
- **Correção de alinhamento em Storybook**: Removido o bloco CSS antigo duplicado/embutido de `.card-personagem` que estava no `<style>` de `storybook.html`. Isso sanou o bug de layout onde as versões mobile/tablet do Storybook forçavam a centralização dos textos do card, permitindo que as regras responsivas de alinhamento à esquerda externas atuem perfeitamente.
- **Documentação card-personagem.md**: Reescrita detalhada mapeando os novos alinhamentos responsivos baseados em design tokens sem clamps, espaçamentos generosos, o parâmetro `name_kanji` restaurado e diretrizes de SEO/Acessibilidade.
- **Showcase no Storybook**: Atualizada a seção no `storybook.html` demonstrando as três variantes em grade reativa (Principal com imagem e Kanji, Principal sem imagem/fallback e Secundário com imagem e Kanji) em conformidade com o design final.

## [1.5.0] — 2026-05-24

### Adicionado
- **Organismo footer**: Rodapé semântico responsivo (`organisms/footer.php` e `.css`) otimizado para SEO avançado, contendo links institucionais descritivos, créditos e conformidade com design tokens.
- **Documentação footer.md**: Manual técnico explicativo em `docs/organisms/footer.md` detalhando as especificações do rodapé.

### Corrigido
- **Átomo imagem-capa**: Corrigidos bugs nos componentes `secao-artigo-unico.php` e `card-noticia-relacionada.php` onde o parâmetro era incorretamente enviado como `image_url` em vez de `src`, quebrando o carregamento das capas.
- **Molécula card-anime**: Refatorado o título para suportar tag HTML dinâmica (`h2` ou `h3` conforme parâmetro `title_tag`) para flexibilidade de hierarquia de SEO.
- **Estrutura de SEO em Breadcrumbs**: Adicionados microdados de Schema (`BreadcrumbList` e `ListItem`) nos arquivos `molecules/breadcrumb.php` e `atoms/breadcrumb-item.php` para indexação avançada estruturada.
- **Backlog Mapeado**: Atualizado o `atomic-design-backlog.md` corrigindo a listagem de componentes existentes (`badge-rank`, `icone-externo-link`, `progress-bar`, `card-personagem`, `stat-bloco` e `footer`) e recalculando os totais globais de componentes.
- **Vitrine Storybook**: Adicionado o preview visual do organismo `footer` no `storybook.html` importando seus estilos locais.

## [1.4.0] — 2026-05-23

### Adicionado
- **Átomo badge-genero**: Pílula de gênero ou tag clicável moderna (`atoms/badge-genero.php` e `.css`) para categorização de animes com suporte a acessibilidade.
- **Molécula card-anime**: Card vertical premium (`molecules/card-anime.php` e `.css`) com animações de hover, exibição de nota MAL e gêneros alinhados perfeitamente.
- **Organismo secao-esteira-animes**: Seção de trilho horizontal deslizante (`organisms/secao-esteira-animes.php` e `.css`) estilo plataformas de streaming, com Scroll Snap nativo e navegação responsiva.
- **Script secao-esteira-animes.js**: Lógica vanilla JS para rolagem suave das setas reativas no desktop e desativação automática nos limites da esteira.
- **Documentações Técnicas**: Criados os manuais individuais `badge-genero.md`, `card-anime.md` e `secao-esteira-animes.md` mapeando toda a anatomia técnica e WCAG A11y.

### Alterado
- **Átomo imagem-capa**: Ajustada a pílula de placeholder (`.imagem-capa-placeholder`) em `imagem-capa.css` com `position: absolute`, `box-sizing: border-box` e `top/bottom/left/right: 0`, resolvendo um bug de cálculo de layout onde a borda de 2px causava transbordamento de largura do contêiner e gerava incompatibilidade com `card-anime__media-link`.
- **Molécula card-anime**: Removida a exibição do badge de status de transmissão para otimizar a visualização em catálogos de animes. Reordenado o conteúdo textual posicionando as tags de gênero (genres) acima do título. Corrigida a largura do link da capa (`.card-anime__media-link`) para `100%` com `max-width: 100%` e `overflow: hidden`, eliminando transbordamentos. Aplicado `min-width: 0`, `max-width: 100%` e `overflow: hidden` na molécula e no seu contêiner de conteúdo, eliminando em definitivo o bug de flexbox do navegador onde textos longos de títulos estouram a largura física do card, com a introdução de um padding lateral de `var(--space-200)` (8px) para excelente Proximity Spacing. **Correção Crítica**: Enfileirado explicitamente `box-sizing: border-box` no contêiner `.card-anime` e em todos os seus descendentes (`.card-anime *`), garantindo que o padding e bordas não causem transbordamento ou façam o texto ultrapassar a largura do card em ambientes sem reset global (como páginas integradas com Elementor no WordPress). Adicionados também `overflow-wrap: break-word` e `word-break: break-word` na manchete `.card-anime__title` para tratar strings ou palavras gigantes sem quebrar a largura do card. **Ajuste de Flexbox**: Removida a propriedade `aspect-ratio: 2 / 3` (deixando-a apenas no contêiner interno) e mantido apenas `flex-shrink: 0` em `.card-anime__media-link`. Isso soluciona o bug nativo de Flexbox onde o contêiner da imagem sofria encolhimento horizontal indevido quando a altura do card era esticada pelo CSS Grid ou carrossel, garantindo que a capa ocupe 100% da largura física e não pareça mais estreita que a caixa de textos.
- **Molécula card-noticia**: Aplicado preventivamente `box-sizing: border-box` no contêiner `.card-noticia` e em todos os seus filhos para imunizar o layout contra quebras de largura em diferentes contextos e builders do WordPress.
- **Organismo secao-esteira-animes**: Reduzido o espaçamento entre cartões (gap do trilho) de `space-400` (24px) para `space-300` (16px) aprimorando as proporções visuais e o aproveitamento de tela.
- **Molécula sidebar-assistir-agora**: Corrigido CSS removendo `!important`, ajustando `min-height` de 380px para 280px, e removendo `border-bottom: none !important` para evitar conflitos de estilo. O botão agora usa `width: 100%` sem `!important` e `display: flex` sem `!important`.
- **Vitrine Storybook (storybook.html) & Docs**: Atualizados os previews interativos e documentações refletindo as correções no `sidebar-assistir-agora`.

---

## [1.3.0] — 2026-05-23

### Adicionado
- **Átomo carousel-dot**: Indicador de slide estilo pílula ativa com transição de expansão horizontal e glow laranja primário.
- **Átomo btn-nav-arrow**: Botão circular de setas direcionais com efeito glassmorphism, sombra 3D e chevron reativo que sofre deslocamento micro-translação no hover.
- **Molécula carousel-nav**: Dock unificado centralizado que agrupa as setas direcionais e os bolinhas indicadoras.
- **Organismo secao-carrossel-destaque**: Nova seção de manchetes da homepage. Agrupa até 4 cards Hero Horizontal em um trilho Scroll Snap nativo acelerado por GPU.
- **Script secao-carrossel-destaque.js**: Inteligência vanilla JS com controle de setas/dots, Scroll-Spy de alta performance (atualização dinâmica no swipe gestual de celulares) e Autoplay temporizado acessível (pausa no hover e foco de teclado).
- **Documentação secao-carrossel-destaque.md**: Manual técnico mapeando a anatomia, parâmetros PHP, regras de acessibilidade WCAG e integração no WordPress.

---

## [1.2.1] — 2026-05-23

### Adicionado
- **Variação Hero Vertical (hero-vertical)**: Criada uma nova variação dedicada no PHP (`molecules/card-noticia.php`) e no CSS (`molecules/card-noticia.css` -> classe `.card-noticia--hero-vertical`) especificamente estruturada para destaques verticais em colunas, ideal para o organismo de destaque principal e widgets.
- **Vitrine Hero Vertical (storybook.html)**: Adicionada a demonstração viva e individual do Hero Vertical no showcase de moléculas.

### Alterado
- **Variação Hero Horizontal (hero)**: Revertida a variação `.card-noticia--hero` para o seu layout horizontal original de alto impacto (split widescreen 60/40), mantendo a máxima flexibilidade de banners do design system.
- **Organismo secao-destaque**: Atualizada a manchete principal no PHP (`secao-destaque.php`) e no Storybook (`storybook.html`) para renderizar a nova classe de destaque vertical `.card-noticia--hero-vertical`, garantindo o alinhamento pixel-perfect e paridade de rotação futura.
- **Documentações Técnicas**: Atualizadas `docs/molecules/card-noticia.md` e `docs/organisms/secao-destaque.md` detalhando a nova variante `hero-vertical` e a separação dos casos de uso de Hero Horizontal e Hero Vertical.

---

## [1.2.0] — 2026-05-23

### Adicionado
- **Organismo secao-destaque**: Nova seção de manchete em destaque nobre para o topo da homepage. Combina 1 manchete/Hero vertical gigante na esquerda (60%) e um painel stack de 3 variações Lista compactas na direita (40%), representando contextos de Lista Horizontal, Widget Lateral e Recomendações Relacionadas.
- **Documentação secao-destaque.md**: Manual técnico completo em `docs/organisms/secao-destaque.md` detalhando parâmetros PHP, estrutura HTML5, responsividade fluida e padrões WCAG A11y.

### Alterado
- **Molécula card-noticia (Variação Hero)**: Refatorada a classe `.card-noticia--hero` em `molecules/card-noticia.css` para renderizar o layout estritamente vertical em desktop. Isso cria um formato "outdoor/billboard" de enorme impacto e fornece a estrutura ideal para futura rotação e transição interativa dos 4 cards.
- **Vitrine Storybook (storybook.html)**: Atualizada a Seção de Destaque no Storybook com a exibição do Hero vertical e personalização dos cards da lista lateral representando as categorias "Lista Horizontal", "Widget Lateral" e "Recomendações".

---

## [1.1.0] — 2026-05-23

### Adicionado
- **Molécula card-noticia**: Card de notícias e artigos do blog inspirado na Crunchyroll, com suporte nativo a 3 variações de exibição (`grid`, `list`, `hero`), efeito zoom na capa widescreen `16:9` e transição de cor do título no hover.
- **Documentação card-noticia.md**: Manual técnico detalhando parâmetros PHP, BEM, acessibilidade (WCAG `aria-label` automatizado e alt de imagens) e proteção CLS (Core Web Vitals).
- **card-noticia ao storybook.html**: Vitrine interativa no Storybook exibindo as três variações em ação fluida.

### Corrigido
- **Consistência Tipográfica no Navigation Drawer**: Corrigido bug de especificidade no seletor `button.drawer-link` em `atoms/drawer-link.css` e `storybook.html` (removendo `font-family/size/weight: inherit`), alinhando os botões acordeão do menu lateral móvel na mesma família tipográfica, tamanho e negrito das demais tags de navegação `<a>` do design system.

---

## [1.0.1] — 2026-05-23

### Adicionado
- **Átomo input-busca-compact**: Input de busca simplificado para header sem botão integrado. Ao ser clicado (readonly), abre o modal de busca completo (`search-modal`) através da classe `.js-open-search-modal`.
- **Organismo search-modal ao storybook**: Adicionado componente do modal de busca ao storybook.html com CSS responsivo e logo SVG compactada.
- **Documentação search-modal.md**: Adicionado arquivo explicativo sobre funcionamento, SEO e WCAG A11y do modal.

### Alterado
- **organisms/header.php**: Substituído `form-busca` por `input-busca-compact` na barra de busca do header.
- **organisms/header.css**: Ajustado CSS para acomodar o `input-busca-compact` em vez do `form-busca`.
- **organisms/search-modal.php**: Substituído o formulário inconsistente `form-busca-modal` pela molécula unificada e padronizada `form-busca`.
- **organisms/search-modal.js**: Foco automático alterado para capturar a classe genérica `.input-field` do design system.
- **organisms/navigation-drawer.php**: Adicionado carregamento dinâmico de links do menu do WordPress e fallbacks de acessibilidade.
- **functions.php**: Adicionado o acoplamento global dos organismos `search-modal` e `navigation-drawer` no rodapé através do gancho `wp_footer`.

### Corrigido
- **organisms/header.php**: Reconstruído e restaurado o cabeçalho completo que estava truncado, estabelecendo a estrutura HTML5 semântica responsiva, acessibilidade por leitores de tela (tags role/aria) e integração funcional de buscas e menu mobile.
- **organisms/search-modal.js (Bug de Clique)**: Corrigido falha de disparo do modal adicionando **Delegação de Eventos (Event Delegation)** no objeto global `document` (evitando falhas por carregamento tardio ou dinâmico do Elementor).
- **organisms/navigation-drawer.js (Bug de Clique)**: Corrigido falha de abertura do menu lateral adicionando **Delegação de Eventos** escutando `.js-open-drawer` de forma global para garantir o funcionamento 100% integrado com o Elementor.

### Removido
- **molecules/form-busca-modal.php**: Removido arquivo redundante de formulário de busca específico do modal.
- **molecules/form-busca-modal.css**: Removido arquivo de estilização redundante.

---

## [1.0.0] — 2026-05-23

### Adicionado
- **Estrutura Base do Tema Filho**: Criada toda a árvore de diretórios do padrão **Atomic Design** (`atoms/`, `molecules/`, `organisms/`, `templates/`, `docs/`, `Novos-arquivos/`).
- **`hello-child/style.css`**: Identificação do tema filho no ecossistema do WordPress.
- **`hello-child/functions.php`**: Lógica de enfileiramento inteligente de CSS (Dynamic Component Enqueue) para ganho de Core Web Vitals e carregamento do Google Fonts.
- **`hello-child/design-tokens.css`**: Definição centralizada de todas as propriedades customizadas do CSS (Cores Brand, Neutral, Status, Tipografia, Espaçamentos, Bordas, Breakpoints).
- **`hello-child/docs/README.md`**: Manual do desenvolvedor, padrões BEM e guia de responsividade clamp.
- **Átomos Desenvolvidos**:
  - `atoms/btn-primary.php` & `.css`: Botão principal com suporte a links patrocinados.
  - `atoms/btn-secondary.php` & `.css`: Botão secundário de retorno e ações alternativas.
  - `atoms/badge-status.php` & `.css`: Badge em pílula pulsante ("Em exibição", "Finalizado").
  - `atoms/nota-mal.php` & `.css`: Score numérico com ícone de estrela SVG inline (com lógica de cor de erro dinâmica para scores < 5.0).
  - `atoms/imagem-capa.php` & `.css`: Posters com aspect-ratio 2:3, lazy load e overlay de score e suporte a badge de horários para lançamentos diários.
  - `atoms/aviso-adblock.php` & `.css`: Banner educativo responsivo (anti-interstitial invasivo).
  - `atoms/input-busca.php` & `.css`: Biblioteca de Inputs flexível (Busca com Lupa, texto simples e seletores/selects).
  - `atoms/badge-horario.php` & `.css`: Badge em pílula glassmorphic escura com relógio em laranja para lançamentos diários da grade de exibição.
  - `atoms/input-label.php` & `.css`: Rótulo (label) semântico em caixa alta para inputs de formulários.
  - `atoms/input-helper.php` & `.css`: Texto de apoio informativo ou alerta de erro de validação (com tags de acessibilidade WCAG).
- **Moléculas Desenvolvidas**:
  - `molecules/form-field.php` & `.css`: Grupo de campo de formulário modular (composição de label + input + helper/error).
  - `molecules/form-busca.php` & `.css`: Formulário de pesquisa integrado com campo de busca e botão de ação primário flat.
- **Organismos Desenvolvidos**:
  - `organisms/navigation-drawer.php`, `.css` & `.js`: Organismo de menu lateral móvel completo com suporte a acordeão e acessibilidade WCAG.
- **Estruturação do Navigation Drawer (Menu Lateral Mobile)**:
  - Criados os átomos `atoms/drawer-overlay.php` & `.css` (fundo com desfoque blur), `atoms/drawer-link.php` & `.css` (com chevron de dropdown automático) e `atoms/drawer-sub-link.php` & `.css` (sub-navegação com marcador).
- **`hello-child/functions.php`**: Estendido com suporte para Dynamic JS Enqueue, permitindo que scripts JavaScript de componentes sejam carregados automaticamente no rodapé somente se o respectivo componente for renderizado na página.
- **`hello-child/storybook.html`**: Atualizado com renderizações vivas, códigos de uso e controles interativos para o botão hamburger (que vira X), overlay blur de fundo, links de gaveta, sub-links com dot e o organismo do Navigation Drawer completo em ação.
- **Documentações individuais em `.md`**: Criada a documentação técnica para o organismo `organisms/navigation-drawer` no diretório `docs/organisms/`.

## [1.4.6] — 2026-05-23

### Adicionado
- **Molécula card-personagem:** Card de personagem com avatar, nome e tipo (Principal/Secundário). Com suporte a fallback de avatar sem imagem e responsividade.
- **Documentação card-personagem.md:** Manual técnico detalhando parâmetros PHP, variáveis CSS, SEO, acessibilidade, responsividade e exemplos de uso.

---

## [1.4.5] — 2026-05-23

### Adicionado
- **Átomo badge-rank:** Badge especial de ranking com cor dourada para exibir rankings especiais (#1, Top 10) na página de detalhe do anime. Com suporte a variações (default, top10, gold).
- **Documentação badge-rank.md:** Manual técnico detalhando parâmetros PHP, variáveis CSS, variações, SEO, acessibilidade e exemplos de uso.

---

## [1.4.4] — 2026-05-23

### Adicionado
- **Átomo progress-bar:** Barra de progresso genérica para exibir percentuais (usuários, estatísticas, etc.). Com suporte a label opcional, exibição de percentual e gradiente laranja.
- **Documentação progress-bar.md:** Manual técnico detalhando parâmetros PHP, variáveis CSS, SEO, acessibilidade e exemplos de uso.

---

## [1.4.3] — 2026-05-23

### Adicionado
- **Molécula stat-bloco:** Bloco de estatísticas para página de detalhes do anime. Compõe rating-score + rank + popularidade + membros em layout grid responsivo.
- **Documentação stat-bloco.md:** Manual técnico detalhando parâmetros PHP, variáveis CSS, responsividade e exemplos de uso.

---

## [1.4.2] — 2026-05-23

### Adicionado
- **Átomo icone-externo-link:** Ícone + label para links externos (ANN, Wiki, etc.). Indica visualmente que o link abre em nova aba/janela com ícone de seta externa.
- **Documentação icone-externo-link.md:** Manual técnico detalhando parâmetros PHP, variáveis CSS, SEO, acessibilidade e exemplos de uso.

---

## [1.4.1] — 2026-05-23

### Corrigido
- `molecules/sidebar-assistir-agora.php`: Corrigido parâmetro de chamada do átomo `imagem-capa` de `image_url` para `src` (conforme padrão do átomo).
- `molecules/sidebar-assistir-agora.css`: Atualizado seletor `.sidebar-assistir-agora__bg img` para `.sidebar-assistir-agora__bg .imagem-capa` para refletir a estrutura real do átomo.
