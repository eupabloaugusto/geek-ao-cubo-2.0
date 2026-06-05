<?php
/**
 * Template Name: Detalhe do Anime
 * Template Post Type: anime
 *
 * Template dinâmico premium para a página de detalhes de um anime.
 * Integra-se com a API do MyAnimeList (Jikan) e carrega os componentes atômicos.
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

while ( have_posts() ) :
	the_post();

	$anime_id   = get_the_ID();
	$mal_id     = (int) get_field( 'anime_id_mal', $anime_id );
	
	// 1. Busca os dados completos na nova API Jikan com Stale-While-Revalidate
	$jikan_data = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();

	// Fallback de propriedades extraídas da API
	$imagem_poster   = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
	$titulo_japones  = $jikan_data['title_japanese'] ?? '';
	$nota            = $jikan_data['score'] ?? '';
	$status_slug     = ! empty( $jikan_data['status'] ) ? Jikan_API::translate_status( $jikan_data['status'] ) : '';
	$episodios       = $jikan_data['episodes'] ?? '';
	$duracao         = $jikan_data['duration'] ?? '';
	$studio          = ! empty( $jikan_data['studios'] ) ? $jikan_data['studios'][0]['name'] : '';
	$ano             = $jikan_data['year'] ?? '';
	$temporada_label = ! empty( $jikan_data['season'] ) ? ucfirst( $jikan_data['season'] ) . ' ' . $ano : $ano;
	$rating_label    = $jikan_data['rating'] ?? '';
	$sinopse         = get_field( 'anime_sinopse_manual' ) ?: ( $jikan_data['synopsis'] ?? get_the_content() );
	$membros         = $jikan_data['members'] ?? '';
	$ranking         = $jikan_data['rank'] ?? '';
	$popularidade    = $jikan_data['popularity'] ?? '';

	// 2. Busca e mapeia dados adicionais do MyAnimeList (Endpoints secundários)
	$personagens  = array();
	$dubladores   = array();
	$relations    = array();
	$recommendations = array();

	if ( $mal_id > 0 ) {
		// A. Personagens & Dubladores (mesmo endpoint)
		$jikan_chars_staff = mm_get_jikan_characters_and_staff( $mal_id );
		$personagens       = $jikan_chars_staff['characters'];
		$dubladores        = $jikan_chars_staff['dubladores'];

		// B. Relações (Extraídas diretamente do endpoint full)
		if ( ! empty( $jikan_data['relations'] ) ) {
			global $wpdb;
			foreach ( $jikan_data['relations'] as $rel ) {
				$relation_type = Jikan_API::translate_relation( $rel['relation'] );
				foreach ( $rel['entry'] as $entry ) {
					
					$anime_url = $entry['url'];
					$anime_image = '';
					
					// Só procuramos no banco de dados local se o item relacionado for um "anime".
					// Se for mangá, novel ou outro, a numeração de IDs do MAL é separada, então sempre tratamos como externo.
					if ( $entry['type'] === 'anime' ) {
						$mal_id_rel = (int) $entry['mal_id'];
						$local_post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'anime_id_mal' AND meta_value = %d", $mal_id_rel) );
						
						if ( $local_post_id ) {
							// Link interno, passando Link Juice!
							$anime_url = get_permalink( $local_post_id );
							$anime_image = get_the_post_thumbnail_url( $local_post_id, 'medium' );
						}
					}
					
					$relations[] = array(
						'anime_title'   => $entry['name'],
						'anime_image'   => $anime_image ?: '',
						'anime_url'     => $anime_url,
						'relation_type' => $relation_type,
					);
				}
			}
		}

		// C. Recomendações
		$recommendations = mm_get_jikan_recommendations( $mal_id );
	}

	// 2.5. Trailers e PVs do Anime
	$anime_videos = $mal_id > 0 ? mm_get_jikan_videos( $mal_id ) : array();

	// Trailer principal do endpoint full
	if ( ! empty( $jikan_data['trailer']['youtube_id'] ) ) {
		$trailer_video_id = $jikan_data['trailer']['youtube_id'];
		$ids_existentes = array_column( $anime_videos, 'id' );
		if ( ! in_array( $trailer_video_id, $ids_existentes, true ) ) {
			array_unshift( $anime_videos, array(
				'id'    => $trailer_video_id,
				'title' => __( 'Trailer Principal', 'geek-ao-cubo' ),
				'thumb' => "https://img.youtube.com/vi/{$trailer_video_id}/hqdefault.jpg",
			) );
		}
	}

	// 3. Busca e mapeia reviews locais vinculadas a este anime
	$reviews_query = mm_query_reviews_do_anime( $anime_id );
	$reviews_data  = array();
	if ( $reviews_query->have_posts() ) {
		while ( $reviews_query->have_posts() ) {
			$reviews_query->the_post();
			$reviews_data[] = array(
				'title'        => get_the_title(),
				'review_url'   => get_permalink(),
				'score'        => get_field( 'review_nota' ),
				'veredicto'    => get_field( 'review_recomenda' ),
				'excerpt'      => get_field( 'review_veredicto' ),
				'author_name'  => get_the_author(),
				'date'         => get_the_date(),
			);
		}
		wp_reset_postdata();
	}

	// 4. Mapeamento de Gêneros (Jikan API)
	$generos_mapped = array();
	if ( ! empty( $jikan_data['genres'] ) ) {
		foreach ( $jikan_data['genres'] as $gen ) {
			$generos_mapped[] = array(
				'name' => Jikan_API::translate_genre( $gen['name'] ),
				'url'  => '#',
			);
		}
	}

	// 5. Verifica se o anime possui dublagem BR (calculado a partir dos dubladores)
	$is_dublado = false;
	if ( ! empty( $dubladores ) ) {
		foreach ( $dubladores as $dub ) {
			// Pode vir como 'Português (BR)' graças ao nosso ajuste no cpt-helpers.php
			if ( strpos( $dub['va_language'], 'Português' ) !== false ) {
				$is_dublado = true;
				break;
			}
		}
	}
	$idioma_label = $is_dublado ? 'Leg | Dub' : 'Legendado';

	// =========================================================================
	// RENDERIZAÇÃO DO TOP HERO
	// =========================================================================
	mm_render_component( 'organisms', 'hero-anime', array(
		'titulo'          => get_the_title(),
		'titulo_japones'  => $titulo_japones,
		'imagem_poster'   => $imagem_poster,
		'imagem_backdrop' => ! empty( $jikan_data['trailer']['images']['maximum_image_url'] ) ? $jikan_data['trailer']['images']['maximum_image_url'] : $imagem_poster,
		'nota'            => $nota ? number_format( (float) $nota, 2 ) : '',
		'status'          => $status_slug,
		'tipo'            => __( 'Anime', 'geek-ao-cubo' ),
		'episodios'       => $episodios,
		'duracao'         => $duracao,
		'studio'          => $studio,
		'ano'             => $ano,
		'temporada'       => $temporada_label,
		'classificacao'   => $rating_label,
		'generos'         => $generos_mapped,
		'sinopse'         => $sinopse,
		'anime_id_mal'    => $mal_id,
		'membros'         => $membros,
		'ranking'         => $ranking,
		'popularidade'    => $popularidade,
		'idioma'          => $idioma_label, // Adicionado badge de Idioma
	) );
	?>

	<!-- Layout de Grade Principal -->
	<div class="anime-layout">
		
		<!-- A. CONTEÚDO PRINCIPAL (Esquerda/Centro) -->
		<main class="anime-layout__main" id="main-content">

			<!-- A0. Anúncio AdSense (Banner Topo) -->
			<?php
			mm_render_component( 'atoms', 'anuncio-adsense', array(
				'variacao' => 'banner',
			) );
			?>

			<!-- A1. Trailers e PVs do Anime -->
			<?php if ( ! empty( $anime_videos ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-trailers', array(
					'titulo'      => count( $anime_videos ) > 1
						? __( 'Trailers e PVs', 'geek-ao-cubo' )
						: __( 'Assista ao trailer', 'geek-ao-cubo' ),
					'videos'      => $anime_videos,
					'anime_title' => get_the_title(),
				) );
				?>
			<?php endif; ?>

			<!-- A2. Seção de Cronologia (Acordeões Híbridos) -->
			<?php
			$raw_title  = get_the_title();
			$base_title = preg_split('/[:\-]/', $raw_title)[0];
			$base_title = trim(preg_replace('/(Season|Part|The Movie|OVA|\d+).*$/i', '', $base_title));
			$franchise  = mm_get_franchise_posts( $raw_title, 'anime', $anime_id, 'anime_id_mal' );

			mm_render_component( 'organisms', 'secao-episodios-accordion', array(
				'titulo'           => sprintf( __( 'Cronologia de %s', 'geek-ao-cubo' ), $base_title ),
				'franchise'        => $franchise,
				'current_type'     => $jikan_data['type'] ?? '',
				'current_episodes' => $jikan_data['episodes'] ?? '',
			) );
			?>

			<!-- A2.5 Anúncio AdSense (Mobile Only) -->
			<?php
			mm_render_component( 'atoms', 'anuncio-adsense', array(
				'variacao'    => 'banner',
				'visibilidade' => 'mobile',
			) );
			?>

			<!-- A3. Seção de Personagens (Jikan API) -->

			<?php if ( ! empty( $personagens ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-personagens', array(
					'titulo'      => __( 'Personagens', 'geek-ao-cubo' ),
					'personagens' => $personagens,
				) );
				?>
			<?php endif; ?>

			<!-- A3.5 Anúncio AdSense Leaderboard (Desktop Only) -->
			<?php
			mm_render_component( 'atoms', 'anuncio-adsense', array(
				'variacao'    => 'leaderboard',
				'visibilidade' => 'desktop',
			) );
			?>

			<!-- A4. Seção de Dubladores em Acordeão (Jikan API) -->
			<?php if ( ! empty( $dubladores ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-personagens-dubladores-accordion', array(
					'titulo'     => __( 'Vozes do Anime', 'geek-ao-cubo' ),
					'dubladores' => $dubladores,
				) );
				?>
			<?php endif; ?>

			<!-- A5. Seção de Reviews Editoriais Locais -->
			<?php if ( ! empty( $reviews_data ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-reviews', array(
					'titulo'      => __( 'Análise da Redação', 'geek-ao-cubo' ),
					'reviews'     => $reviews_data,
					'total_count' => count( $reviews_data ),
					'max_reviews' => 3,
				) );
				?>
			<?php endif; ?>



			<!-- A5.5 Anúncio AdSense (Mobile Only) -->
			<?php
			mm_render_component( 'atoms', 'anuncio-adsense', array(
				'variacao'    => 'banner',
				'visibilidade' => 'mobile',
			) );
			?>

			<!-- A6. Seção de Recomendações (Jikan API) -->
			<?php if ( ! empty( $recommendations ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-recomendacoes', array(
					'titulo'        => __( 'Recomendado para quem gostou de ' . get_the_title(), 'geek-ao-cubo' ),
					'recomendacoes' => $recommendations,
					'ver_mais_url'  => $mal_id > 0 ? "https://myanimelist.net/anime/{$mal_id}" : '',
				) );
				?>
			<?php endif; ?>

			<!-- A7. Anúncio AdSense Leaderboard (Desktop Only) -->
			<?php
			mm_render_component( 'atoms', 'anuncio-adsense', array(
				'variacao'    => 'leaderboard',
				'visibilidade' => 'desktop',
			) );
			?>

		</main>

		<!-- B. BARRA LATERAL (Direita) -->
		<div class="anime-layout__sidebar">

			<!-- B0. Onde Assistir (Streaming) -->
			<?php if ( ! empty( $jikan_data['streaming'] ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'widget-onde-assistir', array(
					'streaming' => $jikan_data['streaming'],
				) );
				?>
			<?php endif; ?>

			<!-- B1.5. Trilha Sonora (Jikan API) -->
			<?php if ( ! empty( $jikan_data['theme'] ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-trilha-sonora', array(
					'openings' => $jikan_data['theme']['openings'] ?? array(),
					'endings'  => $jikan_data['theme']['endings'] ?? array(),
				) );
				?>
			<?php endif; ?>

			<!-- B2. Links Oficiais -->
			<?php if ( ! empty( $jikan_data['external'] ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'widget-links-oficiais', array(
					'external' => $jikan_data['external'],
				) );
				?>
			<?php endif; ?>

			<!-- B3. Anúncio AdSense (Quadrado) -->
			<?php
			mm_render_component( 'atoms', 'anuncio-adsense', array(
				'variacao' => 'quadrado',
			) );
			?>

		</div>

	</div>

	<?php
endwhile;

get_footer();
