<?php
/**
 * Includes: Módulo de SEO Estrutural (Schema Markup JSON-LD)
 *
 * Imprime dados estruturados sofisticados no cabeçalho das páginas singulares
 * para cada CPT do portal, aumentando o desempenho nos resultados orgânicos do Google.
 *
 * @package vibe-animes
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Retorna a URL absoluta de um post traduzida/localizada de acordo com o idioma ativo.
 *
 * @param int         $post_id ID do post.
 * @param string|null $lang    Idioma desejado. Se null, usa o idioma ativo atual.
 * @return string Permalink localizado.
 */
function vibe_get_multilingual_permalink( $post_id, $lang = null ) {
	if ( ! function_exists( 'vibe_multilingual_get_current_language' ) ) {
		$lang = 'pt-BR';
	} elseif ( null === $lang ) {
		$lang = vibe_multilingual_get_current_language();
	}

	// Se for o caso virtual de personagem
	$is_char = false;
	$char_mal_id = 0;
	$post_type_exists = get_post_type( $post_id );
	if ( ! $post_type_exists ) {
		if ( get_query_var( 'personagem_id' ) ) {
			$is_char = true;
			$char_mal_id = (int) get_query_var( 'personagem_id' );
		} elseif ( $post_id > 0 && ! get_post( $post_id ) ) {
			// No caso do script de teste que simula ou passa IDs virtuais
			$is_char = true;
			$char_mal_id = $post_id;
		}
	}

	if ( $is_char ) {
		$slug = 'character';
		if ( class_exists( 'Jikan_API' ) ) {
			$jikan_data = Jikan_API::get_character_full( $char_mal_id );
			if ( ! empty( $jikan_data['name'] ) ) {
				$slug = sanitize_title( $jikan_data['name'] );
			}
		}
		
		$anime_slug = get_query_var( 'anime_slug' );
		if ( ! $anime_slug ) {
			$anime_slug = 'anime';
			if ( ! empty( $jikan_data['anime'] ) && class_exists( 'Jikan_API' ) ) {
				$primeiro_anime_mal_id = $jikan_data['anime'][0]['anime']['mal_id'] ?? 0;
				if ( $primeiro_anime_mal_id ) {
					$local_anime = mm_get_local_anime_by_mal_id( $primeiro_anime_mal_id );
					if ( $local_anime && ! empty( $local_anime['url'] ) ) {
						$anime_slug = wp_basename( $local_anime['url'] );
					}
				}
			}
			if ( $anime_slug === 'anime' && ! empty( $jikan_data['manga'] ) && class_exists( 'Jikan_API' ) ) {
				$primeiro_manga_mal_id = $jikan_data['manga'][0]['manga']['mal_id'] ?? 0;
				if ( $primeiro_manga_mal_id ) {
					global $wpdb;
					$manga_post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'manga_id_mal' AND meta_value = %d", $primeiro_manga_mal_id) );
					if ( $manga_post_id ) {
						$anime_slug = get_post_field( 'post_name', $manga_post_id );
					}
				}
			}
		}

		$home_root = rtrim( get_option( 'home' ), '/' );
		
		$char_slug = 'personagem';
		if ( 'en' === $lang ) {
			$char_slug = 'character';
		} elseif ( 'es' === $lang ) {
			$char_slug = 'personaje';
		} elseif ( 'fr' === $lang ) {
			$char_slug = 'personnage';
		} elseif ( 'de' === $lang ) {
			$char_slug = 'charakter';
		}

		if ( empty( $lang ) || 'pt-BR' === $lang ) {
			return $home_root . '/' . $anime_slug . '/' . $char_slug . '/' . $slug . '/';
		} else {
			return $home_root . '/' . $lang . '/' . $anime_slug . '/' . $char_slug . '/' . $slug . '/';
		}
	}

	$permalink = get_permalink( $post_id );
	if ( empty( $lang ) || 'pt-BR' === $lang ) {
		return $permalink;
	}
	$home_root = rtrim( get_option( 'home' ), '/' );
	$base      = str_replace( $home_root, '', $permalink );
	$base      = preg_replace( '#^/(en|es|fr|de)(/|$)#i', '/', $base );
	return $home_root . '/' . $lang . '/' . ltrim( $base, '/' );
}

/**
 * Imprime a marcação JSON-LD do BreadcrumbList global no wp_head.
 */
function mm_inject_breadcrumbs_json_ld() {
	if ( is_front_page() || is_home() ) {
		return;
	}

	if ( ! function_exists( 'mm_get_breadcrumbs' ) ) {
		return;
	}

	$crumbs = mm_get_breadcrumbs();
	if ( empty( $crumbs ) ) {
		return;
	}

	$items = array();
	$i = 1;
	foreach ( $crumbs as $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i++,
			'item'     => array(
				'@id'  => esc_url( $crumb['url'] ),
				'name' => esc_html( $crumb['name'] ),
			),
		);
	}

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);

	echo "\n" . '<!-- Vibe Animes BreadcrumbList Schema Markup -->' . "\n";
	echo '<script type="application/ld+json">' . "\n";
	echo json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "\n";
	echo '</script>' . "\n";
}
add_action( 'wp_head', 'mm_inject_breadcrumbs_json_ld', 5 );

/**
 * Imprime no cabeçalho (wp_head) os esquemas JSON-LD apropriados das entidades singulares.
 */
function mm_inject_seo_json_ld() {
	$is_char = (bool) get_query_var( 'personagem_id' );

	// Apenas em posts singulares ou personagem virtual
	if ( ! is_singular() && ! $is_char ) {
		return;
	}

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );
	$lang      = function_exists( 'vibe_multilingual_get_current_language' ) ? vibe_multilingual_get_current_language() : 'pt-BR';
	$schema    = array();

	if ( $is_char ) {
		$post_type = 'personagem';
		$post_id   = (int) get_query_var( 'personagem_id' );
	}

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
				'@id'         => vibe_get_multilingual_permalink( $post_id ) . '#series',
				'name'        => get_the_title( $post_id ),
				'url'         => vibe_get_multilingual_permalink( $post_id ),
				'description' => $sinopse ? wp_strip_all_tags( $sinopse ) : get_the_excerpt( $post_id ),
				'inLanguage'  => $lang,
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

			if ( $mal_id > 0 ) {
				$schema['sameAs'] = array(
					'https://myanimelist.net/anime/' . $mal_id
				);
			}

			// Injeção de Elenco Rico se os dados Jikan estiverem disponíveis
			if ( $mal_id > 0 && function_exists( 'mm_get_jikan_characters_and_staff' ) ) {
				$jikan_data_cast = mm_get_jikan_characters_and_staff( $mal_id );
				if ( ! empty( $jikan_data_cast['dubladores'] ) ) {
					$actors = array();
					// Mapeia até 5 atores do elenco principal
					foreach ( array_slice( $jikan_data_cast['dubladores'], 0, 5 ) as $va ) {
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
		// CPT Mangá -> Book
		// ---------------------------------------------------------------------
		case 'manga':
			$mal_id      = (int) get_field( 'manga_id_mal', $post_id );
			$jikan_data  = $mal_id > 0 ? Jikan_API::get_manga_full( $mal_id ) : array();

			$sinopse     = get_field( 'manga_sinopse_manual', $post_id ) ?: ( $jikan_data['synopsis'] ?? '' );
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

			// Autores
			$authors = array();
			if ( ! empty( $jikan_data['authors'] ) ) {
				foreach ( $jikan_data['authors'] as $author ) {
					$authors[] = array(
						'@type' => 'Person',
						'name'  => esc_html( $author['name'] ),
						'url'   => esc_url( $author['url'] ),
					);
				}
			}

			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'Book',
				'@id'         => vibe_get_multilingual_permalink( $post_id ) . '#book',
				'name'        => get_the_title( $post_id ),
				'url'         => vibe_get_multilingual_permalink( $post_id ),
				'description' => $sinopse ? wp_strip_all_tags( $sinopse ) : get_the_excerpt( $post_id ),
				'inLanguage'  => $lang,
			);

			if ( ! empty( $poster ) ) {
				$schema['image'] = esc_url( $poster );
			}

			if ( ! empty( $genres ) ) {
				$schema['genre'] = $genres;
			}

			if ( ! empty( $authors ) ) {
				$schema['author'] = $authors;
			}

			if ( ! empty( $score ) ) {
				$schema['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => number_format( (float) $score, 2 ),
					'bestRating'  => '10',
					'worstRating' => '1',
					'ratingCount' => $membros ? (int) $membros : 1,
				);
			}

			if ( $mal_id > 0 ) {
				$schema['sameAs'] = array(
					'https://myanimelist.net/manga/' . $mal_id
				);
				
				// Dados estruturados estendidos com o agregado MangaDex
				$uuid = class_exists( 'MangaDex_API' ) ? MangaDex_API::get_manga_uuid( $mal_id, get_the_title( $post_id ), $post_id ) : '';
				if ( $uuid ) {
					$schema['sameAs'][] = 'https://mangadex.org/title/' . $uuid;
					$aggregate = MangaDex_API::get_manga_aggregate( $uuid );
					if ( ! empty( $aggregate['total_chapters'] ) ) {
						$schema['numberOfPages'] = (int) $aggregate['total_chapters']; // Representa capítulos como aproximação semântica
					}
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
				'@id'         => vibe_get_multilingual_permalink( $post_id ) . '#episode',
				'name'        => get_the_title( $post_id ),
				'url'         => vibe_get_multilingual_permalink( $post_id ),
				'description' => get_the_excerpt( $post_id ),
				'inLanguage'  => $lang,
			);

			if ( ! empty( $ep_num ) ) {
				$schema['episodeNumber'] = $ep_num;
			}

			// Liga à série pai (usando permalink localizado/canônico)
			if ( ! empty( $parent_anime ) ) {
				$parent_id = is_object( $parent_anime ) ? $parent_anime->ID : (int) $parent_anime;
				if ( $parent_id ) {
					$schema['partOfSeries'] = array(
						'@type' => 'TVSeries',
						'name'  => get_the_title( $parent_id ),
						'url'   => vibe_get_multilingual_permalink( $parent_id ),
					);
				}
			}
			break;

		// ---------------------------------------------------------------------
		// CPT Dublador -> Person
		// ---------------------------------------------------------------------
		case 'dublador':
			$va_id_mal = (int) get_field( 'va_id_mal', $post_id );
			$va_bio    = get_field( 'va_bio_manual', $post_id ) ?: get_the_content( null, false, $post_id );
			$thumb     = get_the_post_thumbnail_url( $post_id, 'large' );

			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'Person',
				'@id'         => vibe_get_multilingual_permalink( $post_id ) . '#person',
				'name'        => get_the_title( $post_id ),
				'url'         => vibe_get_multilingual_permalink( $post_id ),
				'jobTitle'    => 'Voice Actor',
				'description' => $va_bio ? wp_strip_all_tags( $va_bio ) : get_the_excerpt( $post_id ),
				'inLanguage'  => $lang,
			);

			if ( ! empty( $thumb ) ) {
				$schema['image'] = esc_url( $thumb );
			}

			if ( $va_id_mal > 0 ) {
				$schema['sameAs'] = array(
					'https://myanimelist.net/people/' . $va_id_mal
				);
			}
			break;

		// ---------------------------------------------------------------------
		// CPT Personagem -> Person (Fictional Character)
		// ---------------------------------------------------------------------
		case 'personagem':
			$char_id_mal = $post_id;
			$char_bio    = '';
			$char_name   = '';
			$thumb       = '';

			if ( class_exists( 'Jikan_API' ) ) {
				$jikan_data = Jikan_API::get_character_full( $char_id_mal );
				if ( ! empty( $jikan_data ) ) {
					$char_name = $jikan_data['name'] ?? '';
					$char_bio  = $jikan_data['about'] ?? '';
					$thumb     = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
				}
			}

			if ( empty( $char_name ) ) {
				$char_name = get_the_title( $post_id );
				$char_bio  = get_field( 'char_bio_manual', $post_id ) ?: get_the_content( null, false, $post_id );
				$thumb     = get_the_post_thumbnail_url( $post_id, 'large' );
			}

			$schema = array(
				'@context'       => 'https://schema.org',
				'@type'          => 'Person',
				'additionalType' => 'https://schema.org/FictionalCharacter',
				'@id'            => vibe_get_multilingual_permalink( $char_id_mal ) . '#character',
				'name'           => $char_name,
				'url'            => vibe_get_multilingual_permalink( $char_id_mal ),
				'description'    => $char_bio ? wp_strip_all_tags( $char_bio ) : get_the_excerpt( $post_id ),
				'inLanguage'     => $lang,
			);

			if ( ! empty( $thumb ) ) {
				$schema['image'] = esc_url( $thumb );
			}

			if ( $char_id_mal > 0 ) {
				$schema['sameAs'] = array(
					'https://myanimelist.net/character/' . $char_id_mal
				);
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
				'@id'           => vibe_get_multilingual_permalink( $post_id ) . '#review',
				'name'          => get_the_title( $post_id ),
				'url'           => vibe_get_multilingual_permalink( $post_id ),
				'reviewBody'    => $veredicto ? wp_strip_all_tags( $veredicto ) : get_the_excerpt( $post_id ),
				'datePublished' => get_the_date( 'c', $post_id ),
				'inLanguage'    => $lang,
				'author'        => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', $author_id ),
					'url'   => get_author_posts_url( $author_id ),
				),
				'publisher'     => array(
					'@type' => 'Organization',
					'name'  => 'Vibe Animes',
					'url'   => vibe_get_multilingual_permalink( get_option( 'page_on_front' ) ?: $post_id ),
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

			// Liga à obra avaliada (anime)
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
						'url'   => vibe_get_multilingual_permalink( $anime_id ),
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
				'@id'           => vibe_get_multilingual_permalink( $post_id ) . '#article',
				'url'           => vibe_get_multilingual_permalink( $post_id ),
				'headline'      => get_the_title( $post_id ),
				'datePublished' => get_the_date( 'c', $post_id ),
				'dateModified'  => get_the_modified_date( 'c', $post_id ),
				'inLanguage'    => $lang,
				'author'        => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', $author_id ),
					'url'   => get_author_posts_url( $author_id ),
				),
				'publisher'     => array(
					'@type' => 'Organization',
					'name'  => 'Vibe Animes',
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
				'inverno'   => __( 'Inverno', 'vibe-animes' ),
				'primavera' => __( 'Primavera', 'vibe-animes' ),
				'verao'     => __( 'Verão', 'vibe-animes' ),
				'outono'    => __( 'Outono', 'vibe-animes' ),
			);
			$estacao_label = isset( $estacoes_map[ $periodo_raw ] ) ? $estacoes_map[ $periodo_raw ] : ucfirst( (string) $periodo_raw );
			$titulo_temporada = sprintf( __( 'Temporada de %s %s', 'vibe-animes' ), $estacao_label, $ano );

			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'TVSeason',
				'@id'         => vibe_get_multilingual_permalink( $post_id ) . '#season',
				'name'        => $titulo_temporada,
				'url'         => vibe_get_multilingual_permalink( $post_id ),
				'description' => $descricao ? wp_strip_all_tags( $descricao ) : get_the_excerpt( $post_id ),
				'inLanguage'  => $lang,
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
							'url'   => vibe_get_multilingual_permalink( $a_id ),
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
		echo "\n" . '<!-- Vibe Animes Rich SEO Schema Markup -->' . "\n";
		echo '<script type="application/ld+json">' . "\n";
		echo json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "\n";
		echo '</script>' . "\n";
	}
}
add_action( 'wp_head', 'mm_inject_seo_json_ld' );
