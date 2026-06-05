<?php
/**
 * Template Name: Detalhe do Mangá
 * Template Post Type: manga
 *
 * Template dinâmico premium para a página de detalhes de um mangá.
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

	$manga_id   = get_the_ID();
	$mal_id     = (int) get_field( 'manga_id_mal', $manga_id );
	
	// 1. Busca os dados completos na nova API Jikan com Stale-While-Revalidate
	$jikan_data = $mal_id > 0 ? Jikan_API::get_manga_full( $mal_id ) : array();

	// Fallback de propriedades extraídas da API
	$imagem_poster   = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
	$titulo_japones  = $jikan_data['title_japanese'] ?? '';
	$nota            = $jikan_data['score'] ?? '';
	$status_slug     = ! empty( $jikan_data['status'] ) ? Jikan_API::translate_status( $jikan_data['status'] ) : '';
	
	// Para mangás, usamos Volumes e Capítulos no lugar de Episódios
	$volumes         = $jikan_data['volumes'] ?? '';
	$capitulos       = $jikan_data['chapters'] ?? '';
	$temporada_label = $volumes ? "Vol. " . $volumes : ( $capitulos ? "Capítulos: " . $capitulos : '' );
	$episodios       = $capitulos ? $capitulos . ' Capítulos' : ''; // Reaproveitando o campo de eps no hero para caps
	
	$duracao         = ''; // Mangá não tem duração
	
	$authors = [];
	if (!empty($jikan_data['authors'])) {
		foreach($jikan_data['authors'] as $author) {
			$authors[] = $author['name'];
		}
	}
	$studio          = !empty($authors) ? implode(', ', $authors) : ''; // Reutilizamos o campo 'studio' para 'autor' no hero
	
	$ano             = !empty($jikan_data['published']['prop']['from']['year']) ? $jikan_data['published']['prop']['from']['year'] : '';
	$rating_label    = $jikan_data['demographics'][0]['name'] ?? ''; // Demografia ao invés de Rating (ex: Shounen)
	$sinopse         = get_field( 'manga_sinopse_manual' ) ?: ( $jikan_data['synopsis'] ?? get_the_content() );
	$membros         = $jikan_data['members'] ?? '';
	$ranking         = $jikan_data['rank'] ?? '';
	$popularidade    = $jikan_data['popularity'] ?? '';

	// 2. Busca e mapeia dados adicionais do MyAnimeList (Endpoints secundários)
	$personagens  = array();
	$relations    = array();
	$recommendations = array();

	if ( $mal_id > 0 ) {
		// A. Personagens (Mapeados para o card-personagem)
		$personagens = mm_get_jikan_manga_characters( $mal_id );

		// B. Relações (Extraídas diretamente do endpoint full)
		if ( ! empty( $jikan_data['relations'] ) ) {
			global $wpdb;
			foreach ( $jikan_data['relations'] as $rel ) {
				$relation_type = Jikan_API::translate_relation( $rel['relation'] );
				foreach ( $rel['entry'] as $entry ) {
					
					$rel_url = $entry['url'];
					$rel_image = '';
					
					// Verifica internamente se já temos o anime ou mangá cadastrado
					if ( $entry['type'] === 'manga' || $entry['type'] === 'anime' ) {
						$meta_key = $entry['type'] === 'manga' ? 'manga_id_mal' : 'anime_id_mal';
						$mal_id_rel = (int) $entry['mal_id'];
						$local_post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d", $meta_key, $mal_id_rel) );
						
						if ( $local_post_id ) {
							$rel_url = get_permalink( $local_post_id );
							$rel_image = get_the_post_thumbnail_url( $local_post_id, 'medium' );
						}
					}
					
					$relations[] = array(
						'anime_title'   => $entry['name'],
						'anime_image'   => $rel_image ?: '',
						'anime_url'     => $rel_url,
						'relation_type' => $relation_type,
					);
				}
			}
		}

		// C. Recomendações
		$recommendations = mm_get_jikan_manga_recommendations( $mal_id );
	}

	// 3. Reviews editoriais locais vinculadas (se houver cpt review pra mangá no futuro)
	// Como a query atual (mm_query_reviews_do_anime) busca `review_anime_relacionado`,
	// precisaremos checar se o review suporta mangá. Vamos pular reviews locais aqui por enquanto,
	// ou usar uma genérica.
	$reviews_data  = array();

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

	$idioma_label = 'Mangá'; // Badge

	// =========================================================================
	// RENDERIZAÇÃO DO TOP HERO
	// =========================================================================
	mm_render_component( 'organisms', 'hero-anime', array(
		'titulo'          => get_the_title(),
		'titulo_japones'  => $titulo_japones,
		'imagem_poster'   => $imagem_poster,
		'imagem_backdrop' => $imagem_poster, // Mangá geralmente não tem trailer de onde puxar o backdrop
		'nota'            => $nota ? number_format( (float) $nota, 2 ) : '',
		'status'          => $status_slug,
		'tipo'            => $jikan_data['type'] ?? __( 'Mangá', 'geek-ao-cubo' ), // Manga, Novel, One-shot, etc
		'episodios'       => $episodios,
		'duracao'         => $duracao,
		'studio'          => $studio, // Autor(es)
		'ano'             => $ano,
		'temporada'       => $temporada_label,
		'classificacao'   => $rating_label, // Demografia (Shounen, Seinen)
		'generos'         => $generos_mapped,
		'sinopse'         => $sinopse,
		'anime_id_mal'    => $mal_id,
		'membros'         => $membros,
		'ranking'         => $ranking,
		'popularidade'    => $popularidade,
		'idioma'          => $idioma_label,
		'volumes'         => $volumes,
		'capitulos'       => $capitulos,
	) );
	?>

	<!-- Enqueue CSS (reaproveita do single-anime) -->
	<?php wp_enqueue_style( 'geek-ao-cubo-single-anime', get_template_directory_uri() . '/single-anime.css', array(), filemtime( get_template_directory() . '/single-anime.css' ) ); ?>

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

			<!-- A1. Seção de Cronologia (Volumes da Franquia) -->
			<?php
			$raw_title  = get_the_title();
			$base_title = preg_split('/[:\-]/', $raw_title)[0];
			$base_title = trim(preg_replace('/(Season|Part|The Movie|OVA|\d+).*$/i', '', $base_title));
			$franchise  = mm_get_franchise_posts( $raw_title, 'manga', $manga_id, 'manga_id_mal' );

			// Busca o aggregate MangaDex (volumes + capítulos) para o mangá atual
			$mangadex_aggregate = array();
			if ( $mal_id > 0 ) {
				$mangadex_uuid = MangaDex_API::get_manga_uuid( $mal_id, get_the_title(), $manga_id );
				if ( $mangadex_uuid ) {
					$mangadex_aggregate = MangaDex_API::get_manga_aggregate( $mangadex_uuid );
				}
			}

			if ( count( $franchise ) > 0 ) :
				mm_render_component( 'organisms', 'secao-episodios-accordion', array(
					'titulo'            => sprintf( __( 'Volumes da Franquia %s', 'geek-ao-cubo' ), $base_title ),
					'franchise'         => $franchise,
					'current_type'      => $jikan_data['type'] ?? '',
					'current_episodes'  => $capitulos,
					'context'           => 'manga',
					'manga_aggregate'   => $mangadex_aggregate,
				) );
			endif;
			?>

			<!-- A2. Seção de Personagens (Jikan API) -->
			<?php if ( ! empty( $personagens ) ) : ?>
				<?php
				// A API de personagens de mangá retorna um array ligeiramente diferente às vezes, 
				// mas nossa renderização de "secao-personagens" suporta a estrutura básica.
				// (Precisamos formatar igual $personagens do anime se for diferente)
				// Na Jikan v4 o endpoint manga/{id}/characters retorna a mesma estrutura.
				mm_render_component( 'organisms', 'secao-personagens', array(
					'titulo'      => __( 'Personagens', 'geek-ao-cubo' ),
					'personagens' => $personagens,
				) );
				?>
			<?php endif; ?>

			<!-- A2. Recomendações (Mangás) -->
			<?php if ( ! empty( $recommendations ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-recomendacoes', array(
					'titulo'        => __( 'Mangás Recomendados', 'geek-ao-cubo' ),
					'recomendacoes' => $recommendations,
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

			<!-- A5.5 Anúncio AdSense (Mobile Only) -->
			<?php
			mm_render_component( 'atoms', 'anuncio-adsense', array(
				'variacao'    => 'banner',
				'visibilidade' => 'mobile',
			) );
			?>

		</main>

		<!-- B. BARRA LATERAL (Direita) -->
		<div class="anime-layout__sidebar">

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
