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
 * @package hello-elementor-child
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
				'compare' => '=',
				'type'    => 'NUMERIC',
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
				'compare' => '=',
				'type'    => 'NUMERIC',
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
		'timeout'    => 5,
		'user-agent' => 'Geek ao Cubo WP Core/2.0.0',
	) );

	if ( is_wp_error( $response ) ) {
		return array(); // Retorna vazio silenciosamente para não quebrar a página
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return array();
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( empty( $data ) || ! isset( $data['data'] ) ) {
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
		$role_pt   = ( 'Main' === $role_raw ) ? __( 'Principal', 'hello-elementor-child' ) : __( 'Secundário', 'hello-elementor-child' );
		$char_name = $clean_name( $char['name'] );

		// 1. Mapeia personagem
		$characters[] = array(
			'name'       => $char_name,
			'name_kanji' => '', // Endpoint geral de personagens não traz kanji por padrão
			'image_url'  => isset( $char['images']['webp']['image_url'] ) ? $char['images']['webp']['image_url'] : $char['images']['jpg']['image_url'],
			'role'       => $role_pt,
			'url'        => isset( $char['url'] ) ? $char['url'] : '',
		);

		// 2. Mapeia dubladores (Voice Actors)
		// Prioriza Japonês (original) e Português (se houver dublagem brasileira cadastrada)
		if ( ! empty( $item['voice_actors'] ) ) {
			foreach ( $item['voice_actors'] as $va ) {
				$lang = isset( $va['language'] ) ? $va['language'] : '';
				if ( in_array( $lang, array( 'Japanese', 'Portuguese' ), true ) ) {
					$va_person = $va['person'];
					$lang_label = ( 'Japanese' === $lang ) ? __( 'Japonês', 'hello-elementor-child' ) : __( 'Português (BR)', 'hello-elementor-child' );
					
					$dubladores[] = array(
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

	// Limita os arrays para não poluir visualmente a tela
	return array(
		'characters' => array_slice( $characters, 0, 12 ), // Top 12 personagens
		'dubladores' => array_slice( $dubladores, 0, 8 ),   // Top 8 dubladores
	);
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
		'Adaptation'  => __( 'Adaptação', 'hello-elementor-child' ),
		'Prequel'     => __( 'Prequel (Anterior)', 'hello-elementor-child' ),
		'Sequel'      => __( 'Sequência', 'hello-elementor-child' ),
		'Parent story' => __( 'História Pai', 'hello-elementor-child' ),
		'Side story'  => __( 'História Paralela', 'hello-elementor-child' ),
		'Spin-off'    => __( 'Spin-off', 'hello-elementor-child' ),
		'Alternative version' => __( 'Versão Alternativa', 'hello-elementor-child' ),
		'Alternative setting' => __( 'Cenário Alternativo', 'hello-elementor-child' ),
		'Summary'     => __( 'Resumo', 'hello-elementor-child' ),
		'Character'   => __( 'História de Personagem', 'hello-elementor-child' ),
		'Full story'  => __( 'História Completa', 'hello-elementor-child' ),
		'Other'       => __( 'Outro', 'hello-elementor-child' ),
	);

	foreach ( $raw_data as $group ) {
		$relation_raw = isset( $group['relation'] ) ? $group['relation'] : 'Other';
		$relation_pt  = isset( $relations_translation[ $relation_raw ] ) ? $relations_translation[ $relation_raw ] : $relation_raw;

		if ( ! empty( $group['entry'] ) ) {
			foreach ( $group['entry'] as $entry ) {
				// Só processa se for do tipo anime (podem vir mangás nas relações também, o que é ótimo!)
				$type = isset( $entry['type'] ) ? $entry['type'] : '';
				
				$items[] = array(
					'anime_title'   => esc_html( $entry['name'] ),
					'anime_image'   => '', // O endpoint de relations não retorna a imagem de capa, mas o atom imagem-capa tem fallback.
					'anime_url'     => '', // Não linkamos diretamente para o MAL externo para reter tráfego no blog. O usuário pode buscar no blog.
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

