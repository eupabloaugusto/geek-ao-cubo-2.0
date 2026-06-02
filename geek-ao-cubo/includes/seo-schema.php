<?php
/**
 * Includes: Módulo de SEO Estrutural (Schema Markup JSON-LD)
 *
 * Imprime dados estruturados sofisticados no cabeçalho das páginas singulares
 * para cada CPT do portal, aumentando o desempenho nos resultados orgânicos do Google.
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Task 5.3: Imprime no cabeçalho (wp_head) os esquemas JSON-LD apropriados.
 */
function mm_inject_seo_json_ld() {
	// Apenas em posts singulares
	if ( ! is_singular() ) {
		return;
	}

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );
	$schema    = array();

	switch ( $post_type ) {
		// ---------------------------------------------------------------------
		// CPT Anime -> TVSeries
		// ---------------------------------------------------------------------
		case 'anime':
			$mal_id      = (int) get_field( 'anime_id_mal', $post_id );
			$jikan_data  = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();

			$studio      = ! empty( $jikan_data['studios'] ) ? $jikan_data['studios'][0]['name'] : '';
			$ano         = $jikan_data['year'] ?? '';
			$sinopse     = $jikan_data['synopsis'] ?? '';
			$score       = $jikan_data['score'] ?? '';
			$membros     = $jikan_data['members'] ?? '';

			$poster      = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
			if ( empty( $poster ) ) {
				$poster      = get_the_post_thumbnail_url( $post_id, 'large' );
			}

			// Taxonomia Gêneros
			$terms_genero = get_the_terms( $post_id, 'genero' );
			$genres       = array();
			if ( ! empty( $terms_genero ) && ! is_wp_error( $terms_genero ) ) {
				foreach ( $terms_genero as $t ) {
					$genres[] = $t->name;
				}
			}

			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'TVSeries',
				'@id'         => get_permalink( $post_id ) . '#series',
				'name'        => get_the_title( $post_id ),
				'url'         => get_permalink( $post_id ),
				'description' => $sinopse ? wp_strip_all_tags( $sinopse ) : get_the_excerpt( $post_id ),
			);

			if ( ! empty( $poster ) ) {
				$schema['image'] = esc_url( $poster );
			}

			if ( ! empty( $genres ) ) {
				$schema['genre'] = $genres;
			}

			if ( ! empty( $ano ) ) {
				$schema['startDate'] = $ano;
			}

			if ( ! empty( $studio ) ) {
				$schema['productionCompany'] = array(
					'@type' => 'Organization',
					'name'  => esc_html( $studio ),
				);
			}

			// Injeção de nota baseada no MAL
			if ( ! empty( $score ) ) {
				$schema['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => number_format( (float) $score, 2 ),
					'bestRating'  => '10',
					'worstRating' => '1',
					'ratingCount' => $membros ? (int) $membros : 1,
				);
			}

			// Injeção de Elenco Riko se os dados Jikan estiverem disponíveis
			if ( $mal_id > 0 && function_exists( 'mm_get_jikan_characters_and_staff' ) ) {
				$jikan_data = mm_get_jikan_characters_and_staff( $mal_id );
				if ( ! empty( $jikan_data['dubladores'] ) ) {
					$actors = array();
					// Mapeia até 5 atores do elenco principal
					foreach ( array_slice( $jikan_data['dubladores'], 0, 5 ) as $va ) {
						$actors[] = array(
							'@type' => 'PerformanceRole',
							'actor' => array(
								'@type' => 'Person',
								'name'  => esc_html( $va['va_name'] ),
								'url'   => esc_url( $va['va_url'] ),
							),
							'characterName' => esc_html( $va['character_name'] ),
						);
					}
					$schema['actor'] = $actors;
				}
			}
			break;

		// ---------------------------------------------------------------------
		// CPT Episódio -> TVEpisode
		// ---------------------------------------------------------------------
		case 'episodio':
			$ep_num       = get_field( 'ep_numero', $post_id );
			$parent_anime = get_field( 'ep_anime_relacionado', $post_id );
			
			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'TVEpisode',
				'name'        => get_the_title( $post_id ),
				'url'         => get_permalink( $post_id ),
				'description' => get_the_excerpt( $post_id ),
			);

			if ( ! empty( $ep_num ) ) {
				$schema['episodeNumber'] = $ep_num;
			}

			// Liga à série pai
			if ( ! empty( $parent_anime ) ) {
				$parent_id = is_object( $parent_anime ) ? $parent_anime->ID : (int) $parent_anime;
				if ( $parent_id ) {
					$schema['partOfSeries'] = array(
						'@type' => 'TVSeries',
						'name'  => get_the_title( $parent_id ),
						'url'   => get_permalink( $parent_id ),
					);
				}
			}
			break;

		// ---------------------------------------------------------------------
		// CPT Review -> Review (Crítica de Obra)
		// ---------------------------------------------------------------------
		case 'review':
			$nota      = get_field( 'review_nota', $post_id );
			$veredicto = get_field( 'review_veredicto', $post_id );
			$anime_rel = get_field( 'review_anime_relacionado', $post_id );
			$author_id = get_post_field( 'post_author', $post_id );

			$schema = array(
				'@context'      => 'https://schema.org',
				'@type'         => 'Review',
				'name'          => get_the_title( $post_id ),
				'url'           => get_permalink( $post_id ),
				'reviewBody'    => $veredicto ? wp_strip_all_tags( $veredicto ) : get_the_excerpt( $post_id ),
				'datePublished' => get_the_date( 'c', $post_id ),
				'author'        => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', $author_id ),
					'url'   => get_author_posts_url( $author_id ),
				),
				'publisher'     => array(
					'@type' => 'Organization',
					'name'  => 'Geek ao Cubo',
					'url'   => home_url( '/' ),
				),
			);

			if ( ! empty( $nota ) ) {
				$schema['reviewRating'] = array(
					'@type'       => 'Rating',
					'ratingValue' => number_format( (float) $nota, 1 ),
					'bestRating'  => '10',
					'worstRating' => '1',
				);
			}

			// Liga à obra avaliada
			if ( ! empty( $anime_rel ) ) {
				$anime_id = is_object( $anime_rel ) ? $anime_rel->ID : (int) $anime_rel;
				if ( $anime_id ) {
					$mal_id = (int) get_field( 'anime_id_mal', $anime_id );
					$jikan_data = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();
					$anime_poster = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
					
					if ( empty( $anime_poster ) ) {
						$anime_poster = get_the_post_thumbnail_url( $anime_id, 'medium' );
					}

					$schema['itemReviewed'] = array(
						'@type' => 'TVSeries',
						'name'  => get_the_title( $anime_id ),
						'url'   => get_permalink( $anime_id ),
					);

					if ( ! empty( $anime_poster ) ) {
						$schema['itemReviewed']['image'] = esc_url( $anime_poster );
					}
				}
			}
			break;

		// ---------------------------------------------------------------------
		// Post Comum -> BlogPosting (Editorial)
		// ---------------------------------------------------------------------
		case 'post':
			$author_id = get_post_field( 'post_author', $post_id );
			$thumb     = get_the_post_thumbnail_url( $post_id, 'large' );

			$schema = array(
				'@context'      => 'https://schema.org',
				'@type'         => 'BlogPosting',
				'headline'      => get_the_title( $post_id ),
				'datePublished' => get_the_date( 'c', $post_id ),
				'dateModified'  => get_the_modified_date( 'c', $post_id ),
				'author'        => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', $author_id ),
					'url'   => get_author_posts_url( $author_id ),
				),
				'publisher'     => array(
					'@type' => 'Organization',
					'name'  => 'Geek ao Cubo',
					'logo'  => array(
						'@type' => 'ImageObject',
						'url'   => get_stylesheet_directory_uri() . '/atoms/logo.png', // Substituir pelo caminho real do logo do tema
					),
				),
				'description'   => get_the_excerpt( $post_id ),
			);

			if ( ! empty( $thumb ) ) {
				$schema['image'] = esc_url( $thumb );
			}
			break;

		// ---------------------------------------------------------------------
		// CPT Temporada -> TVSeason
		// ---------------------------------------------------------------------
		case 'temporada':
			$periodo_raw  = get_field( 'temp_periodo', $post_id );
			$ano          = get_field( 'temp_ano', $post_id );
			$descricao    = get_field( 'temp_descricao', $post_id );
			$animes_raw   = get_field( 'temp_animes', $post_id );

			$estacoes_map = array(
				'inverno'   => __( 'Inverno', 'geek-ao-cubo' ),
				'primavera' => __( 'Primavera', 'geek-ao-cubo' ),
				'verao'     => __( 'Verão', 'geek-ao-cubo' ),
				'outono'    => __( 'Outono', 'geek-ao-cubo' ),
			);
			$estacao_label = isset( $estacoes_map[ $periodo_raw ] ) ? $estacoes_map[ $periodo_raw ] : ucfirst( (string) $periodo_raw );
			$titulo_temporada = sprintf( __( 'Temporada de %s %s', 'geek-ao-cubo' ), $estacao_label, $ano );

			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'TVSeason',
				'name'        => $titulo_temporada,
				'url'         => get_permalink( $post_id ),
				'description' => $descricao ? wp_strip_all_tags( $descricao ) : get_the_excerpt( $post_id ),
			);

			if ( ! empty( $ano ) ) {
				$schema['temporalCoverage'] = (string) $ano;
			}

			if ( ! empty( $animes_raw ) && is_array( $animes_raw ) ) {
				$parts = array();
				foreach ( array_slice( $animes_raw, 0, 10 ) as $anime_post ) {
					$a_id = is_object( $anime_post ) ? $anime_post->ID : (int) $anime_post;
					if ( $a_id ) {
						$parts[] = array(
							'@type' => 'TVSeries',
							'name'  => get_the_title( $a_id ),
							'url'   => get_permalink( $a_id ),
						);
					}
				}
				if ( ! empty( $parts ) ) {
					$schema['parts'] = $parts;
				}
			}
			break;
	}

	// Se montou um esquema válido, imprime-o
	if ( ! empty( $schema ) ) {
		echo "\n" . '<!-- Geek ao Cubo Rich SEO Schema Markup -->' . "\n";
		echo '<script type="application/ld+json">' . "\n";
		echo json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "\n";
		echo '</script>' . "\n";
	}
}
add_action( 'wp_head', 'mm_inject_seo_json_ld' );
