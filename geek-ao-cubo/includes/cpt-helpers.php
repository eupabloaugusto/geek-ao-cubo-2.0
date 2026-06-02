<?php
/**
 * CPT Query Helpers — Funções de Consulta Reutilizáveis
 *
 * Centraliza todas as queries customizadas dos CPTs em funções nomeadas.
 * Princípio: NUNCA escrever WP_Query inline nos templates — sempre usar helpers.
 *
 * Benefícios:
 *  - Um único ponto para otimizar queries (ex: adicionar 'no_found_rows' => true)
 *  - Fácil de testar e mockar
 *  - Evita esquecer wp_reset_postdata() nos templates
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// =========================================================================
// ANIME
// =========================================================================

/**
 * Retorna posts do CPT 'anime' com filtros opcionais.
 *
 * @param array $args {
 *     Argumentos opcionais.
 *
 *     @type int    $per_page    Quantidade de resultados. -1 para todos. Default 12.
 *     @type int    $page        Página atual (para paginação). Default 1.
 *     @type string $orderby     Campo de ordenação. Default 'date'.
 *     @type string $order       Direção: 'ASC' ou 'DESC'. Default 'DESC'.
 *     @type array  $generos     Array de slugs de gênero para filtrar.
 *     @type array  $status      Array de slugs de status_exibicao para filtrar.
 *     @type string $meta_key    Meta key para ordenação (ex: 'anime_nota_mal').
 *     @type string $meta_type   Tipo do meta para ordenação (ex: 'DECIMAL').
 * }
 * @return WP_Query
 */
function mm_query_animes( array $args = array() ) {
	$defaults = array(
		'per_page' => 12,
		'page'     => 1,
		'orderby'  => 'date',
		'order'    => 'DESC',
		'generos'  => array(),
		'status'   => array(),
		'meta_key' => '',
		'meta_type' => 'CHAR',
	);

	$args = wp_parse_args( $args, $defaults );

	$query_args = array(
		'post_type'           => 'anime',
		'post_status'         => 'publish',
		'posts_per_page'      => (int) $args['per_page'],
		'paged'               => (int) $args['page'],
		'orderby'             => sanitize_key( $args['orderby'] ),
		'order'               => in_array( strtoupper( $args['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $args['order'] ) : 'DESC',
		'no_found_rows'       => false, // Precisa de found_posts para paginação
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	// Filtro por gênero
	if ( ! empty( $args['generos'] ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'genero',
			'field'    => 'slug',
			'terms'    => array_map( 'sanitize_title', (array) $args['generos'] ),
		);
	}

	// Filtro por status de exibição
	if ( ! empty( $args['status'] ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'status_exibicao',
			'field'    => 'slug',
			'terms'    => array_map( 'sanitize_title', (array) $args['status'] ),
		);
	}

	// Operador lógico se ambos os filtros de taxonomia estiverem presentes
	if ( isset( $query_args['tax_query'] ) && count( $query_args['tax_query'] ) > 1 ) {
		$query_args['tax_query']['relation'] = 'AND';
	}

	// Ordenação por meta (ex: por nota)
	if ( ! empty( $args['meta_key'] ) ) {
		$query_args['meta_key']  = sanitize_key( $args['meta_key'] );
		$query_args['orderby']   = 'meta_value_num';
	}

	$query = new WP_Query( $query_args );

	// Importante: NÃO chamamos wp_reset_postdata() aqui porque o template
	// pode precisar do loop. Deve ser chamado após o template terminar o loop.

	return $query;
}


/**
 * Retorna os animes de uma temporada (ano + período).
 *
 * @param int    $temporada_id  ID do post de Temporada.
 * @param int    $per_page      Itens por página. -1 para todos.
 * @return WP_Post[]            Array de objetos WP_Post ou array vazio.
 */
function mm_get_animes_da_temporada( int $temporada_id, int $per_page = -1 ) {
	if ( ! $temporada_id ) {
		return array();
	}

	// Os animes estão armazenados no campo ACF 'temp_animes' como IDs
	$anime_ids = get_field( 'temp_animes', $temporada_id );

	if ( empty( $anime_ids ) ) {
		return array();
	}

	// Normaliza para array de IDs inteiros
	$ids = array_map( function( $item ) {
		return is_object( $item ) ? (int) $item->ID : (int) $item;
	}, (array) $anime_ids );

	$args = array(
		'post_type'           => 'anime',
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'post__in'            => $ids,
		'orderby'             => 'post__in', // Mantém a ordem definida no ACF
		'no_found_rows'       => true,       // Sem paginação = mais performático
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	$posts = get_posts( $args );
	wp_reset_postdata(); // get_posts() altera o global $post

	return $posts;
}


/**
 * Retorna posts do CPT 'anime' filtrados pela letra inicial do título.
 *
 * Usa posts_where para filtrar no banco sem carregar todos os posts.
 * Letra '#' filtra títulos que NÃO começam com A–Z (números e caracteres especiais).
 * Letra vazia retorna todos os animes (sem filtro de letra).
 *
 * Suporta filtros adicionais via $extra_args:
 *   - filtro_generos    (array)  slugs da taxonomia 'genero'.
 *   - filtro_status     (string) slug da taxonomia 'status_exibicao'.
 *   - filtro_idioma     (string) valor do ACF 'anime_idioma' (LIKE).
 *   - filtro_tipo_midia (string) valor do ACF 'anime_tipo' (=).
 *   - filtro_ordem      (string) 'populares' | 'recente' | 'alfabetica'.
 *
 * @param string $letra      Letra inicial (A–Z), '#' para não-alfabéticos, '' para todos.
 * @param array  $extra_args Args extras. Chaves 'filtro_*' são tratadas internamente.
 * @return WP_Query
 */
function mm_query_animes_por_letra( string $letra = '', array $extra_args = array() ) {
	$letra = strtoupper( trim( $letra ) );

	// ── Extrai os filtros especiais de $extra_args ────────────────────────
	$filtro_generos    = isset( $extra_args['filtro_generos'] )    ? array_filter( array_map( 'sanitize_title', (array) $extra_args['filtro_generos'] ) )    : array();
	$filtro_status     = isset( $extra_args['filtro_status'] )     ? sanitize_key( $extra_args['filtro_status'] )     : '';
	$filtro_idioma     = isset( $extra_args['filtro_idioma'] )     ? sanitize_text_field( $extra_args['filtro_idioma'] ) : '';
	$filtro_tipo_midia = isset( $extra_args['filtro_tipo_midia'] ) ? sanitize_key( $extra_args['filtro_tipo_midia'] )  : '';
	$filtro_ordem      = isset( $extra_args['filtro_ordem'] )      ? sanitize_key( $extra_args['filtro_ordem'] )      : '';
	$posts_per_page    = isset( $extra_args['posts_per_page'] )    ? (int) $extra_args['posts_per_page']              : -1;
	$paged             = isset( $extra_args['paged'] )             ? (int) $extra_args['paged']                       : 1;

	// Busca textual (aceita 's' ou 'busca' — catálogo usa 'busca' para não conflitar com search.php)
	$filtro_busca = '';
	if ( ! empty( $extra_args['s'] ) ) {
		$filtro_busca = sanitize_text_field( $extra_args['s'] );
	} elseif ( ! empty( $extra_args['busca'] ) ) {
		$filtro_busca = sanitize_text_field( $extra_args['busca'] );
	}

	unset(
		$extra_args['filtro_generos'],
		$extra_args['filtro_status'],
		$extra_args['filtro_idioma'],
		$extra_args['filtro_tipo_midia'],
		$extra_args['filtro_ordem'],
		$extra_args['posts_per_page'],
		$extra_args['paged'],
		$extra_args['s'],
		$extra_args['busca']
	);

	// ── Tratamento especial: Status "lancamento" ────────────────────────────────────────────
	// Em Lançamento = animes com episódios publicados nos últimos 30 dias
	//                + animes (filmes/OVAs) publicados nos últimos 30 dias
	// Usa transient de 30min para não fazer N+1 queries a cada page load.
	$lancamento_post_ids = null;
	if ( 'lancamento' === $filtro_status ) {
		$cache_key  = 'mm_catalogo_lancamento_ids';
		$cached_ids = get_transient( $cache_key );

		if ( false === $cached_ids ) {
			// 1. Episódios publicados nos últimos 30 dias
			$ep_ids = get_posts( array(
				'post_type'      => 'episodio',
				'posts_per_page' => 500,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'date_query'     => array(
					array( 'after' => '30 days ago', 'inclusive' => true ),
				),
			) );

			$ep_anime_ids = array();
			foreach ( $ep_ids as $ep_id ) {
				$meta = get_post_meta( $ep_id, 'ep_anime_relacionado', true );
				if ( is_array( $meta ) ) {
					foreach ( $meta as $anime_id ) {
						if ( $anime_id ) {
							$ep_anime_ids[] = (int) $anime_id;
						}
					}
				} elseif ( $meta ) {
					$ep_anime_ids[] = (int) $meta;
				}
			}

			// 2. Animes (filmes, OVAs) publicados diretamente nos últimos 30 dias
			$recent_anime_ids = get_posts( array(
				'post_type'      => 'anime',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'date_query'     => array(
					array( 'after' => '30 days ago', 'inclusive' => true ),
				),
			) );

			$cached_ids = array_values( array_unique( array_merge( $ep_anime_ids, $recent_anime_ids ) ) );
			set_transient( $cache_key, $cached_ids, 30 * MINUTE_IN_SECONDS );
		}

		// Se nenhum ID encontrado, garante resultado vazio (sem retornar tudo)
		$lancamento_post_ids = ! empty( $cached_ids ) ? $cached_ids : array( 0 );
		$filtro_status = ''; // Não usa tax_query para este caso
	}

	// ── Monta tax_query ───────────────────────────────────────────────────
	$tax_query = array();

	// Exclusão de conteúdo adulto (mesma regra de security-filters.php, aplicada às queries customizadas)
	$tax_query[] = array(
		'taxonomy' => 'genero',
		'field'    => 'slug',
		'terms'    => array( 'hentai', 'erotica', 'rx' ),
		'operator' => 'NOT IN',
	);

	if ( ! empty( $filtro_generos ) ) {
		$tax_query[] = array(
			'taxonomy' => 'genero',
			'field'    => 'slug',
			'terms'    => $filtro_generos,
			'operator' => 'IN',
		);
	}
	if ( ! empty( $filtro_status ) && 'todos' !== $filtro_status ) {
		$tax_query[] = array(
			'taxonomy' => 'status_exibicao',
			'field'    => 'slug',
			'terms'    => $filtro_status,
			'operator' => 'IN',
		);
	}
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	// ── Monta meta_query ──────────────────────────────────────────────────
	$meta_query    = array();
	$query_post_type = 'anime'; // padrão; muda para 'manga' se filtro_tipo_midia === 'manga'

	if ( ! empty( $filtro_idioma ) && 'todos' !== $filtro_idioma ) {
		if ( 'legendado' === $filtro_idioma ) {
			// Inclui posts sem anime_idioma definido (o padrão do sistema é 'legendado')
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'anime_idioma',
					'value'   => 'legendado',
					'compare' => 'LIKE',
				),
				array(
					'key'     => 'anime_idioma',
					'compare' => 'NOT EXISTS',
				),
			);
		} else {
			$meta_query[] = array(
				'key'     => 'anime_idioma',
				'value'   => $filtro_idioma,
				'compare' => 'LIKE',
			);
		}
	}
	if ( ! empty( $filtro_tipo_midia ) && 'todos' !== $filtro_tipo_midia ) {
		if ( 'manga' === $filtro_tipo_midia ) {
			// Mangá é um CPT separado — troca o post_type da query
			$query_post_type = 'manga';
			// Limpa filtros de idioma (campo não existe no CPT manga)
			$meta_query = array();
		} elseif ( 'serie' === $filtro_tipo_midia ) {
			$tipos_mapeados = array( 'TV', 'ONA' );
		} elseif ( 'filme' === $filtro_tipo_midia ) {
			$tipos_mapeados = array( 'Movie' );
		} else {
			// OVA, Special — coincide diretamente com os tipos do Jikan
			$tipos_mapeados = array( $filtro_tipo_midia );
		}
		if ( isset( $tipos_mapeados ) ) {
			$meta_query[] = array(
				'key'     => 'anime_tipo',
				'value'   => $tipos_mapeados,
				'compare' => 'IN',
			);
		}
	}
	if ( count( $meta_query ) > 1 ) {
		$meta_query['relation'] = 'AND';
	}

	// ── Mapeia ordenação ──────────────────────────────────────────────────
	// IMPORTANTE: 'populares' usa named meta_query clause + LEFT JOIN para não excluir
	// posts que ainda não têm 'anime_membros' sincronizado (evita resultado vazio).
	$orderby = 'title';
	$order   = 'ASC';
	if ( 'populares' === $filtro_ordem ) {
		// Clauses OR: posts com e sem o campo são incluídos (LEFT JOIN em vez de INNER JOIN)
		$membros_or = array(
			'relation'       => 'OR',
			'membros_clause' => array(
				'key'     => 'anime_membros',
				'type'    => 'NUMERIC',
				'compare' => 'EXISTS',
			),
			array(
				'key'     => 'anime_membros',
				'compare' => 'NOT EXISTS',
			),
		);
		if ( empty( $meta_query ) ) {
			$meta_query = $membros_or;
		} else {
			$meta_query = array(
				'relation' => 'AND',
				$meta_query,
				$membros_or,
			);
		}
		// orderby como array referencia a named clause — posts sem o campo ficam no final
		$orderby = array( 'membros_clause' => 'DESC', 'title' => 'ASC' );
		$order   = 'DESC';
	} elseif ( 'recente' === $filtro_ordem ) {
		$orderby = 'date';
		$order   = 'DESC';
	}

	$no_found_rows = ( -1 === $posts_per_page ) ? true : false;

	$query_args_base = array(
		'post_type'              => $query_post_type,
		'post_status'            => 'publish',
		'posts_per_page'         => $posts_per_page,
		'paged'                  => $paged,
		'orderby'                => $orderby,
		'order'                  => $order,
		'no_found_rows'          => $no_found_rows,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);
	if ( ! empty( $tax_query ) ) {
		$query_args_base['tax_query'] = $tax_query;
	}
	if ( ! empty( $meta_query ) ) {
		$query_args_base['meta_query'] = $meta_query;
	}
	// Nota: meta_key removido — populares agora usa named meta_query clause (LEFT JOIN)
	if ( ! empty( $filtro_busca ) ) {
		$query_args_base['s'] = $filtro_busca;
	}
	// Restrição de IDs para status 'lancamento' (query computada, não taxonômica)
	if ( null !== $lancamento_post_ids ) {
		$query_args_base['post__in'] = $lancamento_post_ids;
	}

	// ── Sem letra: constrói query diretamente (sem posts_where) ──────────
	if ( '' === $letra ) {
		return new WP_Query( $query_args_base );
	}

	// ── Com letra: registra filtro temporário no posts_where ─────────────
	$filter_letra = $letra;

	$where_cb = function( $where, $wp_query ) use ( $filter_letra ) {
		global $wpdb;

		if ( $wp_query->get( 'mm_letra_filter' ) ) {
			if ( '#' === $filter_letra ) {
				$where .= " AND {$wpdb->posts}.post_title NOT REGEXP '^[A-Za-z]'";
			} else {
				$safe = esc_sql( $wpdb->esc_like( $filter_letra ) );
				$where .= " AND {$wpdb->posts}.post_title LIKE '{$safe}%'";
			}
		}

		return $where;
	};

	add_filter( 'posts_where', $where_cb, 10, 2 );

	$query_args = array_merge( $query_args_base, array(
		'mm_letra_filter' => true,
	) );

	$query = new WP_Query( $query_args );

	remove_filter( 'posts_where', $where_cb, 10 );

	return $query;
}


/**
 * Retorna um array com as letras iniciais que possuem ao menos 1 anime publicado.
 * Usado pela nav-alfabetica para desabilitar letras sem conteúdo.
 * Resultado cacheado em transient por 12 horas.
 *
 * @return array Array de letras em maiúsculo (ex: ['A', 'B', 'N', '#']).
 */
function mm_get_letras_ativas_catalogo() {
	$cache_key = 'mm_letras_ativas_catalogo';
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	global $wpdb;

	$results = $wpdb->get_col(
		"SELECT DISTINCT UPPER( LEFT( post_title, 1 ) )
		 FROM {$wpdb->posts}
		 WHERE post_type = 'anime'
		   AND post_status = 'publish'
		 ORDER BY 1 ASC"
	);

	$letras = array();

	foreach ( $results as $char ) {
		if ( preg_match( '/[A-Z]/', $char ) ) {
			$letras[] = $char;
		} else {
			// Números ou caracteres especiais → '#'
			if ( ! in_array( '#', $letras, true ) ) {
				$letras[] = '#';
			}
		}
	}

	// Cache por 12 horas (letras novas aparecem após importação)
	set_transient( $cache_key, $letras, 12 * HOUR_IN_SECONDS );

	return $letras;
}


/**
 * Retorna posts da mesma franquia baseados no título raiz.
 * Extrai o título base (antes de ':', '-' ou sufixos como Season/Part)
 * e busca no CPT informado, ordenando por ID do MAL.
 *
 * @param string $raw_title   Título completo do post atual.
 * @param string $post_type   CPT para buscar ('anime' ou 'manga').
 * @param int    $current_id  ID do post atual (para marcar is_current).
 * @param string $meta_key    Meta key do MAL ('anime_id_mal' ou 'manga_id_mal').
 * @return array              Array de itens da franquia.
 */
function mm_get_franchise_posts( string $raw_title, string $post_type, int $current_id, string $meta_key = 'anime_id_mal' ) {
	$base_title = preg_split('/[:\-]/', $raw_title)[0] ?? '';
	$base_title = trim(preg_replace('/(Season|Part|The Movie|OVA|\d+).*$/i', '', $base_title));

	if ( empty( $base_title ) ) {
		return array();
	}

	$franchise_query = new WP_Query(array(
		'post_type'      => $post_type,
		's'              => $base_title,
		'posts_per_page' => 100,
	));

	$franchise = array();

	if ( $franchise_query->have_posts() ) {
		while ( $franchise_query->have_posts() ) {
			$franchise_query->the_post();
			if ( stripos( get_the_title(), $base_title ) !== false ) {
				$franchise[] = array(
					'id'           => get_the_ID(),
					'title'        => get_the_title(),
					'permalink'    => get_permalink(),
					'anime_id_mal' => get_post_meta( get_the_ID(), $meta_key, true ),
					'is_current'   => ( get_the_ID() === $current_id )
				);
			}
		}
		wp_reset_postdata();
	}

	// Ordena por ID do MyAnimeList (ordem cronológica de lançamento)
	usort($franchise, function($a, $b) {
		return (int)$a['anime_id_mal'] <=> (int)$b['anime_id_mal'];
	});

	return $franchise;
}


// =========================================================================
// EPISÓDIO
// =========================================================================

/**
 * Retorna os episódios de um anime específico, ordenados por número.
 *
 * @param int  $anime_id    ID do post do Anime pai.
 * @param int  $per_page    Quantidade. -1 para todos. Default -1.
 * @param int  $page        Página atual para paginação. Default 1.
 * @return WP_Query
 */
function mm_query_episodios_do_anime( int $anime_id, int $per_page = -1, int $page = 1 ) {
	$query = new WP_Query( array(
		'post_type'          => 'episodio',
		'post_status'        => 'publish',
		'posts_per_page'     => $per_page,
		'paged'              => $page,
		'meta_key'           => 'ep_numero',
		'orderby'            => 'meta_value_num',
		'order'              => 'ASC',
		'no_found_rows'      => ( -1 === $per_page ), // Otimiza se não tiver paginação
		'meta_query'         => array(
			array(
				'key'     => 'ep_anime_relacionado',
				'value'   => $anime_id,
				'compare' => 'LIKE',
			),
		),
		'update_post_meta_cache' => true,
		'update_post_term_cache' => false, // Episódios não têm taxonomias
	) );

	return $query;
}


/**
 * Retorna o episódio mais recente de um anime.
 *
 * @param int $anime_id  ID do post do Anime.
 * @return WP_Post|null  Objeto WP_Post ou null se não encontrado.
 */
function mm_get_ultimo_episodio( int $anime_id ) {
	$posts = get_posts( array(
		'post_type'      => 'episodio',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => 'ep_numero',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'ep_anime_relacionado',
				'value'   => $anime_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			),
		),
	) );

	wp_reset_postdata();
	return ! empty( $posts ) ? $posts[0] : null;
}


// =========================================================================
// REVIEW
// =========================================================================

/**
 * Retorna as reviews de um anime específico.
 *
 * @param int $anime_id   ID do post do Anime.
 * @param int $per_page   Quantidade. Default 5.
 * @return WP_Query
 */
function mm_query_reviews_do_anime( int $anime_id, int $per_page = 5 ) {
	$query = new WP_Query( array(
		'post_type'          => 'review',
		'post_status'        => 'publish',
		'posts_per_page'     => $per_page,
		'orderby'            => 'date',
		'order'              => 'DESC',
		'no_found_rows'      => false,
		'meta_query'         => array(
			array(
				'key'     => 'review_anime_relacionado',
				'value'   => $anime_id,
				'compare' => 'LIKE',
			),
		),
		'update_post_meta_cache' => true,
		'update_post_term_cache' => false,
	) );

	return $query;
}


// =========================================================================
// TEMPORADA
// =========================================================================

/**
 * Retorna a temporada atual ou mais recente publicada.
 *
 * @return WP_Post|null
 */
function mm_get_temporada_atual() {
	$ano_atual     = (int) date( 'Y' );
	$mes_atual     = (int) date( 'n' );

	// Determina o período sazonal atual
	if ( $mes_atual >= 1 && $mes_atual <= 3 ) {
		$periodo = 'inverno';
	} elseif ( $mes_atual >= 4 && $mes_atual <= 6 ) {
		$periodo = 'primavera';
	} elseif ( $mes_atual >= 7 && $mes_atual <= 9 ) {
		$periodo = 'verao';
	} else {
		$periodo = 'outono';
	}

	$posts = get_posts( array(
		'post_type'      => 'temporada',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => 'temp_ano',
				'value'   => $ano_atual,
				'compare' => '=',
				'type'    => 'NUMERIC',
			),
			array(
				'key'     => 'temp_periodo',
				'value'   => $periodo,
				'compare' => '=',
			),
		),
	) );

	wp_reset_postdata();
	return ! empty( $posts ) ? $posts[0] : null;
}

// =========================================================================
// JIKAN API (MYANIMELIST) & YOUTUBE INTEGRATION (DYNAMIC TRANSIENTS CACHE)
// =========================================================================

/**
 * Extrai o ID do vídeo do YouTube de uma URL.
 * Suporta formatos padrão, share links, embeds e curtos.
 *
 * @param string $url URL do YouTube.
 * @return string     ID do vídeo ou string vazia.
 */
function mm_get_youtube_video_id( $url ) {
	if ( empty( $url ) ) {
		return '';
	}

	$pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
	if ( preg_match( $pattern, $url, $matches ) ) {
		return $matches[1];
	}

	return '';
}

/**
 * Handler genérico de consulta à API Jikan (MyAnimeList) com cache em Transients.
 * Cache expira em 24 horas. Evita rate limits (HTTP 429).
 *
 * @param int    $anime_mal_id ID do anime no MAL.
 * @param string $endpoint     Endpoint específico (ex: 'characters', 'recommendations', 'relations').
 * @return array               Dados decodificados ou array vazio em caso de erro.
 */
function mm_get_jikan_data( int $anime_mal_id, string $endpoint ) {
	if ( ! $anime_mal_id ) {
		return array();
	}

	$transient_key = 'mm_jikan_' . sanitize_key( $endpoint ) . '_' . $anime_mal_id;
	$cached_data   = get_transient( $transient_key );

	if ( false !== $cached_data ) {
		return $cached_data;
	}

	$api_url = "https://api.jikan.moe/v4/anime/{$anime_mal_id}/{$endpoint}";
	
	// Faz a chamada externa com timeout razoável
	$response = wp_remote_get( $api_url, array(
		'timeout'    => 10,
		'user-agent' => 'Geek ao Cubo WP Core/2.0.0',
	) );

	if ( is_wp_error( $response ) ) {
		// Evita martelar a API em caso de falha de rede/DNS.
		set_transient( $transient_key, array(), 10 * MINUTE_IN_SECONDS );
		return array();
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		// Rate limit: cacheia vazio por mais tempo para respeitar o provedor.
		if ( 429 === (int) $code ) {
			set_transient( $transient_key, array(), 30 * MINUTE_IN_SECONDS );
			return array();
		}

		// Erros variados (5xx/4xx): cache curto.
		set_transient( $transient_key, array(), 10 * MINUTE_IN_SECONDS );
		return array();
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( empty( $data ) || ! isset( $data['data'] ) ) {
		// Resposta inesperada: evita refetch imediato.
		set_transient( $transient_key, array(), 10 * MINUTE_IN_SECONDS );
		return array();
	}

	$result = $data['data'];

	// Salva em cache por 24 horas (86400 segundos)
	set_transient( $transient_key, $result, DAY_IN_SECONDS );

	return $result;
}

/**
 * Busca personagens e dubladores da Jikan API e os formata para os componentes.
 *
 * @param int $anime_mal_id ID no MAL.
 * @return array {
 *     @type array $characters  Formatado para secao-personagens.
 *     @type array $dubladores  Formatado para secao-dubladores.
 * }
 */
function mm_get_jikan_characters_and_staff( int $anime_mal_id ) {
	$raw_data = mm_get_jikan_data( $anime_mal_id, 'characters' );

	if ( empty( $raw_data ) ) {
		return array( 'characters' => array(), 'dubladores' => array() );
	}

	// Ordena os dados brutos da Jikan: Main antes de Supporting; desempate por favorites (popularidade MAL) DESC
	usort( $raw_data, function( $a, $b ) {
		$roleA = isset( $a['role'] ) ? $a['role'] : 'Supporting';
		$roleB = isset( $b['role'] ) ? $b['role'] : 'Supporting';
		if ( $roleA !== $roleB ) {
			return ( 'Main' === $roleA ) ? -1 : 1;
		}
		$favA = isset( $a['character']['favorites'] ) ? (int) $a['character']['favorites'] : 0;
		$favB = isset( $b['character']['favorites'] ) ? (int) $b['character']['favorites'] : 0;
		return $favB - $favA; // maior popularidade primeiro
	} );

	$characters = array();
	$dubladores  = array();

	// Função local para formatar nomes de "Sobrenome, Nome" para "Nome Sobrenome"
	$clean_name = function( $name ) {
		$parts = explode( ', ', $name );
		return ( count( $parts ) === 2 ) ? $parts[1] . ' ' . $parts[0] : $name;
	};

	foreach ( $raw_data as $item ) {
		if ( empty( $item['character'] ) ) {
			continue;
		}

		$char      = $item['character'];
		$role_raw  = isset( $item['role'] ) ? $item['role'] : 'Supporting';
		$role_pt   = ( 'Main' === $role_raw ) ? __( 'Protagonista', 'geek-ao-cubo' ) : __( 'Figurante', 'geek-ao-cubo' );
		$char_name = $clean_name( $char['name'] );
		$favorites = isset( $char['favorites'] ) ? (int) $char['favorites'] : 0;

		// 1. Mapeia personagem
		$characters[] = array(
			'name'       => $char_name,
			'name_kanji' => '', // Endpoint geral de personagens não traz kanji por padrão
			'image_url'  => isset( $char['images']['webp']['image_url'] ) ? $char['images']['webp']['image_url'] : ( isset( $char['images']['jpg']['image_url'] ) ? $char['images']['jpg']['image_url'] : '' ),
			'role'       => $role_pt,
			'url'        => isset( $char['mal_id'] ) ? site_url( '/personagem/' . $char['mal_id'] . '/' . sanitize_title( $char_name ) ) : ( isset( $char['url'] ) ? $char['url'] : '' ),
			'favorites'  => $favorites,
		);

		// 2. Mapeia dubladores (Voice Actors)
		// Prioriza Japonês (original) e Português (se houver dublagem brasileira cadastrada)
		if ( ! empty( $item['voice_actors'] ) ) {
			foreach ( $item['voice_actors'] as $va ) {
				$lang = isset( $va['language'] ) ? $va['language'] : '';
				if ( in_array( $lang, array( 'Japanese', 'Portuguese', 'Portuguese (BR)' ), true ) ) {
					$va_person = $va['person'];
					$lang_label = ( 'Japanese' === $lang ) ? __( 'Japonês', 'geek-ao-cubo' ) : __( 'Português (BR)', 'geek-ao-cubo' );
					
					$dubladores[] = array(
						'va_mal_id'      => isset( $va_person['mal_id'] ) ? (int) $va_person['mal_id'] : 0,
						'va_name'        => $clean_name( $va_person['name'] ),
						'va_image'       => isset( $va_person['images']['jpg']['image_url'] ) ? $va_person['images']['jpg']['image_url'] : '',
						'va_url'         => isset( $va_person['url'] ) ? $va_person['url'] : '',
						'va_language'    => $lang_label,
						'character_name' => $char_name,
						'episodios'      => 0, // Informação não disponível neste endpoint
						'ano_inicio'     => 0,
						'ano_fim'        => 0,
					);
				}
			}
		}
	}

	return array(
		'characters' => array_slice( $characters, 0, 300 ), // Top 300 personagens
		'dubladores' => array_slice( $dubladores, 0, 300 ), // Top 300 dubladores
	);
}

/**
 * Retorna o ID do post CPT 'anime' vinculado a um MAL ID.
 *
 * @param int $mal_id ID no MyAnimeList.
 * @return int Post ID ou 0 se não encontrado.
 */
function mm_get_anime_post_id_by_mal_id( int $mal_id ): int {
	if ( $mal_id <= 0 ) {
		return 0;
	}

	$local_query = new WP_Query( array(
		'post_type'              => 'anime',
		'posts_per_page'         => 1,
		'meta_query'             => array(
			array(
				'key'     => 'anime_id_mal',
				'value'   => $mal_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			),
		),
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	) );

	return ! empty( $local_query->posts ) ? (int) $local_query->posts[0] : 0;
}

/**
 * Detecta dublagem PT-BR nos dados brutos de personagens da Jikan.
 *
 * @param array $characters_data Resposta de /anime/{id}/characters.
 */
function mm_jikan_has_ptbr_dub( array $characters_data ): bool {
	foreach ( $characters_data as $item ) {
		if ( empty( $item['voice_actors'] ) || ! is_array( $item['voice_actors'] ) ) {
			continue;
		}

		foreach ( $item['voice_actors'] as $va ) {
			$lang = isset( $va['language'] ) ? $va['language'] : '';
			if ( in_array( $lang, array( 'Portuguese', 'Portuguese (BR)' ), true ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Resolve o slug persistido em anime_idioma a partir dos personagens Jikan.
 *
 * @param array $characters_data Resposta de /anime/{id}/characters.
 * @return string 'dublado' ou 'legendado'.
 */
function mm_resolve_anime_idioma_slug( array $characters_data ): string {
	return mm_jikan_has_ptbr_dub( $characters_data ) ? 'dublado' : 'legendado';
}

/**
 * Sincroniza postmeta anime_idioma com base nos dubladores Jikan.
 *
 * @param int        $mal_id           ID no MyAnimeList.
 * @param int        $post_id          Post ID opcional (lookup automático se 0).
 * @param array|null $characters_data  Dados brutos de characters; busca cache/API se null.
 * @return string Slug salvo ('dublado'|'legendado') ou string vazia em falha.
 */
function mm_sync_anime_idioma_meta( int $mal_id, int $post_id = 0, ?array $characters_data = null ): string {
	if ( $mal_id <= 0 ) {
		return '';
	}

	if ( $post_id <= 0 ) {
		$post_id = mm_get_anime_post_id_by_mal_id( $mal_id );
	}

	if ( $post_id <= 0 ) {
		return '';
	}

	if ( null === $characters_data ) {
		if ( class_exists( 'Jikan_API' ) ) {
			$characters_data = Jikan_API::get_anime_characters( $mal_id );
		}

		if ( empty( $characters_data ) ) {
			$characters_data = mm_get_jikan_data( $mal_id, 'characters' );
		}
	}

	$slug = ! empty( $characters_data )
		? mm_resolve_anime_idioma_slug( $characters_data )
		: 'legendado';

	update_post_meta( $post_id, 'anime_idioma', sanitize_key( $slug ) );

	return $slug;
}

/**
 * Tenta buscar localmente um anime cadastrado no WordPress com base no ID do MAL.
 *
 * @param int $mal_id ID no MyAnimeList.
 * @return array|false Array com 'image' e 'url', ou false se não encontrado.
 */
function mm_get_local_anime_by_mal_id( int $mal_id ) {
	$post_id = mm_get_anime_post_id_by_mal_id( $mal_id );

	if ( $post_id > 0 ) {
		return array(
			'url'   => get_permalink( $post_id ),
			'image' => get_the_post_thumbnail_url( $post_id, 'medium' ) ?: '',
		);
	}

	return false;
}

/**
 * Tenta buscar localmente um dublador cadastrado no WordPress com base no ID do MAL.
 *
 * @param int $mal_id ID no MyAnimeList.
 * @return array|false Array com 'url' e 'image', ou false se não encontrado.
 */
function mm_get_local_dublador_by_mal_id( int $mal_id ) {
	if ( ! $mal_id ) {
		return false;
	}

	$local_query = new WP_Query( array(
		'post_type'      => 'dublador',
		'posts_per_page' => 1,
		'meta_query'     => array(
			array(
				'key'     => 'dublador_id_mal',
				'value'   => $mal_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			),
		),
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	if ( ! empty( $local_query->posts ) ) {
		$post_id = $local_query->posts[0];
		return array(
			'url'   => get_permalink( $post_id ),
			'image' => get_the_post_thumbnail_url( $post_id, 'medium' ) ?: '',
		);
	}

	return false;
}

/**
 * Busca detalhes de um anime ou manga na Jikan API: imagem de capa e URL do trailer.
 * Cache de 7 dias em caso de sucesso; 15 minutos em caso de falha (auto-cura).
 *
 * @param int    $mal_id ID no MyAnimeList.
 * @param string $type   Tipo: 'anime' ou 'manga'.
 * @return array         Array com 'image', 'url' e 'trailer'.
 */
function mm_get_jikan_entry_details( int $mal_id, string $type = 'anime' ) {
	$type = ( 'manga' === strtolower( $type ) ) ? 'manga' : 'anime';
	$data_ret = array(
		'image'   => '',
		'url'     => "https://myanimelist.net/{$type}/{$mal_id}",
		'trailer' => '',
	);

	if ( ! $mal_id ) {
		return $data_ret;
	}

	$transient_key = 'mm_jikan_detail_' . $type . '_' . $mal_id;
	$cached        = get_transient( $transient_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$api_url  = "https://api.jikan.moe/v4/{$type}/{$mal_id}";
	$response = wp_remote_get( $api_url, array(
		'timeout'    => 8,
		'user-agent' => 'Geek ao Cubo WP Core/2.0.0',
	) );

	if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
		$body    = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $body, true );

		if ( ! empty( $decoded['data'] ) ) {
			$entry_data = $decoded['data'];

			// Imagem de capa
			$image_url = '';
			if ( ! empty( $entry_data['images']['webp']['image_url'] ) ) {
				$image_url = $entry_data['images']['webp']['image_url'];
			} elseif ( ! empty( $entry_data['images']['jpg']['image_url'] ) ) {
				$image_url = $entry_data['images']['jpg']['image_url'];
			}
			if ( $image_url ) {
				$data_ret['image'] = $image_url;
			}

			// Trailer do YouTube
			$trailer = isset( $entry_data['trailer'] ) ? $entry_data['trailer'] : array();
			if ( ! empty( $trailer['url'] ) ) {
				$data_ret['trailer'] = $trailer['url'];
			} elseif ( ! empty( $trailer['youtube_id'] ) ) {
				$data_ret['trailer'] = 'https://www.youtube.com/watch?v=' . $trailer['youtube_id'];
			}
		}

		// Sucesso: cache longo de 7 dias
		set_transient( $transient_key, $data_ret, 7 * DAY_IN_SECONDS );
	} else {
		// Falha de rede ou status não-200: cache curto para permitir auto-cura
		$http_code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		$fail_ttl  = ( 429 === $http_code ) ? 30 * MINUTE_IN_SECONDS : 15 * MINUTE_IN_SECONDS;
		set_transient( $transient_key, $data_ret, $fail_ttl );
	}

	return $data_ret;
}

/**
 * Busca trailers e PVs de um anime na Jikan API (/anime/{id}/videos).
 * Retorna somente itens do array `promo` que possuem youtube_id válido.
 * Cache: 7 dias (sucesso) | 15 min (falha/429).
 *
 * @param int $mal_id ID do anime no MyAnimeList.
 * @return array Array normalizado: [['id' => string, 'title' => string, 'thumb' => string], ...]
 */
function mm_get_jikan_videos( int $mal_id ): array {
	if ( ! $mal_id ) {
		return array();
	}

	$transient_key = 'mm_jikan_videos_' . $mal_id;
	$cached        = get_transient( $transient_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$api_url  = "https://api.jikan.moe/v4/anime/{$mal_id}/videos";
	$response = wp_remote_get( $api_url, array(
		'timeout'    => 8,
		'user-agent' => 'Geek ao Cubo WP Core/2.0.0',
	) );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		$http_code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		$fail_ttl  = ( 429 === $http_code ) ? 30 * MINUTE_IN_SECONDS : 15 * MINUTE_IN_SECONDS;
		set_transient( $transient_key, array(), $fail_ttl );
		return array();
	}

	$body    = wp_remote_retrieve_body( $response );
	$decoded = json_decode( $body, true );
	$promos  = isset( $decoded['data']['promo'] ) ? $decoded['data']['promo'] : array();

	$videos = array();
	foreach ( $promos as $item ) {
		$yt_id = isset( $item['trailer']['youtube_id'] ) ? trim( $item['trailer']['youtube_id'] ) : '';
		if ( empty( $yt_id ) ) {
			continue;
		}
		$thumb = '';
		if ( ! empty( $item['trailer']['images']['large_image_url'] ) ) {
			$thumb = $item['trailer']['images']['large_image_url'];
		} elseif ( ! empty( $item['trailer']['images']['image_url'] ) ) {
			$thumb = $item['trailer']['images']['image_url'];
		} else {
			$thumb = "https://img.youtube.com/vi/{$yt_id}/hqdefault.jpg";
		}
		$videos[] = array(
			'id'    => $yt_id,
			'title' => isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : 'Trailer',
			'thumb' => esc_url_raw( $thumb ),
		);
	}

	set_transient( $transient_key, $videos, 7 * DAY_IN_SECONDS );
	return $videos;
}

/**
 * Busca a lista de episódios de um anime na Jikan API (página 1, até 100 eps).
 * Usado como fallback quando não há CPTs 'episodio' locais importados.
 * Cache: 24 h (sucesso) | 15 min (falha/429).
 *
 * @param int $anime_mal_id ID do anime no MyAnimeList.
 * @param int $page         Página da listagem (padrão: 1).
 * @return array            Array de episódios com 'numero', 'titulo', 'data', 'filler', 'recap'.
 */
function mm_get_jikan_episodes( int $anime_mal_id, int $page = 1 ) {
	if ( ! $anime_mal_id ) {
		return array();
	}

	$transient_key = 'mm_jikan_episodes_' . $anime_mal_id . '_p' . $page;
	$cached        = get_transient( $transient_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$api_url  = "https://api.jikan.moe/v4/anime/{$anime_mal_id}/episodes?page={$page}";
	$response = wp_remote_get( $api_url, array(
		'timeout'    => 10,
		'user-agent' => 'Geek ao Cubo WP Core/2.0.0',
	) );

	if ( is_wp_error( $response ) ) {
		set_transient( $transient_key, array(), 15 * MINUTE_IN_SECONDS );
		return array();
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		$fail_ttl = ( 429 === $code ) ? 30 * MINUTE_IN_SECONDS : 15 * MINUTE_IN_SECONDS;
		set_transient( $transient_key, array(), $fail_ttl );
		return array();
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( empty( $data ) || ! isset( $data['data'] ) ) {
		set_transient( $transient_key, array(), 15 * MINUTE_IN_SECONDS );
		return array();
	}

	$episodes = array();
	foreach ( $data['data'] as $ep ) {
		$num          = isset( $ep['mal_id'] ) ? (int) $ep['mal_id'] : 0;
		$title_raw    = isset( $ep['title'] ) && ! empty( $ep['title'] ) ? $ep['title'] : '';
		$ep_title     = ! empty( $title_raw ) ? $title_raw : sprintf( __( 'Episódio %s', 'geek-ao-cubo' ), $num );
		$aired_raw    = isset( $ep['aired'] ) ? $ep['aired'] : '';
		$ep_data      = ! empty( $aired_raw ) ? date_i18n( 'd/m/Y', strtotime( $aired_raw ) ) : '';

		$episodes[] = array(
			'numero' => $num,
			'titulo' => $ep_title,
			'data'   => $ep_data,
			'filler' => isset( $ep['filler'] ) ? (bool) $ep['filler'] : false,
			'recap'  => isset( $ep['recap'] )  ? (bool) $ep['recap']  : false,
		);
	}

	set_transient( $transient_key, $episodes, DAY_IN_SECONDS );

	return $episodes;
}

/**
 * Busca animes relacionados da Jikan API e os formata para secao-relacionados.
 *
 * @param int $anime_mal_id ID no MAL.
 * @return array
 */
function mm_get_jikan_relations( int $anime_mal_id ) {
	$raw_data = mm_get_jikan_data( $anime_mal_id, 'relations' );

	if ( empty( $raw_data ) ) {
		return array();
	}

	$items = array();

	// Tradução amigável dos tipos de relação do MAL para Português
	$relations_translation = array(
		'Adaptation'  => __( 'Adaptação', 'geek-ao-cubo' ),
		'Prequel'     => __( 'Prequel (Anterior)', 'geek-ao-cubo' ),
		'Sequel'      => __( 'Sequência', 'geek-ao-cubo' ),
		'Parent story' => __( 'História Pai', 'geek-ao-cubo' ),
		'Side story'  => __( 'História Paralela', 'geek-ao-cubo' ),
		'Spin-off'    => __( 'Spin-off', 'geek-ao-cubo' ),
		'Alternative version' => __( 'Versão Alternativa', 'geek-ao-cubo' ),
		'Alternative setting' => __( 'Cenário Alternativo', 'geek-ao-cubo' ),
		'Summary'     => __( 'Resumo', 'geek-ao-cubo' ),
		'Character'   => __( 'História de Personagem', 'geek-ao-cubo' ),
		'Full story'  => __( 'História Completa', 'geek-ao-cubo' ),
		'Other'       => __( 'Outro', 'geek-ao-cubo' ),
	);

	foreach ( $raw_data as $group ) {
		$relation_raw = isset( $group['relation'] ) ? $group['relation'] : 'Other';
		$relation_pt  = isset( $relations_translation[ $relation_raw ] ) ? $relations_translation[ $relation_raw ] : $relation_raw;

		if ( ! empty( $group['entry'] ) ) {
			foreach ( $group['entry'] as $entry ) {
				$type   = isset( $entry['type'] ) ? strtolower( trim( $entry['type'] ) ) : 'anime';
				$mal_id = isset( $entry['mal_id'] ) ? (int) $entry['mal_id'] : 0;

				$anime_image = '';
				$anime_url   = '';

				if ( $mal_id > 0 ) {
					// 1. Tenta buscar localmente primeiro (rápido, sem requisição HTTP)
					$local_post = mm_get_local_anime_by_mal_id( $mal_id );
					if ( $local_post ) {
						$anime_url   = $local_post['url'];
						$anime_image = $local_post['image'];
					} else {
						// 2. Se não achar localmente, tenta obter da Jikan API com cache de 7 dias
						$jikan_details = mm_get_jikan_entry_details( $mal_id, $type );
						$anime_image   = $jikan_details['image'];
						$anime_url     = $jikan_details['url'];
					}
				}

				$items[] = array(
					'anime_title'   => esc_html( $entry['name'] ),
					'anime_image'   => $anime_image,
					'anime_url'     => $anime_url,
					'relation_type' => $relation_pt,
				);
			}
		}
	}

	return $items;
}

/**
 * Busca animes recomendados da Jikan API e os formata para secao-recomendacoes.
 *
 * @param int $anime_mal_id ID no MAL.
 * @return array
 */
function mm_get_jikan_recommendations( int $anime_mal_id ) {
	$raw_data = mm_get_jikan_data( $anime_mal_id, 'recommendations' );

	if ( empty( $raw_data ) ) {
		return array();
	}

	$recomendacoes = array();

	foreach ( $raw_data as $rec ) {
		if ( empty( $rec['entry'] ) ) {
			continue;
		}

		$entry = $rec['entry'];
		
		$recomendacoes[] = array(
			'anime_title' => esc_html( $entry['title'] ),
			'anime_image' => isset( $entry['images']['webp']['image_url'] ) ? $entry['images']['webp']['image_url'] : $entry['images']['jpg']['image_url'],
			'anime_url'   => '', // Evitamos mandar tráfego externo por padrão
			'rec_count'   => isset( $rec['votes'] ) ? (int) $rec['votes'] : 1,
		);
	}

	return array_slice( $recomendacoes, 0, 8 ); // Retorna no máximo as top 8 recomendações
}

/**
 * Retorna o post de Temporada ao qual o anime está associado.
 *
 * @param int $anime_id ID do post do Anime.
 * @return WP_Post|null
 */
function mm_get_temporada_do_anime( int $anime_id ) {
	if ( ! $anime_id ) {
		return null;
	}

	$posts = get_posts( array(
		'post_type'      => 'temporada',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'temp_animes',
				'value'   => '"' . $anime_id . '"', // ACF armazena arrays serializados de IDs
				'compare' => 'LIKE',
			),
		),
	) );

	wp_reset_postdata();
	
	if ( ! empty( $posts ) ) {
		return $posts[0];
	}

	// Se não achou por serialize, tenta busca direta por ID puro
	$posts = get_posts( array(
		'post_type'      => 'temporada',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'temp_animes',
				'value'   => $anime_id,
				'compare' => '=',
			),
		),
	) );

	wp_reset_postdata();

	return ! empty( $posts ) ? $posts[0] : null;
}


// =========================================================================
// GENERAL / EDITORIAL POSTS (Usados na Página Inicial)
// =========================================================================

/**
 * Retorna os posts marcados como destaque (categoria ou tag) ou os últimos posts.
 * Centraliza a query de Destaques Editoriais.
 *
 * @param int $per_page Qtd de posts. Default 4.
 * @return WP_Query
 */
function mm_query_posts_destaque( int $per_page = 4 ) {
	$args_query = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'tax_query'      => array(
			'relation' => 'OR',
			array(
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => 'destaque',
			),
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => 'destaque',
			),
		),
	);
	$query = new WP_Query( $args_query );

	// Fallback: se não encontrar com 'destaque', traz os últimos posts publicados
	if ( ! $query->have_posts() ) {
		$args_query_fallback = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
		);
		$query = new WP_Query( $args_query_fallback );
	}

	return $query;
}

/**
 * Retorna as últimas notícias/artigos publicados, excluindo IDs específicos.
 * Centraliza a query de Notícias Recentes da homepage.
 *
 * @param int   $per_page    Qtd de posts. Default 4.
 * @param array $exclude_ids IDs a serem excluídos da query.
 * @return WP_Query
 */
function mm_query_noticias_recentes( int $per_page = 4, array $exclude_ids = array() ) {
	$args_news = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'post__not_in'   => array_map( 'intval', $exclude_ids ),
	);
	return new WP_Query( $args_news );
}

/**
 * Retorna os últimos episódios publicados de todos os animes.
 * Centraliza a query de Novos Episódios da homepage.
 *
 * @param int $per_page Qtd de episódios. Default 10.
 * @return WP_Query
 */
function mm_query_recent_episodios( int $per_page = 10 ) {
	$args_eps = array(
		'post_type'      => 'episodio',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	return new WP_Query( $args_eps );
}

/**
 * Busca e mapeia personagens de mangá da API Jikan.
 * A estrutura é adaptada para encaixar perfeitamente no `card-personagem`.
 *
 * @param int $manga_mal_id ID no MAL.
 * @return array Array de personagens mapeados.
 */
function mm_get_jikan_manga_characters( int $manga_mal_id ) {
	$raw_data = Jikan_API::get_manga_characters( $manga_mal_id );

	if ( empty( $raw_data ) ) {
		return array();
	}

	usort( $raw_data, function( $a, $b ) {
		$roleA = isset( $a['role'] ) ? $a['role'] : 'Supporting';
		$roleB = isset( $b['role'] ) ? $b['role'] : 'Supporting';
		if ( $roleA !== $roleB ) {
			return ( 'Main' === $roleA ) ? -1 : 1;
		}
		$favA = isset( $a['character']['favorites'] ) ? (int) $a['character']['favorites'] : 0;
		$favB = isset( $b['character']['favorites'] ) ? (int) $b['character']['favorites'] : 0;
		return $favB - $favA;
	} );

	$characters = array();

	$clean_name = function( $name ) {
		$parts = explode( ', ', $name );
		return ( count( $parts ) === 2 ) ? $parts[1] . ' ' . $parts[0] : $name;
	};

	foreach ( $raw_data as $item ) {
		if ( empty( $item['character'] ) ) {
			continue;
		}

		$char      = $item['character'];
		$role_raw  = isset( $item['role'] ) ? $item['role'] : 'Supporting';
		$role_pt   = ( 'Main' === $role_raw ) ? __( 'Protagonista', 'geek-ao-cubo' ) : __( 'Figurante', 'geek-ao-cubo' );
		$char_name = $clean_name( $char['name'] );
		$favorites = isset( $char['favorites'] ) ? (int) $char['favorites'] : 0;

		$characters[] = array(
			'name'       => $char_name,
			'name_kanji' => '',
			'image_url'  => isset( $char['images']['webp']['image_url'] ) ? $char['images']['webp']['image_url'] : ( isset( $char['images']['jpg']['image_url'] ) ? $char['images']['jpg']['image_url'] : '' ),
			'role'       => $role_pt,
			'url'        => isset( $char['mal_id'] ) ? site_url( '/personagem/' . $char['mal_id'] . '/' . sanitize_title( $char_name ) ) : ( isset( $char['url'] ) ? $char['url'] : '' ),
			'favorites'  => $favorites,
		);
	}

	return $characters;
}

/**
 * Busca mangás recomendados da Jikan API e os formata para secao-recomendacoes.
 *
 * @param int $manga_mal_id ID no MAL.
 * @return array
 */
function mm_get_jikan_manga_recommendations( int $manga_mal_id ) {
	$raw_data = Jikan_API::get_manga_recommendations( $manga_mal_id );

	if ( empty( $raw_data ) ) {
		return array();
	}

	$recomendacoes = array();

	foreach ( $raw_data as $rec ) {
		if ( empty( $rec['entry'] ) ) {
			continue;
		}

		$entry = $rec['entry'];
		
		$recomendacoes[] = array(
			'anime_title' => esc_html( $entry['title'] ),
			'anime_image' => isset( $entry['images']['webp']['image_url'] ) ? $entry['images']['webp']['image_url'] : ( isset( $entry['images']['jpg']['image_url'] ) ? $entry['images']['jpg']['image_url'] : '' ),
			'anime_url'   => '', // Evitamos mandar tráfego externo por padrão
			'rec_count'   => isset( $rec['votes'] ) ? (int) $rec['votes'] : 1,
		);
	}

	return array_slice( $recomendacoes, 0, 8 ); // Retorna no máximo as top 8 recomendações
}

