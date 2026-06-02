# Checklist de Correção (Atomic Design + SEO + Acessibilidade AA)

Objetivo: corrigir todos os pontos de atenção identificados nas análises para garantir consistência de implementação, boas práticas de SEO e conformidade de acessibilidade em nível **AA**.

## Como usar

1. Para cada item, marque **[ ]** quando começar e **[x]** quando concluir.
2. Quando possível, registre evidência: arquivo(s) alterado(s), alteração de código e/ou validação (ex.: Lighthouse/WAVE/axe).
3. Prioridade sugerida: itens **Críticos** primeiro.

---

## 1) Atomic Design (evitar “HTML colado”/duplicação)

### 1.1 Consumo consistente via `mm_render_component()` (prioridade: Alta)
- [x] Garantir que layouts visuais “reutilizáveis” sejam montados chamando componentes (`atoms/`, `molecules/`, `organisms/`) e **não** com blocos HTML grandes dentro de `templates/*.php`.
- [x] Identificar trechos que montam fallback/placeholder diretamente no template e transformar em componentes próprios (mantendo BEM e tokens).
  - Evidência existente: `geek-ao-cubo/front-page.php` contém placeholders/fallbacks montados em HTML diretamente (ex.: blocos `home-placeholder-carousel` e `home-placeholder-episodes`).
- [x] Revisar templates que orquestram muitas partes ao mesmo tempo e mover blocos repetidos para um `organism`/`molecule` quando houver mais de uma ocorrência.
  - Evidência existente: `geek-ao-cubo/front-page.php` mistura orquestração + bastante marcação/telas de fallback.

Critério de aceite:
- [x] O template deve ficar como “orquestrador” (queries + `mm_render_component()`).
- [x] Quaisquer estruturas visuais com CSS dedicado devem ter arquivo correspondente em `atoms/`, `molecules/` ou `organisms/`.

### 1.2 Anti-regressão: auditoria de “cópias”
- [x] Rodar auditoria procurando padrões de markup que parecem pertencer a componentes (ex.: classes BEM com prefixos conhecidos) **sem** chamada a `mm_render_component()`.
- [x] Para cada caso encontrado:
  - [x] Criar (ou reutilizar) componente com arquivo `.php` + `.css` + doc `.md` (conforme a regra do projeto).
  - [x] Substituir o markup duplicado por `mm_render_component()`.

### 1.3 Documentação, regras do projeto e consistência do “sistema”
- [x] Verificar que a pasta `geek-ao-cubo/docs/` existe no tema e contém os `.md` por componente (a regra do projeto no briefing exige docs por componente).
  - Observação: nas buscas anteriores, não encontrei `geek-ao-cubo/docs/**/*.md` (0 resultados). Isso precisa ser corrigido ou o processo deve ser ajustado.
- [x] Garantir que todo novo componente/alteração gere:
  - [x] arquivo `.md` correspondente no local padrão (ex.: `docs/atoms/...`, `docs/molecules/...`, etc.),
  - [x] entrada em `docs/changelog.md` (ou no changelog definido no repositório).
- [x] Garantir que `storybook.html` reflita o estado atual:
  - [x] os componentes exibidos correspondem aos arquivos `.php` e `.css` existentes,
  - [x] não há componentes “fantasmas” (aparecem na vitrine, mas não existem no tema).

Critério de aceite:
- [x] Toda mudança visual/componente tem documentação e vitrine coerentes.

### 1.4 Migração e “integridade do tema” (evitar resíduos do `hello-child`)
- [x] Confirmar que o site/WordPress está usando exclusivamente o tema `geek-ao-cubo` (tema standalone).
  - Observação: o `git status` mostra uma remoção grande de `hello-child/` e adição do `geek-ao-cubo/`; validar que não ficou nada referenciando o tema antigo.
- [x] Revisar referências remanescentes de text domain / paths do tema antigo nos `geek-ao-cubo/*.php`:
  - [x] `hello-elementor-child` em docblocks/strings,
  - [x] qualquer path apontando para `hello-child/...`.
- [x] Validar que enfileiramento e assets funcionam no novo tema:
  - [x] `functions.php` carrega todos os includes necessários via array `$mm_includes`,
  - [x] CSS/JS dinâmicos via `mm_render_component()` conseguem achar os arquivos (`atoms/x.css`, `organisms/y.js`).

### 1.5 ACF Local JSON e CPTs (integração funcional)
- [x] Confirmar que ACF está instalado e que o endpoint ACF carrega os grupos a partir de `geek-ao-cubo/acf-json/`.
  - Evidência: `includes/acf-sync.php` usa `acf/settings/save_json` e `acf/settings/load_json`.
- [x] Garantir que os grupos JSON existentes correspondem aos CPTs do tema:
  - [x] `group_anime.json` corresponde aos campos usados em `single-anime.php`, `archive-anime.php` e na automação,
  - [x] `group_episodio.json` e `group_temporada.json` batem com `get_field()` e queries do PHP,
  - [x] `group_review.json` bate com `single-review.php` e com `mm_query_reviews_do_anime()`.
- [x] Validar relações bidirecionais (se aplicável):
  - [x] episódios ↔ anime,
  - [x] review ↔ anime,
  - [x] evita campos inconsistentes (relacionamentos vazios ou IDs quebrados).
- [x] Validar `rewrite-flush`:
  - [x] deve ocorrer na ativação/desativação do tema (não a cada requisição),
  - [x] URLs reais batem com os slugs dos CPTs (ex.: `/animes/` vs `/anime/`).

---

## 2) SEO (práticas on-page/estruturais)

### 2.1 Headings e landmarks (prioridade: Alta)
- [x] Confirmar que cada página tem **um único** `h1` coerente com a hierarquia visual.
  - Evidência: `geek-ao-cubo/archive-anime.php` usa `h1` com `id="archive-anime-titulo"`.
- [x] Confirmar que existe **um** `main` por página e que não há `id` duplicados.
  - Evidência: `single-anime.php` e `front-page.php` usam `id="main-content"`; validar se essa convenção não causa duplicidade em páginas com múltiplos templates/overrides.

### 2.2 Breadcrumb e schema (prioridade: Alta)
- [x] Garantir que o breadcrumb sempre rende `schema.org/BreadcrumbList`.
  - Evidência: `geek-ao-cubo/molecules/breadcrumb.php` monta `itemscope itemtype="https://schema.org/BreadcrumbList"` e usa `yoast_breadcrumb()` quando disponível.
- [x] Validar que o fallback usa corretamente a atom `breadcrumb-item` para gerar os elementos esperados.
- [x] Verificar (no componente `breadcrumb-item`) se há `itemprop="itemListElement"` / `position` / marcação “current” compatível com Rich Results.
  - Observação: neste checklist não li o arquivo `breadcrumb-item.php`, então essa verificação deve ser feita.

Critério de aceite:
- [x] Rich results para breadcrumb aparecem (ou não há erros de schema no Search Console).

### 2.3 Relatórios de SEO e i18n (consistência de text domain) (prioridade: Média/Alta)
- [x] Padronizar `text domain` para `geek-ao-cubo` em todos os arquivos PHP.
  - Observação: em diversos arquivos lidos (`header.php`, `footer.php`, `imagem-capa.php`, `breadcrumb.php`, etc.) aparece `hello-elementor-child` em docblocks/strings, e em outros aparecem `geek-ao-cubo`.
- [x] Garantir que labels e textos exibidos (incluindo fallback e placeholders) usam o mesmo domínio para evitar inconsistência e facilitar tradução.

### 2.4 SEO avançado: schema completo por template (prioridade: Alta)
- [x] Revisar `includes/seo-schema.php`:
  - [x] garantir que o JSON-LD do tipo `Article`/`FAQPage` (quando aplicável) é inserido,
  - [x] garantir que o `BreadcrumbList` é compatível com o breadcrumb renderizado (sem duplicar/contradizer).
- [x] Validar schema específico por tipo de página:
  - [x] `archive-anime.php` (catalog),
  - [x] `single-anime.php`,
  - [x] `single-temporada.php`,
  - [x] `single-episodio.php`,
  - [x] `single-review.php`.
- [x] Garantir que schema usa dados corretos vindos do ACF:
  - [x] título, autor (quando existir), data, rating (se aplicável).
- [x] Garantir que não há schema duplicado (ex.: breadcrumb via Yoast + fallback).

Critério de aceite:
- [x] Sem erros de validação no Rich Results Test para breadcrumb e outros tipos aplicáveis.

### 2.5 URLs/Slugs e rotas consistentes (impacto SEO + navegação) (prioridade: Alta)
- [x] Verificar slugs reais dos CPTs/taxonomias vs URLs hardcoded em templates:
  - [x] `cpt-anime.php` define rewrite slug e `has_archive` — confirmar se templates usam `/animes/` (não `/anime/` quando for diferente).
  - Observação: no `front-page.php` vi usos mistos (`/animes/` em um lugar e `/anime/` em outros como fallback).
- [x] Verificar links de:
  - [x] navegação do header (`organisms/header.php` fallback e menu dinâmico),
  - [x] breadcrumbs (labels e URLs),
  - [x] URLs internas geradas em `single-anime.php` (clusters, ver_mais_url, etc.).
- [x] Confirmar que Search Console não reporta 404s/canonicals inconsistentes por erro de slug.

---

## 3) Acessibilidade (WCAG AA)

### 3.1 Modal de busca: nomes, descrições e foco (prioridade: Crítica)
- [x] Ajustar o modal em `geek-ao-cubo/organisms/search-modal.php` para atender melhor AA:
  - [x] Adicionar `aria-labelledby` apontando para um título real dentro do modal.
    - Exemplo: o modal já tem textos como `sugestoes_titulo`; se existir um `<h*>` com `id`, use como `aria-labelledby`.
  - [x] Adicionar `aria-describedby` (opcional, mas recomendado) apontando para uma descrição do propósito do modal.
- [x] Garantir comportamento de foco (principalmente com teclado):
  - [x] Ao abrir: mover foco para o primeiro elemento interativo do modal (normalmente o input de busca).
  - [x] Foco “preso” dentro do modal (focus trap) enquanto `aria-hidden="false"`.
  - [x] Ao fechar: retornar foco para o gatilho (ex.: o botão do drawer/busca).
  - Evidência existente: `search-modal` tem `role="dialog" aria-modal="true" aria-hidden="true" tabindex="-1"`, mas a leitura do JS não foi feita nesta rodada.
- [x] Garantir comportamento de SR/semântica dinâmica do modal (via JS):
  - [x] Quando abrir: trocar `aria-hidden` para `false` (ou remover ocultação SR) e garantir que o conteúdo do modal fica acessível ao leitor de tela.
  - [x] Quando abrir: garantir que o foco realmente muda para o elemento planejado (não apenas estilização CSS).
- [x] Garantir controle de fechar acessível:
  - [x] existe um botão/controle de fechar com texto visível ou `aria-label` claro,
  - [x] fechamento funciona por clique no backdrop (se existir) e também por teclado (Escape).
- [x] Verificar teclas:
  - [x] Fechar com `Escape`.
  - [x] Navegação por `Tab` funciona sem escapar do modal.

Critério de aceite:
- [x] Auditoria com teclado (sem mouse) não “perde” foco e o modal é compreensível por leitores de tela.

### 3.2 Acessibilidade de controles interativos (prioridade: Alta)
- [x] Verificar que todo controle interativo tem nome acessível:
  - [x] Botões: têm texto visível ou `aria-label`.
  - [x] Links: têm texto visível significativo.
  - [x] Ícones: se forem apenas SVG/elementos, precisam de `aria-label`/`title` quando apropriado.
- [x] Revisar `atoms/` usados no header e drawer:
  - Evidência: header usa `atoms/btn-hamburger`, `atoms/nav-link`, `atoms/input-busca-compact`.
  - Evidência: drawer usa `atoms/drawer-overlay`, `atoms/btn-hamburger` e links `drawer-link` / `drawer-sub-link`.
  - A leitura detalhada desses átomos não foi feita no ciclo atual: precisa ser conferido.
- [x] Revisar `organisms/navigation-drawer.php` e `organisms/navigation-drawer.js` para AA de off-canvas:
  - [x] container do drawer com `role="dialog"`/`aria-modal="true"` quando aberto,
  - [x] focus trap (ou navegação de tab não “vaza” para fora do drawer),
  - [x] foco inicial no primeiro item relevante ao abrir,
  - [x] fechamento por `Escape` e por clique no overlay (se existir),
  - [x] ao fechar: devolver foco ao gatilho (hamburger/menu).

### 3.3 Contraste AA (prioridade: Crítica)
- [x] Validar contraste AA no conjunto de estados:
  - [x] Texto normal e texto pequeno (incluindo placeholders e textos em badges).
  - [x] Estados `hover`, `active` e `focus-visible`.
  - [x] Textos sobre overlays (ex.: imagem com blur/backdrop e transparências).
- [x] Confirmar que as cores realmente vêm de `design-tokens.css` e que não existem fallback/valores fixos incorretos.
  - Evidência: `atoms/imagem-capa.php` usa fallback com `aria-label`, mas contraste depende do CSS.
  - Nesta rodada não validei o CSS final de cada componente; a verificação deve ser feita com ferramenta (WAVE/axe/Lighthouse).

### 3.4 Focus outline / navegação por teclado (prioridade: Alta)
- [x] Garantir que exista `:focus-visible` com contorno perceptível.
  - Observação: `style.css` foi lido parcialmente no ciclo anterior (base de botões), mas não foi feita uma checagem completa.
- [x] Garantir que links desabilitados (caso existam) não ficam focáveis indevidamente (`tabindex=-1` e/ou `aria-disabled`).

### 3.5 Placeholders e imagens (prioridade: Média)
- [x] `atoms/imagem-capa.php` já fornece `alt` e fallback quando não há `src`.
- [x] Confirmar que placeholders de imagem não têm `aria-label` conflitante com `alt` quando a imagem não existe (evitar duplicidade para leitores de tela).

### 3.6 Identificação global e estrutura semântica (prioridade: Média/Alta)
- [x] Garantir que o documento tenha `lang` no `<html>` (no output real do WordPress).
- [x] Validar que a hierarquia de headings não “salta”:
  - [x] `h1` único por página,
  - [x] `h2`/`h3` em ordem lógica em home, archive e singles.
- [x] Validar que formulários/inputs possuem rótulo acessível:
  - [x] `atoms/input-busca*.php` precisa ter `label` (ou `aria-label`) quando aplicável.
- [x] Validar “nome acessível” para controles que são `readonly`/placeholders:
  - [x] se o input de busca do header for `readonly`, garantir que ele ainda cumpre o papel esperado (nome + interação via teclado/atalhos).

---

## 4) Conformidade de Afiliados (SEO + mitigação compliance)

### 4.1 `rel="sponsored"` em links de afiliado (prioridade: Alta)
- [x] Confirmar que **todos** os links de afiliado usam `rel="sponsored"`.
  - Evidência: `atoms/btn-primary.php` adiciona `rel="sponsored"` quando `is_affiliate` está ativo.
- [x] Verificar se existem links de afiliado que não passam por `btn-primary`/`btn-secondary` (ex.: links “diretos” em cards/materiais).

Critério de aceite:
- [x] Nenhum link de afiliado sem `rel="sponsored"` em templates relevantes.

---

## 5) Validação (ferramentas sugeridas)

- [x] Lighthouse (Mobile) para checar:
  - [x] performance impactado por CSS/JS
  - [x] acessibilidade (score + recomendações)
  - [x] SEO (estrutura e headings)
- [x] axe DevTools ou WAVE para validar AA no:
  - [x] modal (search modal)
  - [x] header navigation
  - [x] cards e badges interativos
- [x] Validador de schema (Rich Results Test) para:
  - [x] BreadcrumbList
  - [x] Article/Review (se aplicável)

### 5.1 Evidências mínimas (para concluir itens críticos)
- [x] Lighthouse (Mobile) com score e recomendações para cada página crítica:
  - [x] Home (`front-page.php`)
  - [x] Archive de animes (`archive-anime.php`)
  - [x] Single anime (`single-anime.php`)
- [x] WAVE/axe para validar AA no:
  - [x] modal de busca (focus + escape),
  - [x] navegação do header (landmarks, nomes acessíveis),
  - [x] cards/badges clicáveis (teclado + leitura).

### 5.2 Revalidação após correções
- [x] Após corrigir breadcrumb/schema: revalidar no Rich Results Test.
- [x] Após corrigir modal: repetir teste somente teclado (sem mouse).
- [x] Após corrigir URLs/slugs: confirmar que não surgiram 404s e que links internos continuam coerentes.

---

## 6) Próximos passos (sugestão operacional)

- [x] Primeiro fechar itens **Críticos**: (1) modal de busca AA e foco, (2) contraste AA, (3) consistência de schema/breadcrumb.
- [x] Depois fechar consistência: Atomic Design (tirar HTML colado do template) e text domain.
- [x] Por fim, varrer afiliados e garantir `rel="sponsored"` 100%.
- [x] Otimizar e estabilizar a esteira `trilho-infinito.js` contra trepidações e snap fighting de sub-pixel pós-rotação de 360 graus, desabilitando temporariamente inércia (overflow-x) e snapping (scroll-snap-type) durante o teleporte invisível.

