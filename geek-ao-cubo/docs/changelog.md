# Changelog — Geek ao Cubo

Toda criação, alteração ou remoção de componentes do Atomic Design e correções estruturais globais são registradas neste log de alterações.

---

## [2026-05-27]

### Adicionado
- `molecules/home-placeholder-carousel.php` — Molécula de fallback para o carrossel de destaques da Home.
- `molecules/home-placeholder-carousel.css` — Estilos locais do fallback do carrossel da Home.
- `docs/molecules/home-placeholder-carousel.md` — Documentação técnica da molécula de fallback do carrossel.
- `molecules/home-placeholder-episodes.php` — Molécula de fallback (grade esqueleto) para novos episódios da Home.
- `molecules/home-placeholder-episodes.css` — Estilos locais do fallback de novos episódios.
- `docs/molecules/home-placeholder-episodes.md` — Documentação técnica da molécula de fallback de episódios.
- `organisms/secao-episodios-accordion.php` — Organismo que agrupa episódios de um anime por temporada/arco em acordeões contendo tabelas HTML.
- `organisms/secao-episodios-accordion.css` — Estilos visuais e responsividade da tabela acordeão com suporte a scroll horizontal e design tokens.
- `organisms/secao-episodios-accordion.js` — Lógica de interatividade (slide down/up de acordeões) e paginação dinâmica local por blocos de 15 episódios.

### Alterado
- `front-page.php` — Refatorado para extrair HTML inline de fallbacks e consumir os novos componentes `home-placeholder-carousel` e `home-placeholder-episodes` de forma consistente via `mm_render_component()`. Padronizados strings e text domain para `geek-ao-cubo`.
- `front-page.css` — Limpeza de estilos de placeholders de carrossel e novos episódios, delegados para seus respectivos arquivos CSS de componente.
- `organisms/hero-anime.php` — Substituído o rótulo estático `'no MyAnimeList'` pelo número de votos totais do anime, adicionando o parâmetro `$membros`.
- `organisms/hero-anime.js` — Implementada a função `updateMembers()` para realizar a sincronização dinâmica do total de membros/votos da API do Jikan/MAL em tempo de execução com micro-animação suave.
- `single-anime.php` — Injetados os parâmetros `'anime_id_mal'` e `'membros'` no componente `hero-anime` na página singular do anime. Removido o componente `sidebar-anime-info` da barra lateral e transferida a seção de conteúdos relacionados (`secao-relacionados`) da coluna principal para a barra lateral. Integrada também a nova seção de episódios acordeão `secao-episodios-accordion` no corpo principal.
- `single-anime.css` — Adicionadas regras de layout responsivo e estilização de barra lateral para `.secao-relacionados` quando inserida dentro do container `.anime-layout__sidebar`, adaptando a tipografia das relações e forçando uma única coluna para os cards horizontais.
- `single-temporada.php` — Injetados os parâmetros `'anime_id_mal'` e `'membros'` no componente `hero-anime` na seção de destaque sazonal da temporada.
