<?php
/**
 * CPT Query Helpers â FunÃ§Ãµes de Consulta ReutilizÃ¡veis
 *
 * Centraliza todas as queries customizadas dos CPTs em funÃ§Ãµes nomeadas.
 * PrincÃ­pio: NUNCA escrever WP_Query inline nos templates â sempre usar helpers.
 *
 * BenefÃ­cios:
 *  - Um Ãºnico ponto para otimizar queries (ex: adicionar 'no_found_rows' => true)
 *  - FÃ¡cil de testar e mockar
 *  - Evita esquecer wp_reset_postdata() nos templates
 *
 * @package vibe-animes
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
 *     @type int    $page        PÃ¡gina atual (para paginaÃ§Ã£o). Default 1.
 *     @type string $orderby     Campo de ordenaÃ§Ã£o. Default 'date'.
 *     @type string $order       DireÃ§Ã£o: 'ASC' ou 'DESC'. Default 'DESC'.
 *     @type array  $generos     Array de slugs de gÃªnero para filtrar.
 *     @type array  $status      Array de slugs de status_exibicao para filtrar.
 *     @type string $meta_key    Meta key para ordenaÃ§Ã£o (ex: 'anime_nota_mal').
 *     @type string $meta_type   Tipo do meta para ordenaÃ§Ã£o (ex: 'DECIMAL').
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
		'no_found_rows'       => false, // Precisa de found_posts para paginaÃ§Ã£o
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	// Filtro por gÃªnero
	if ( ! empty( $args['generos'] ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'genero',
			'field'    => 'slug',
			'terms'    => array_map( 'sanitize_title', (array) $args['generos'] ),
		);
	}

	// Filtro por status de exibiÃ§Ã£o
	if ( ! empty( $args['status'] ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'status_exibicao',
			'field'    => 'slug',
			'terms'    => array_map( 'sanitize_title', (array) $args['status'] ),
		);
	}

	// Operador lÃ³gico se ambos os filtros de taxonomia estiverem presentes
	if ( isset( $query_args['tax_query'] ) && count( $query_args['tax_query'] ) > 1 ) {
		$query_args['tax_query']['relation'] = 'AND';
	}

	// OrdenaÃ§Ã£o por meta (ex: por nota)
	if ( ! empty( $args['meta_key'] ) ) {
		$query_args['meta_key']  = sanitize_key( $args['meta_key'] );
		$query_args['orderby']   = 'meta_value_num';
	}

	$query = new WP_Query( $query_args );

	// Importante: NÃO chamamos wp_reset_postdata() aqui porque o template
	// pode precisar do loop. Deve ser chamado apÃ³s o template terminar o loop.

	return $query;
}


/**
 * Retorna os animes de uma temporada (ano + perÃ­odo).
 *
 * @param int    $temporada_id  ID do post de Temporada.
 * @param int    $per_page      Itens por pÃ¡gina. -1 para todos.
 * @return WP_Post[]            Array de objetos WP_Post ou array vazio.
 */
function mm_get_animes_da_temporada( int $temporada_id, int $per_page = -1 ) {
	if ( ! $temporada_id ) {
		return array();
	}

	// Os animes estÃ£o armazenados no campo ACF 'temp_animes' como IDs
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
		'orderby'             => 'post__in', // MantÃ©m a ordem definida no ACF
		'no_found_rows'       => true,       // Sem paginaÃ§Ã£o = mais performÃ¡tico
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	$posts = get_posts( $args );
	wp_reset_postdata(); // get_posts() altera o global $post

	return $posts;
}


/**
 * Retorna posts do CPT 'anime' filtrados pela letra inicial do tÃ­tulo.
 *
 * Usa posts_where para filtrar no banco sem carregar todos os posts.
 * Letra '#' filtra tÃ­tulos que NÃO comeÃ§am com AâZ (nÃºmeros e caracteres especiais).
 * Letra vazia retorna todos os animes (sem filtro de letra).
 *
 * Suporta filtros adicionais via $extra_args:
 *   - filtro_generos    (array)  slugs da taxonomia 'genero'.
 *   - filtro_status     (string) slug da taxonomia 'status_exibicao'.
 *   - filtro_idioma     (string) valor do ACF 'anime_idioma' (LIKE).
 *   - filtro_tipo_midia (string) valor do ACF 'anime_tipo' (=).
 *   - filtro_ordem      (string) 'populares' | 'recente' | 'alfabetica'.
 *
 * @param string $letra      Letra inicial (AâZ), '#' para nÃ£o-alfabÃ©ticos, '' para todos.
 * @param array  $extra_args Args extras. Chaves 'filtro_*' sÃ£o tratadas internamente.
 * @return WP_Query
 */
function mm_query_animes_por_letra( string $letra = '', array $extra_args = array() ) {
	$filtro_tipo_midia_check = isset( $extra_args['filtro_tipo_midia'] ) ? sanitize_key( $extra_args['filtro_tipo_midia'] ) : '';
	$query_post_type = ( 'manga' === $filtro_tipo_midia_check ) ? 'manga' : 'anime';

	$letra = strtoupper( trim( $letra ) );

	// ââ Extrai os filtros especiais de $extra_args ââââââââââââââââââââââââ
	$filtro_generos    = isset( $extra_args['filtro_generos'] )    ? array_filter( array_map( 'sanitize_title', (array) $extra_args['filtro_generos'] ) )    : array();
	$filtro_status     = isset( $extra_args['filtro_status'] )     ? sanitize_key( $extra_args['filtro_status'] )     : '';
	$filtro_idioma     = isset( $extra_args['filtro_idioma'] )     ? sanitize_text_field( $extra_args['filtro_idioma'] ) : '';
	$filtro_tipo_midia = isset( $extra_args['filtro_tipo_midia'] ) ? sanitize_key( $extra_args['filtro_tipo_midia'] )  : '';
	$filtro_ordem      = isset( $extra_args['filtro_ordem'] )      ? sanitize_key( $extra_args['filtro_ordem'] )      : '';
	$posts_per_page    = isset( $extra_args['posts_per_page'] )    ? (int) $extra_args['posts_per_page']              : -1;
	$paged             = isset( $extra_args['paged'] )             ? (int) $extra_args['paged']                       : 1;

	// Busca textual (aceita 's' ou 'busca' â catÃ¡logo usa 'busca' para nÃ£o conflitar com search.php)
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

	// ââ Tratamento especial: Status "lancamento" ââââââââââââââââââââââââââââââââââââââââââââ
	// Em LanÃ§amento = animes com episÃ³dios publicados nos Ãºltimos 30 dias
	//                + animes (filmes/OVAs) publicados nos Ãºltimos 30 dias
	// Usa transient de 30min para nÃ£o fazer N+1 queries a cada page load.
	$lancamento_post_ids = null;
	if ( 'lancamento' === $filtro_status ) {
		$cache_key  = 'mm_catalogo_lancamento_ids';
		$cached_ids = get_transient( $cache_key );

		if ( false === $cached_ids ) {
			// 1. EpisÃ³dios publicados nos Ãºltimos 30 dias
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

			// 2. Animes (filmes, OVAs) publicados diretamente nos Ãºltimos 30 dias
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
		$filtro_status = ''; // NÃ£o usa tax_query para este caso
	}

	// ââ Monta tax_query âââââââââââââââââââââââââââââââââââââââââââââââââââ
	$tax_query = array();

	// ExclusÃ£o de conteÃºdo adulto (mesma regra de security-filters.php, aplicada Ã s queries customizadas)
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
		$status_taxonomy = ( 'manga' === $query_post_type ) ? 'status_manga' : 'status_exibicao';
		$tax_query[] = array(
			'taxonomy' => $status_taxonomy,
			'field'    => 'slug',
			'terms'    => $filtro_status,
			'operator' => 'IN',
		);
	}
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	// ââ Monta meta_query ââââââââââââââââââââââââââââââââââââââââââââââââââ
	$meta_query    = array();
	// $query_post_type ja foi definido no inicio da funcao para a tax_query


	if ( ! empty( $filtro_idioma ) && 'todos' !== $filtro_idioma ) {
		if ( 'legendado' === $filtro_idioma ) {
			// Inclui posts sem anime_idioma definido (o padrÃ£o do sistema Ã© 'legendado')
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
			// MangÃ¡ Ã© um CPT separado â troca o post_type da query
			$query_post_type = 'manga';
			// Limpa filtros de idioma (campo nÃ£o existe no CPT manga)
			$meta_query = array();
		} elseif ( 'serie' === $filtro_tipo_midia ) {
			$tipos_mapeados = array( 'TV', 'ONA' );
		} elseif ( 'filme' === $filtro_tipo_midia ) {
			$tipos_mapeados = array( 'Movie' );
		} else {
			// OVA, Special â coincide diretamente com os tipos do Jikan
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

	// ââ Mapeia ordenaÃ§Ã£o ââââââââââââââââââââââââââââââââââââââââââââââââââ
	// IMPORTANTE: 'populares' usa named meta_query clause + LEFT JOIN para nÃ£o excluir
	// posts que ainda nÃ£o tÃªm 'anime_membros' sincronizado (evita resultado vazio).
	$orderby = 'title';
	$order   = 'ASC';
	if ( 'populares' === $filtro_ordem ) {
		$membros_meta_key = ( 'manga' === $query_post_type ) ? 'manga_membros' : 'anime_membros';
		// Clauses OR: posts com e sem o campo sÃ£o incluÃ­dos (LEFT JOIN em vez de INNER JOIN)
		$membros_or = array(
			'relation'       => 'OR',
			'membros_clause' => array(
				'key'     => $membros_meta_key,
				'type'    => 'NUMERIC',
				'compare' => 'EXISTS',
			),
			array(
				'key'     => $membros_meta_key,
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
		// orderby como array referencia a named clause â posts sem o campo ficam no final
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
	// Nota: meta_key removido â populares agora usa named meta_query clause (LEFT JOIN)
	if ( ! empty( $filtro_busca ) ) {
		$query_args_base['s'] = $filtro_busca;
	}
	// RestriÃ§Ã£o de IDs para status 'lancamento' (query computada, nÃ£o taxonÃ´mica)
	if ( null !== $lancamento_post_ids ) {
		$query_args_base['post__in'] = $lancamento_post_ids;
	}

	// ââ Busca textual em mÃºltiplos nÃ­veis (exata â nomes alternativos â fuzzy) ââ
	$search_filter_cb = null;
	$merged_ids      = null; // IDs mesclados de mÃºltiplas queries

	if ( ! empty( $filtro_busca ) ) {
		$search_term_raw = $filtro_busca;
		$meta_key_search = 'anime' === $query_post_type ? 'anime_nomes_busca' : 'manga_nomes_busca';
		$meta_key_sinopse = 'anime' === $query_post_type ? 'anime_sinopse' : 'manga_sinopse_manual';

		// NÃ­vel 1: busca nativa WP (post_title + post_content + sinopse via posts_search)
		$query_args_nivel1 = $query_args_base;
		$query_args_nivel1['s'] = $search_term_raw;
		$query_args_nivel1['fields'] = 'ids';
		$query_args_nivel1['posts_per_page'] = -1;
		$query_args_nivel1['no_found_rows'] = true;
		$query_args_nivel1['mm_search_meta'] = true;

		// Filtro posts_search para incluir sinopse
		$search_filter_cb = function( $search, $wp_query ) use ( $search_term_raw, $meta_key_sinopse ) {
			if ( ! $wp_query->get( 'mm_search_meta' ) ) {
				return $search;
			}
			global $wpdb;
			$s_words = array_filter( explode( ' ', $search_term_raw ) );
			$subqueries = array();
			foreach ( $s_words as $word ) {
				$like = '%' . $wpdb->esc_like( $word ) . '%';
				$subqueries[] = $wpdb->prepare( "meta_value LIKE %s", $like );
			}
			$subquery_str = implode( ' AND ', $subqueries );

			$subquery = "{$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '{$meta_key_sinopse}' AND ({$subquery_str}))";
			$search = preg_replace( '/\)\\)\\)\s*$/', ') OR ' . $subquery . '))', $search );
			return $search;
		};
		add_filter( 'posts_search', $search_filter_cb, 10, 2 );

		$query_nivel1 = new WP_Query( $query_args_nivel1 );
		$ids_nivel1   = $query_nivel1->posts ?: array();

		remove_filter( 'posts_search', $search_filter_cb, 10 );

		// NÃ­vel 2: busca em nomes alternativos (meta LIKE) â excluindo jÃ¡ encontrados
		$ids_nivel2 = array();
		if ( count( $ids_nivel1 ) < 5 ) {
			$query_args_nivel2 = $query_args_base;
			$query_args_nivel2['fields'] = 'ids';
			$query_args_nivel2['posts_per_page'] = -1;
			$query_args_nivel2['no_found_rows'] = true;
			$query_args_nivel2['post__not_in'] = $ids_nivel1;
			$meta_query_nomes = array( 'relation' => 'AND' );
			$s_words = array_filter( explode( ' ', $search_term_raw ) );
			foreach ( $s_words as $word ) {
				$meta_query_nomes[] = array(
					'key'     => $meta_key_search,
					'value'   => $word,
					'compare' => 'LIKE',
				);
			}
			$query_args_nivel2['meta_query'] = array( $meta_query_nomes );
			$query_nivel2 = new WP_Query( $query_args_nivel2 );
			$ids_nivel2   = $query_nivel2->posts ?: array();
		}

		// NÃ­vel 3: fuzzy â busca por similaridade de strings nos nomes alternativos
		$ids_nivel3 = array();
		if ( count( $ids_nivel1 ) + count( $ids_nivel2 ) < 5 && strlen( $search_term_raw ) >= 3 ) {
			$excluidos = array_merge( $ids_nivel1, $ids_nivel2 );

			// Busca todos os posts que possuem nomes alternativos, excluindo jÃ¡ encontrados
			$query_args_nivel3 = $query_args_base;
			unset( $query_args_nivel3['s'] ); // Remove busca nativa para nÃ£o restringir candidatos
			$query_args_nivel3['fields'] = 'ids';
			$query_args_nivel3['posts_per_page'] = 200; // Limite para performance
			$query_args_nivel3['no_found_rows'] = true;
			$query_args_nivel3['post__not_in'] = $excluidos;
			$query_args_nivel3['meta_query'] = array(
				array(
					'key'     => $meta_key_search,
					'compare' => 'EXISTS',
				),
			);
			$query_nivel3 = new WP_Query( $query_args_nivel3 );

			$termo_lower = mb_strtolower( $search_term_raw );
			$termo_palavras = preg_split( '/\s+/u', $termo_lower );

			foreach ( $query_nivel3->posts as $pid ) {
				$nomes = get_post_meta( $pid, $meta_key_search, true );
				if ( empty( $nomes ) ) {
					continue;
				}
				$nomes_lower = mb_strtolower( $nomes );
				$palavras_nomes = preg_split( '/\s+/u', $nomes_lower );

				$match = false;
				// Compara cada palavra do termo com cada palavra dos nomes alternativos
				foreach ( $termo_palavras as $tp ) {
					$tp_len = mb_strlen( $tp );
					if ( $tp_len < 3 ) {
						continue;
					}
					foreach ( $palavras_nomes as $pn ) {
						$pn_len = mb_strlen( $pn );
						if ( $pn_len < 3 ) {
							continue;
						}
						// Levenshtein distance (tolerÃ¢ncia proporcional ao comprimento)
						$max_len = max( $tp_len, $pn_len );
						$dist = levenshtein( $tp, $pn );
						$ratio = $dist / $max_len;

						// Se for a mesma palavra com prefixo/sufixo (ex: "Narut" vs "Naruto")
						if ( strpos( $pn, $tp ) === 0 || strpos( $tp, $pn ) === 0 ) {
							$match = true;
							break 2;
						}
						// TolerÃ¢ncia: <= 1 erro para palavras curtas, <= 30% para palavras longas
						if ( $max_len <= 5 && $dist <= 1 ) {
							$match = true;
							break 2;
						}
						if ( $max_len > 5 && $ratio <= 0.30 ) {
							$match = true;
							break 2;
						}
					}
				}
				if ( $match ) {
					$ids_nivel3[] = $pid;
				}
			}
			$ids_nivel3 = array_values( array_unique( $ids_nivel3 ) );
		}

		// Mescla IDs: exatos primeiro, depois nomes alternativos, depois fuzzy
		$merged_ids = array_values( array_unique( array_merge( $ids_nivel1, $ids_nivel2, $ids_nivel3 ) ) );

		// Se hÃ¡ filtro de lanÃ§amento, faz interseÃ§Ã£o com os IDs permitidos
		if ( null !== $lancamento_post_ids && ! empty( $merged_ids ) ) {
			$merged_ids = array_values( array_intersect( $merged_ids, $lancamento_post_ids ) );
		}

		// Se mesclou, substitui a query base para usar os IDs ordenados
		if ( ! empty( $merged_ids ) ) {
			unset( $query_args_base['s'] );
			$query_args_base['post__in'] = $merged_ids;
			$query_args_base['orderby']  = 'post__in';
			$query_args_base['order']    = 'ASC';
		} elseif ( null !== $lancamento_post_ids ) {
			// Busca sem resultados mas com filtro de lanÃ§amento ativo â mantÃ©m post__in vazio
			unset( $query_args_base['s'] );
			$query_args_base['post__in'] = array( 0 );
		}
	}

	// ââ Sem letra: constrÃ³i query diretamente (sem posts_where) ââââââââââ
	if ( '' === $letra ) {
		$query = new WP_Query( $query_args_base );
		return $query;
	}

	// ââ Com letra: registra filtro temporÃ¡rio no posts_where âââââââââââââ
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
		'mm_search_meta'  => ! empty( $filtro_busca ),
	) );

	$query = new WP_Query( $query_args );

	remove_filter( 'posts_where', $where_cb, 10 );
	if ( $search_filter_cb ) {
		remove_filter( 'posts_search', $search_filter_cb, 10 );
	}

	return $query;
}


/**
 * Retorna um array com as letras iniciais que possuem ao menos 1 anime publicado.
 * Usado pela nav-alfabetica para desabilitar letras sem conteÃºdo.
 * Resultado cacheado em transient por 12 horas.
 *
 * @return array Array de letras em maiÃºsculo (ex: ['A', 'B', 'N', '#']).
 */
function mm_get_letras_ativas_catalogo( $post_type = 'anime' ) {
	$cache_key = 'mm_letras_ativas_catalogo_' . $post_type;
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	global $wpdb;

	$results = $wpdb->get_col( $wpdb->prepare(
		"SELECT DISTINCT UPPER( LEFT( post_title, 1 ) )
		 FROM {$wpdb->posts}
		 WHERE post_type = %s
		   AND post_status = 'publish'
		 ORDER BY 1 ASC",
		$post_type
	) );

	$letras = array();

	foreach ( $results as $char ) {
		if ( preg_match( '/[A-Z]/', $char ) ) {
			$letras[] = $char;
		} else {
			// NÃºmeros ou caracteres especiais â '#'
			if ( ! in_array( '#', $letras, true ) ) {
				$letras[] = '#';
			}
		}
	}

	// Cache por 12 horas (letras novas aparecem apÃ³s importaÃ§Ã£o)
	set_transient( $cache_key, $letras, 12 * HOUR_IN_SECONDS );

	return $letras;
}


/**
 * Retorna um array com as letras iniciais que possuem ao menos 1 personagem.
 * Usado pela nav-alfabetica no catÃ¡logo de personagens.
 * Resultado cacheado em transient por 12 horas.
 *
 * @return array Array de letras em maiÃºsculo (ex: ['A', 'B', 'N', '#']).
 */
function mm_get_letras_ativas_personagens() {
	$cache_key = 'mm_letras_ativas_personagens';
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$letras_dict = array();
	$anime_ids = get_posts( array(
		'post_type'      => 'anime',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'anime_id_mal',
				'compare' => 'EXISTS',
			),
		),
	) );

	foreach ( $anime_ids as $anime_post_id ) {
		$mal_id = (int) get_post_meta( $anime_post_id, 'anime_id_mal', true );
		if ( ! $mal_id ) continue;

		$chars_raw = get_transient( 'jikan_anime_chars_' . $mal_id );
		if ( empty( $chars_raw ) || ! is_array( $chars_raw ) ) continue;

		foreach ( $chars_raw as $item ) {
			if ( empty( $item['character'] ) || empty( $item['character']['name'] ) ) continue;

			$name = $item['character']['name'];
			$parts = explode( ', ', $name );
			$titulo = ( count( $parts ) === 2 ) ? $parts[1] . ' ' . $parts[0] : $name;

			$inicial = strtoupper( mb_substr( $titulo, 0, 1 ) );
			if ( preg_match( '/[A-Z]/', $inicial ) ) {
				$letras_dict[ $inicial ] = true;
			} else {
				$letras_dict['#'] = true;
			}
		}
	}

	$letras = array_keys( $letras_dict );
	sort( $letras );

	// Garante que '#' esteja presente se necessÃ¡rio (ordenaÃ§Ã£o nÃ£o Ã© rÃ­gida aqui, pois a UI refaz a ordem)
	set_transient( $cache_key, $letras, 12 * HOUR_IN_SECONDS );

	return $letras;
}


/**
 * Retorna um array com as letras iniciais que possuem ao menos 1 dublador.
 * Usado pela nav-alfabetica no catÃ¡logo de dubladores.
 * Resultado cacheado em transient por 12 horas.
 *
 * @return array Array de letras em maiÃºsculo (ex: ['A', 'B', 'N', '#']).
 */
function mm_get_letras_ativas_dubladores_catalogo() {
	$cache_key = 'mm_letras_ativas_dubladores_catalogo_v2';
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$letras_dict = array();
	$anime_ids = get_posts( array(
		'post_type'      => 'anime',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'anime_id_mal',
				'compare' => 'EXISTS',
			),
		),
	) );

	foreach ( $anime_ids as $anime_post_id ) {
		$mal_id = (int) get_post_meta( $anime_post_id, 'anime_id_mal', true );
		if ( ! $mal_id ) continue;

		$chars_raw = get_transient( 'jikan_anime_chars_' . $mal_id );
		if ( empty( $chars_raw ) || ! is_array( $chars_raw ) ) continue;

		foreach ( $chars_raw as $item ) {
			if ( empty( $item['voice_actors'] ) || ! is_array( $item['voice_actors'] ) ) continue;

			foreach ( $item['voice_actors'] as $va ) {
				$lang = isset( $va['language'] ) ? $va['language'] : '';
				$allowed_langs = array( 'Japanese', 'Portuguese', 'Portuguese (BR)', 'English', 'Spanish', 'French', 'German' );
				if ( ! in_array( $lang, $allowed_langs, true ) ) {
					continue;
				}

				if ( empty( $va['person'] ) || empty( $va['person']['name'] ) ) continue;

				$name = $va['person']['name'];
				$parts = explode( ', ', $name );
				$titulo = ( count( $parts ) === 2 ) ? $parts[1] . ' ' . $parts[0] : $name;

				$inicial = strtoupper( mb_substr( $titulo, 0, 1 ) );
				if ( preg_match( '/[A-Z]/', $inicial ) ) {
					$letras_dict[ $inicial ] = true;
				} else {
					$letras_dict['#'] = true;
				}
			}
		}
	}

	$letras = array_keys( $letras_dict );
	sort( $letras );

	set_transient( $cache_key, $letras, 12 * HOUR_IN_SECONDS );

	return $letras;
}


/**
 * Retorna o nÃºmero total de personagens Ãºnicos publicados atravÃ©s dos animes.
 * Usado pelo hero do catÃ¡logo de personagens.
 * Resultado cacheado por 12 horas.
 *
 * @return int Total de personagens Ãºnicos.
 */
function mm_count_personagens_catalogo() {
	$cache_key = 'mm_count_personagens_catalogo';
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return (int) $cached;
	}

	$seen_mal_ids = array();
	$anime_ids = get_posts( array(
		'post_type'      => 'anime',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'anime_id_mal',
				'compare' => 'EXISTS',
			),
		),
	) );

	foreach ( $anime_ids as $anime_post_id ) {
		$mal_id = (int) get_post_meta( $anime_post_id, 'anime_id_mal', true );
		if ( ! $mal_id ) continue;

		$chars_raw = get_transient( 'jikan_anime_chars_' . $mal_id );
		if ( empty( $chars_raw ) || ! is_array( $chars_raw ) ) continue;

		foreach ( $chars_raw as $item ) {
			if ( empty( $item['character'] ) || empty( $item['character']['mal_id'] ) ) continue;
			$seen_mal_ids[ $item['character']['mal_id'] ] = true;
		}
	}

	$total = count( $seen_mal_ids );
	set_transient( $cache_key, $total, 12 * HOUR_IN_SECONDS );

	return $total;
}

/**
 * Retorna o nÃºmero total de dubladores Ãºnicos agregados atravÃ©s dos animes.
 * Usado pelo hero do catÃ¡logo de dubladores.
 * Resultado cacheado por 12 horas.
 *
 * @return int Total de dubladores Ãºnicos.
 */
function mm_count_dubladores_catalogo() {
	$cache_key = 'mm_count_dubladores_catalogo_v2';
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return (int) $cached;
	}

	$seen_mal_ids = array();
	$anime_ids = get_posts( array(
		'post_type'      => 'anime',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'anime_id_mal',
				'compare' => 'EXISTS',
			),
		),
	) );

	foreach ( $anime_ids as $anime_post_id ) {
		$mal_id = (int) get_post_meta( $anime_post_id, 'anime_id_mal', true );
		if ( ! $mal_id ) continue;

		$chars_raw = get_transient( 'jikan_anime_chars_' . $mal_id );
		if ( empty( $chars_raw ) || ! is_array( $chars_raw ) ) continue;

		foreach ( $chars_raw as $item ) {
			if ( empty( $item['voice_actors'] ) || ! is_array( $item['voice_actors'] ) ) continue;
			
			foreach ( $item['voice_actors'] as $va ) {
				$lang = isset( $va['language'] ) ? $va['language'] : '';
				$allowed_langs = array( 'Japanese', 'Portuguese', 'Portuguese (BR)', 'English', 'Spanish', 'French', 'German' );
				if ( ! in_array( $lang, $allowed_langs, true ) ) {
					continue;
				}
				
				if ( empty( $va['person'] ) || empty( $va['person']['mal_id'] ) ) continue;
				$seen_mal_ids[ $va['person']['mal_id'] ] = true;
			}
		}
	}

	$total = count( $seen_mal_ids );
	set_transient( $cache_key, $total, 12 * HOUR_IN_SECONDS );

	return $total;
}


/**
 * Retorna um array com as letras iniciais que possuem ao menos 1 dublador publicado.
 * Usado pela nav-alfabetica no archive-dublador.php.
 * Resultado cacheado em transient por 12 horas.
 *
 * @return array Array de letras em maiÃºsculo (ex: ['A', 'B', 'N', '#']).
 */
function mm_get_letras_ativas_dubladores() {
	$cache_key = 'mm_letras_ativas_dubladores';
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	global $wpdb;

	$results = $wpdb->get_col(
		"SELECT DISTINCT UPPER( LEFT( post_title, 1 ) )
		 FROM {$wpdb->posts}
		 WHERE post_type = 'dublador'
		   AND post_status = 'publish'
		 ORDER BY 1 ASC"
	);

	$letras = array();

	foreach ( $results as $char ) {
		if ( preg_match( '/[A-Z]/', $char ) ) {
			$letras[] = $char;
		} else {
			if ( ! in_array( '#', $letras, true ) ) {
				$letras[] = '#';
			}
		}
	}

	set_transient( $cache_key, $letras, 12 * HOUR_IN_SECONDS );

	return $letras;
}


/**
 * Retorna posts da mesma franquia baseados no tÃ­tulo raiz.
 * Extrai o tÃ­tulo base (antes de ':', '-' ou sufixos como Season/Part)
 * e busca no CPT informado, ordenando por ID do MAL.
 *
 * @param string $raw_title   TÃ­tulo completo do post atual.
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

	// Ordena por ID do MyAnimeList (ordem cronolÃ³gica de lanÃ§amento)
	usort($franchise, function($a, $b) {
		return (int)$a['anime_id_mal'] <=> (int)$b['anime_id_mal'];
	});

	return $franchise;
}


// =========================================================================
// EPISÃDIO
// =========================================================================

/**
 * Retorna os episÃ³dios de um anime especÃ­fico, ordenados por nÃºmero.
 *
 * @param int  $anime_id    ID do post do Anime pai.
 * @param int  $per_page    Quantidade. -1 para todos. Default -1.
 * @param int  $page        PÃ¡gina atual para paginaÃ§Ã£o. Default 1.
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
		'no_found_rows'      => ( -1 === $per_page ), // Otimiza se nÃ£o tiver paginaÃ§Ã£o
		'meta_query'         => array(
			array(
				'key'     => 'ep_anime_relacionado',
				'value'   => $anime_id,
				'compare' => 'LIKE',
			),
		),
		'update_post_meta_cache' => true,
		'update_post_term_cache' => false, // EpisÃ³dios nÃ£o tÃªm taxonomias
	) );

	return $query;
}


/**
 * Retorna o episÃ³dio mais recente de um anime.
 *
 * @param int $anime_id  ID do post do Anime.
 * @return WP_Post|null  Objeto WP_Post ou null se nÃ£o encontrado.
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
 * Retorna as reviews de um anime especÃ­fico.
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

	// Determina o perÃ­odo sazonal atual
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
 * Extrai o ID do vÃ­deo do YouTube de uma URL.
 * Suporta formatos padrÃ£o, share links, embeds e curtos.
 *
 * @param string $url URL do YouTube.
 * @return string     ID do vÃ­deo ou string vazia.
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
 * Handler genÃ©rico de consulta Ã  API Jikan (MyAnimeList) com cache em Transients.
 * Cache expira em 24 horas. Evita rate limits (HTTP 429).
 *
 * @param int    $anime_mal_id ID do anime no MAL.
 * @param string $endpoint     Endpoint especÃ­fico (ex: 'characters', 'recommendations', 'relations').
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
	
	// Faz a chamada externa com timeout razoÃ¡vel
	$response = wp_remote_get( $api_url, array(
		'timeout'    => 10,
		'user-agent' => 'Vibe Animes WP Core/2.0.0',
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

	// FunÃ§Ã£o local para formatar nomes de "Sobrenome, Nome" para "Nome Sobrenome"
	$clean_name = function( $name ) {
		$parts = explode( ', ', $name );
		return ( count( $parts ) === 2 ) ? $parts[1] . ' ' . $parts[0] : $name;
	};

	global $wpdb;
	$anime_slug = $wpdb->get_var( $wpdb->prepare("SELECT p.post_name FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE pm.meta_key = 'anime_id_mal' AND pm.meta_value = %d AND p.post_type = 'anime' LIMIT 1", $anime_mal_id) );
	if ( ! $anime_slug ) {
		$anime_slug = 'anime';
	}

	foreach ( $raw_data as $item ) {
		if ( empty( $item['character'] ) ) {
			continue;
		}

		$char      = $item['character'];
		$role_raw  = isset( $item['role'] ) ? $item['role'] : 'Supporting';
		$role_pt   = ( 'Main' === $role_raw ) ? __( 'Protagonista', 'vibe-animes' ) : __( 'Figurante', 'vibe-animes' );
		$char_name = $clean_name( $char['name'] );
		$favorites = isset( $char['favorites'] ) ? (int) $char['favorites'] : 0;

		// 1. Mapeia personagem
		$characters[] = array(
			'name'       => $char_name,
			'name_kanji' => '', // Endpoint geral de personagens nÃ£o traz kanji por padrÃ£o
			'image_url'  => isset( $char['images']['webp']['image_url'] ) ? $char['images']['webp']['image_url'] : ( isset( $char['images']['jpg']['image_url'] ) ? $char['images']['jpg']['image_url'] : '' ),
			'role'       => $role_pt,
			'url'        => isset( $char['mal_id'] ) ? site_url( '/' . $anime_slug . '/personagem/' . sanitize_title( $char_name ) . '/' ) : ( isset( $char['url'] ) ? $char['url'] : '' ),
			'favorites'  => $favorites,
		);

		// 2. Mapeia dubladores (Voice Actors)
		// Prioriza os idiomas listados abaixo, filtrando o resto da API Jikan
		if ( ! empty( $item['voice_actors'] ) ) {
			$allowed_langs = array( 'Japanese', 'Portuguese', 'Portuguese (BR)', 'English', 'Spanish', 'French', 'German' );
			$lang_labels = array(
				'Japanese'        => __( 'JaponÃªs', 'vibe-animes' ),
				'Portuguese'      => __( 'PortuguÃªs (BR)', 'vibe-animes' ),
				'Portuguese (BR)' => __( 'PortuguÃªs (BR)', 'vibe-animes' ),
				'English'         => __( 'InglÃªs', 'vibe-animes' ),
				'Spanish'         => __( 'Espanhol', 'vibe-animes' ),
				'French'          => __( 'FrancÃªs', 'vibe-animes' ),
				'German'          => __( 'AlemÃ£o', 'vibe-animes' ),
			);

			foreach ( $item['voice_actors'] as $va ) {
				$lang = isset( $va['language'] ) ? $va['language'] : '';
				if ( in_array( $lang, $allowed_langs, true ) ) {
					$va_person = $va['person'];
					$lang_label = isset( $lang_labels[ $lang ] ) ? $lang_labels[ $lang ] : $lang;
					
					$dubladores[] = array(
						'va_mal_id'      => isset( $va_person['mal_id'] ) ? (int) $va_person['mal_id'] : 0,
						'va_name'        => $clean_name( $va_person['name'] ),
						'va_image'       => isset( $va_person['images']['jpg']['image_url'] ) ? $va_person['images']['jpg']['image_url'] : '',
						'va_url'         => isset( $va_person['url'] ) ? $va_person['url'] : '',
						'va_language'    => $lang_label,
						'character_name' => $char_name,
						'episodios'      => 0, // InformaÃ§Ã£o nÃ£o disponÃ­vel neste endpoint
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
 * @return int Post ID ou 0 se nÃ£o encontrado.
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
 * @param int        $post_id          Post ID opcional (lookup automÃ¡tico se 0).
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
 * @return array|false Array com 'image' e 'url', ou false se nÃ£o encontrado.
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
 * @return array|false Array com 'url' e 'image', ou false se nÃ£o encontrado.
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
		'user-agent' => 'Vibe Animes WP Core/2.0.0',
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
		// Falha de rede ou status nÃ£o-200: cache curto para permitir auto-cura
		$http_code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		$fail_ttl  = ( 429 === $http_code ) ? 30 * MINUTE_IN_SECONDS : 15 * MINUTE_IN_SECONDS;
		set_transient( $transient_key, $data_ret, $fail_ttl );
	}

	return $data_ret;
}

/**
 * Busca trailers e PVs de um anime na Jikan API (/anime/{id}/videos).
 * Retorna somente itens do array `promo` que possuem youtube_id vÃ¡lido.
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
		'user-agent' => 'Vibe Animes WP Core/2.0.0',
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
		$yt_id = $item['trailer']['youtube_id'] ?? '';
		if ( empty( $yt_id ) && ! empty( $item['trailer']['embed_url'] ) ) {
			preg_match( '/embed\/([a-zA-Z0-9_-]+)/', $item['trailer']['embed_url'], $matches );
			$yt_id = $matches[1] ?? '';
		}
		if ( empty( $yt_id ) && ! empty( $item['trailer']['url'] ) ) {
			preg_match( '/[?&]v=([a-zA-Z0-9_-]+)/', $item['trailer']['url'], $matches );
			$yt_id = $matches[1] ?? '';
		}

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
 * Busca a lista de episÃ³dios de um anime na Jikan API (pÃ¡gina 1, atÃ© 100 eps).
 * Usado como fallback quando nÃ£o hÃ¡ CPTs 'episodio' locais importados.
 * Cache: 24 h (sucesso) | 15 min (falha/429).
 *
 * @param int $anime_mal_id ID do anime no MyAnimeList.
 * @param int $page         PÃ¡gina da listagem (padrÃ£o: 1).
 * @return array            Array de episÃ³dios com 'numero', 'titulo', 'data', 'filler', 'recap'.
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
		'user-agent' => 'Vibe Animes WP Core/2.0.0',
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
		$ep_title     = ! empty( $title_raw ) ? $title_raw : sprintf( __( 'EpisÃ³dio %s', 'vibe-animes' ), $num );
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

	// TraduÃ§Ã£o amigÃ¡vel dos tipos de relaÃ§Ã£o do MAL para PortuguÃªs
	$relations_translation = array(
		'Adaptation'  => __( 'AdaptaÃ§Ã£o', 'vibe-animes' ),
		'Prequel'     => __( 'Prequel (Anterior)', 'vibe-animes' ),
		'Sequel'      => __( 'SequÃªncia', 'vibe-animes' ),
		'Parent story' => __( 'HistÃ³ria Pai', 'vibe-animes' ),
		'Side story'  => __( 'HistÃ³ria Paralela', 'vibe-animes' ),
		'Spin-off'    => __( 'Spin-off', 'vibe-animes' ),
		'Alternative version' => __( 'VersÃ£o Alternativa', 'vibe-animes' ),
		'Alternative setting' => __( 'CenÃ¡rio Alternativo', 'vibe-animes' ),
		'Summary'     => __( 'Resumo', 'vibe-animes' ),
		'Character'   => __( 'HistÃ³ria de Personagem', 'vibe-animes' ),
		'Full story'  => __( 'HistÃ³ria Completa', 'vibe-animes' ),
		'Other'       => __( 'Outro', 'vibe-animes' ),
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
					// 1. Tenta buscar localmente primeiro (rÃ¡pido, sem requisiÃ§Ã£o HTTP)
					$local_post = mm_get_local_anime_by_mal_id( $mal_id );
					if ( $local_post ) {
						$anime_url   = $local_post['url'];
						$anime_image = $local_post['image'];
					} else {
						// 2. Se nÃ£o achar localmente, tenta obter da Jikan API com cache de 7 dias
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
			'anime_url'   => '', // Evitamos mandar trÃ¡fego externo por padrÃ£o
			'rec_count'   => isset( $rec['votes'] ) ? (int) $rec['votes'] : 1,
		);
	}

	return array_slice( $recomendacoes, 0, 8 ); // Retorna no mÃ¡ximo as top 8 recomendaÃ§Ãµes
}

/**
 * Retorna o post de Temporada ao qual o anime estÃ¡ associado.
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

	// Se nÃ£o achou por serialize, tenta busca direta por ID puro
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
// GENERAL / EDITORIAL POSTS (Usados na PÃ¡gina Inicial)
// =========================================================================

/**
 * Retorna os posts marcados como destaque (categoria ou tag) ou os Ãºltimos posts.
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

	// Fallback: se nÃ£o encontrar com 'destaque', traz os Ãºltimos posts publicados
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
 * Retorna as Ãºltimas notÃ­cias/artigos publicados, excluindo IDs especÃ­ficos.
 * Centraliza a query de NotÃ­cias Recentes da homepage.
 *
 * @param int   $per_page    Qtd de posts. Default 4.
 * @param array $exclude_ids IDs a serem excluÃ­dos da query.
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
 * Retorna os Ãºltimos episÃ³dios publicados de todos os animes.
 * Centraliza a query de Novos EpisÃ³dios da homepage.
 *
 * @param int $per_page Qtd de episÃ³dios. Default 10.
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
 * Busca e mapeia personagens de mangÃ¡ da API Jikan.
 * A estrutura Ã© adaptada para encaixar perfeitamente no `card-personagem`.
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

	global $wpdb;
	$manga_slug = $wpdb->get_var( $wpdb->prepare("SELECT p.post_name FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE pm.meta_key = 'manga_id_mal' AND pm.meta_value = %d AND p.post_type = 'manga' LIMIT 1", $manga_mal_id) );
	if ( ! $manga_slug ) {
		$manga_slug = 'manga';
	}

	foreach ( $raw_data as $item ) {
		if ( empty( $item['character'] ) ) {
			continue;
		}

		$char      = $item['character'];
		$role_raw  = isset( $item['role'] ) ? $item['role'] : 'Supporting';
		$role_pt   = ( 'Main' === $role_raw ) ? __( 'Protagonista', 'vibe-animes' ) : __( 'Figurante', 'vibe-animes' );
		$char_name = $clean_name( $char['name'] );
		$favorites = isset( $char['favorites'] ) ? (int) $char['favorites'] : 0;

		$characters[] = array(
			'name'       => $char_name,
			'name_kanji' => '',
			'image_url'  => isset( $char['images']['webp']['image_url'] ) ? $char['images']['webp']['image_url'] : ( isset( $char['images']['jpg']['image_url'] ) ? $char['images']['jpg']['image_url'] : '' ),
			'role'       => $role_pt,
			'url'        => isset( $char['mal_id'] ) ? site_url( '/' . $manga_slug . '/personagem/' . sanitize_title( $char_name ) . '/' ) : ( isset( $char['url'] ) ? $char['url'] : '' ),
			'favorites'  => $favorites,
		);
	}

	return $characters;
}

/**
 * Busca mangÃ¡s recomendados da Jikan API e os formata para secao-recomendacoes.
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
			'anime_url'   => '', // Evitamos mandar trÃ¡fego externo por padrÃ£o
			'rec_count'   => isset( $rec['votes'] ) ? (int) $rec['votes'] : 1,
		);
	}

	return array_slice( $recomendacoes, 0, 8 ); // Retorna no mÃ¡ximo as top 8 recomendaÃ§Ãµes
}


/**
 * Busca metadados de um personagem da Jikan API (gender, is_protagonista).
 * Resultado cacheado em transient por 30 dias.
 *
 * @param int $mal_id ID do personagem no MAL.
 * @return array Array com 'gender' e 'is_protagonista'.
 */
function mm_get_personagem_metadados( int $mal_id ) {
	$cache_key = 'mm_char_meta_' . $mal_id;
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$meta = array(
		'gender'         => '',
		'is_protagonista'=> false,
	);

	if ( class_exists( 'Jikan_API' ) ) {
		$data = Jikan_API::get_character_full( $mal_id );
		if ( ! empty( $data ) ) {
			// GÃªnero
			if ( ! empty( $data['gender'] ) ) {
				$meta['gender'] = sanitize_text_field( $data['gender'] );
			} elseif ( ! empty( $data['about'] ) ) {
				// Fallback: parse do campo about
				if ( preg_match( '/Gender:\s*(Male|Female)/i', $data['about'], $matches ) ) {
					$meta['gender'] = ucfirst( strtolower( $matches[1] ) );
				}
			}

			// Verifica se tem role Main em algum anime
			if ( ! empty( $data['anime'] ) ) {
				foreach ( $data['anime'] as $ap ) {
					if ( isset( $ap['role'] ) && 'Main' === $ap['role'] ) {
						$meta['is_protagonista'] = true;
						break;
					}
				}
			}
		}
	}

	// Cache por 30 dias
	set_transient( $cache_key, $meta, 30 * DAY_IN_SECONDS );

	return $meta;
}



/**
 * Retorna tags de SEO dinâmicas baseadas no CPT.
 * 
 * @param int $post_id
 * @return array
 */
function mm_get_dynamic_seo_tags( $post_id ) {
	$post_type = get_post_type( $post_id );
	$title     = get_the_title( $post_id );
	$tags      = array();

	// Função auxiliar para evitar links (Opção B)
	$add_tag = function( $name ) use ( &$tags ) {
		$tags[] = array( "name" => $name, "url" => "" );
	};

	switch ( $post_type ) {
		case "anime":
			$add_tag( "Assistir " . $title );
			$add_tag( $title . " online" );
			$add_tag( "Episódios de " . $title );
			$add_tag( $title . " dublado" );
			$add_tag( $title . " legendado" );
			break;
		case "manga":
			$add_tag( "Ler mangá " . $title );
			$add_tag( $title . " mangá online" );
			$add_tag( "Capítulos " . $title );
			$add_tag( "Mangá " . $title . " em português" );
			break;
		case "personagem":
			$add_tag( "Personagem " . $title );
			$add_tag( "Imagens de " . $title );
			$add_tag( "História " . $title );
			break;
		case "post":
			$add_tag( "Notícias " . $title );
			$add_tag( "Sobre " . $title );
			break;
		case "dublador":
			$add_tag( "Dublador " . $title );
			$add_tag( "Vozes de " . $title );
			$add_tag( "Trabalhos de " . $title );
			break;
	}

	return $tags;
}


/**
 * ---------------------------------------------------------------------
 * TASK S0.3: Helper de Breadcrumbs para o Schema Markup
 * ---------------------------------------------------------------------
 */
function mm_get_breadcrumbs() {
	if ( is_front_page() || is_home() ) {
		return array();
	}

	$crumbs   = array();
	$crumbs[] = array(
		'name' => __( 'Home', 'vibe-animes' ),
		'url'  => home_url( '/' ),
	);

	$is_char = (bool) get_query_var( 'personagem_id' );
	if ( is_singular() || $is_char ) {
		$post_type = $is_char ? 'personagem' : get_post_type();
		$post_id   = $is_char ? (int) get_query_var( 'personagem_id' ) : get_the_ID();

		if ( $is_char ) {
			$title = 'Character';
			if ( class_exists( 'Jikan_API' ) ) {
				$jikan_data = Jikan_API::get_character_full( $post_id );
				if ( ! empty( $jikan_data['name'] ) ) {
					$title = $jikan_data['name'];
				}
			}
		} else {
			$title = get_the_title( $post_id );
		}

		switch ( $post_type ) {
			case 'anime':
				$crumbs[] = array(
					'name' => __( 'Animes', 'vibe-animes' ),
					'url'  => home_url( '/catalogo-de-animes/' ),
				);
				break;
			case 'manga':
				$crumbs[] = array(
					'name' => __( 'Mangás', 'vibe-animes' ),
					'url'  => home_url( '/catalogo-de-animes/?tipo_midia=manga' ),
				);
				break;
			case 'dublador':
				$crumbs[] = array(
					'name' => __( 'Animes', 'vibe-animes' ),
					'url'  => home_url( '/catalogo-de-animes/' ),
				);
				if ( function_exists( 'mm_get_anime_slug_for_dublador' ) ) {
					$anime_slug = mm_get_anime_slug_for_dublador( $post_id );
					if ( $anime_slug && $anime_slug !== 'anime' ) {
						$anime_post = get_page_by_path( $anime_slug, OBJECT, 'anime' );
						if ( $anime_post ) {
							$crumbs[] = array(
								'name' => get_the_title( $anime_post->ID ),
								'url'  => vibe_get_multilingual_permalink( $anime_post->ID ),
							);
						}
					}
				}
				$crumbs[] = array(
					'name' => __( 'Dubladores', 'vibe-animes' ),
					'url'  => home_url( '/dubladores/' ),
				);
				break;
			case 'personagem':
				$parent_slug = get_query_var( 'anime_slug' );
				$parent_post = null;
				if ( $parent_slug ) {
					$parent_post = get_page_by_path( $parent_slug, OBJECT, array( 'anime', 'manga' ) );
				}

				if ( $parent_post ) {
					if ( 'manga' === $parent_post->post_type ) {
						$crumbs[] = array(
							'name' => __( 'Mangás', 'vibe-animes' ),
							'url'  => home_url( '/catalogo-de-animes/?tipo_midia=manga' ),
						);
					} else {
						$crumbs[] = array(
							'name' => __( 'Animes', 'vibe-animes' ),
							'url'  => home_url( '/catalogo-de-animes/' ),
						);
					}
					$crumbs[] = array(
						'name' => get_the_title( $parent_post->ID ),
						'url'  => vibe_get_multilingual_permalink( $parent_post->ID ),
					);
				}

				$crumbs[] = array(
					'name' => __( 'Personagens', 'vibe-animes' ),
					'url'  => home_url( '/personagens/' ),
				);
				break;
			case 'temporada':
				$crumbs[] = array(
					'name' => __( 'Animes', 'vibe-animes' ),
					'url'  => home_url( '/catalogo-de-animes/' ),
				);
				if ( function_exists( 'mm_get_anime_slug_for_temporada' ) ) {
					$anime_slug = mm_get_anime_slug_for_temporada( $post_id );
					if ( $anime_slug && $anime_slug !== 'anime' ) {
						$anime_post = get_page_by_path( $anime_slug, OBJECT, 'anime' );
						if ( $anime_post ) {
							$crumbs[] = array(
								'name' => get_the_title( $anime_post->ID ),
								'url'  => vibe_get_multilingual_permalink( $anime_post->ID ),
							);
						}
					}
				}
				break;
			case 'post':
				$crumbs[] = array(
					'name' => __( 'Artigos', 'vibe-animes' ),
					'url'  => home_url( '/publicacoes/' ),
				);
				break;
		}

		$crumbs[] = array(
			'name' => $title,
			'url'  => function_exists( 'vibe_get_multilingual_permalink' ) ? vibe_get_multilingual_permalink( $post_id ) : get_permalink( $post_id ),
		);
	} elseif ( is_post_type_archive() ) {
		$post_type = get_query_var( 'post_type' );
		$pt_obj    = get_post_type_object( $post_type );
		$crumbs[]  = array(
			'name' => $pt_obj->labels->name,
			'url'  => get_post_type_archive_link( $post_type ),
		);
	}

	return $crumbs;
}
