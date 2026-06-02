# Jikan API Data Mapping

Este documento lista todos os parâmetros retornados pelo endpoint `/anime/{id}` da **Jikan API v4** e indica como eles são utilizados atualmente na plataforma *Geek ao Cubo*.

**Nota Importante (Arquitetura "Sem Filtro"):**
A partir da versão mais recente, o sistema salva o **payload completo (raw)** retornado pela Jikan em formato JSON. O objetivo é garantir que não precisemos refazer a importação de catálogo toda vez que uma nova funcionalidade for lançada.
- **Anime:** O JSON bruto é armazenado no campo ACF `anime_jikan_raw`.
- **Episódio:** O JSON bruto do episódio é armazenado no campo ACF `ep_jikan_raw`.

Esta tabela é o **Dicionário Oficial de Dados** do ecossistema e deve ser atualizada sempre que um dado do Jikan for transformado em um elemento visível ou lógico no Blog.

---

## 1. Anime Payload (`/anime/{id}`)

| Parâmetro Jikan | Uso no Geek ao Cubo | Onde é Salvo / Renderizado |
| :--- | :--- | :--- |
| `mal_id` | **[USADO]** Chave primária. | `anime_id_mal` (ACF). Usado como ID único para referências cruzadas. |
| `url` | [NÃO USADO] Link da página no MAL. | Salvo apenas no Raw Payload. |
| `images` | **[USADO]** Capas do Anime. | `anime_imagem_capa_url`. Renderizado nas miniaturas e cards (via large_image_url). |
| `trailer.url` | **[USADO]** Trailer de vídeo. | `anime_trailer_url`. Renderizado no modal de trailer via YouTube. |
| `approved` | [NÃO USADO] Se é aprovado no MAL. | Salvo apenas no Raw Payload. |
| `titles` | **[USADO]** Títulos localizados. | Título do Post (title_english) e `anime_titulo_japones` / `anime_titulos_alternativos`. |
| `type` | [NÃO USADO] Formato (TV, Movie). | Salvo apenas no Raw Payload. |
| `source` | **[USADO]** Obra original. | `anime_source`. Renderizado na ficha técnica do anime (Mangá, Light Novel, etc). |
| `episodes` | **[USADO]** Total de episódios. | `anime_total_episodios`. Renderizado na ficha técnica. |
| `status` | [NÃO USADO] Status atual. | Salvo apenas no Raw Payload. |
| `airing` | [NÃO USADO] Booleano de exibição. | Salvo apenas no Raw Payload. |
| `aired` | [NÃO USADO] Data de transmissão. | Salvo apenas no Raw Payload. |
| `duration` | **[USADO]** Duração por ep. | `anime_duracao`. Renderizado na ficha técnica. |
| `rating` | **[USADO]** Classificação etária. | `anime_rating`. Renderizado na ficha técnica. |
| `score` | **[USADO]** Avaliação geral. | `anime_nota_mal`. Usado para os Rankings e Ficha Técnica. |
| `scored_by` | [NÃO USADO] Qtd de avaliações. | Salvo apenas no Raw Payload. |
| `rank` | **[USADO]** Posição global. | `anime_ranking`. Renderizado na ficha técnica. |
| `popularity` | **[USADO]** Popularidade global. | `anime_popularidade`. Renderizado na ficha técnica. |
| `members` | **[USADO]** Quantidade de fãs. | `anime_membros`. Renderizado na ficha técnica. |
| `favorites` | [NÃO USADO] Favoritos. | Salvo apenas no Raw Payload. |
| `synopsis` | **[USADO]** Resumo da história. | `anime_sinopse`. Renderizado na seção de sinopse. |
| `background` | [NÃO USADO] Curiosidades. | Salvo apenas no Raw Payload. |
| `season` & `year` | **[USADO]** Temporada (Spring, etc). | Associado ao CPT Temporada (`temp_periodo`, `temp_ano`). |
| `broadcast` | **[USADO]** Horário de exibição. | `anime_horario_exibicao` e `anime_dia_semana`. Usado no cronograma (BRT). |
| `producers` | [NÃO USADO] Produtoras envolvidas.| Salvo apenas no Raw Payload. |
| `licensors` | [NÃO USADO] Distribuidoras. | Salvo apenas no Raw Payload. |
| `studios` | **[USADO]** Estúdio de animação. | `anime_studio`. Renderizado na ficha técnica. |
| `genres` | **[USADO]** Gêneros temáticos. | Taxonomia WP `genero`. Renderizado nas pílulas de tag do anime. |
| `explicit_genres` | [NÃO USADO] Gêneros 18+. | Salvo apenas no Raw Payload. |
| `themes` | **[USADO]** Temas do anime. | Mesclado na taxonomia WP `genero`. |
| `demographics` | **[USADO]** Público alvo. | Mesclado na taxonomia WP `genero` (Shounen, Seinen). |
| `relations` | **[USADO]** Animes Prequels. | Usado pelo robô para buscar temporadas anteriores recursivamente. |

---

## 2. Episódio Payload (`/anime/{id}/episodes`)

| Parâmetro Jikan | Uso no Geek ao Cubo | Onde é Salvo / Renderizado |
| :--- | :--- | :--- |
| `mal_id` | **[USADO]** ID/Número do Episódio. | `ep_numero`. Identificador do episódio. |
| `title` | **[USADO]** Nome oficial do episódio. | Título do Post (composição). |
| `score` | **[USADO]** Nota do episódio. | `ep_nota_media`. Renderizado no acordeão de episódios (Estrelas). |
| `aired` | **[USADO]** Data de estreia. | `ep_data_lancamento`. Usado no acordeão e no cronograma. |
| `filler` | [NÃO USADO] Episódio filler? | Salvo apenas no Raw Payload. |
| `recap` | [NÃO USADO] Episódio resumo? | Salvo apenas no Raw Payload. |

---

*Documentação criada sob a arquitetura Sem Filtro. Toda vez que um parâmetro `[NÃO USADO]` for mapeado para o design ou ACF, atualize este arquivo.*
