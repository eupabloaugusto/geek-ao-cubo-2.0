<?php
/**
 * Template Name: Detalhe do Anime
 * Template Post Type: anime
 *
 * Template dinâmico premium para a página de detalhes de um anime.
 * Integra-se com a API do MyAnimeList (Jikan) e carrega os componentes atômicos.
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

// Enfileira o estilo específico deste template
wp_enqueue_style(
	'mm-style-single-anime',
	get_stylesheet_directory_uri() . '/single-anime.css',
	array( 'mm-design-tokens' ),
	'1.0.0'
);

while ( have_posts() ) :
	the_post();

	$anime_id   = get_the_ID();
	$mal_id     = (int) get_field( 'anime_id_mal', $anime_id );
	
	// 1. Capa Poster (Imagem Destacada ou fallback da URL do MAL)
	$imagem_poster = get_the_post_thumbnail_url( $anime_id, 'large' );
	if ( empty( $imagem_poster ) ) {
		$imagem_poster = get_field( 'anime_imagem_capa_url', $anime_id );
	}

	// 2. Busca e mapeia dados adicionais do MyAnimeList via Jikan API com Cache Transient
	$personagens  = array();
	$dubladores   = array();
	$relations    = array();
	$recommendations = array();

	if ( $mal_id > 0 ) {
		// A. Personagens & Dubladores (mesmo endpoint)
		$jikan_chars_staff = mm_get_jikan_characters_and_staff( $mal_id );
		$personagens       = $jikan_chars_staff['characters'];
		$dubladores        = $jikan_chars_staff['dubladores'];

		// B. Relações (Prequels, Sequels...)
		$relations = mm_get_jikan_relations( $mal_id );

		// C. Recomendações
		$recommendations = mm_get_jikan_recommendations( $mal_id );
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

	// 4. Mapeamento de Gêneros (Taxonomia 'genero')
	$terms_genero = get_the_terms( $anime_id, 'genero' );
	$generos_mapped = array();
	if ( ! empty( $terms_genero ) && ! is_wp_error( $terms_genero ) ) {
		foreach ( $terms_genero as $term ) {
			$generos_mapped[] = array(
				'name' => $term->name,
				'url'  => get_term_link( $term ),
			);
		}
	}

	// 5. Mapeamento de Status (Taxonomia 'status_exibicao')
	$terms_status = get_the_terms( $anime_id, 'status_exibicao' );
	$status_slug = '';
	if ( ! empty( $terms_status ) && ! is_wp_error( $terms_status ) ) {
		$status_slug = $terms_status[0]->slug;
	}

	// 6. Determina a Temporada (CPT 'temporada' via relationship meta query ou fallback)
	$temporada_post = mm_get_temporada_do_anime( $anime_id );
	$temporada_label = '';
	if ( $temporada_post ) {
		$temporada_label = get_the_title( $temporada_post->ID );
	} else {
		// Fallback para ano
		$temporada_label = get_field( 'anime_ano', $anime_id ) ? get_field( 'anime_ano', $anime_id ) : '';
	}

	// 7. Classificação Etária
	$rating_raw = get_field( 'anime_rating', $anime_id );
	$rating_map = array(
		'g'     => __( 'G – Livre', 'hello-elementor-child' ),
		'pg'    => __( 'PG – Livre c/ Recomendação', 'hello-elementor-child' ),
		'pg13'  => __( 'PG-13 – Maiores de 13 anos', 'hello-elementor-child' ),
		'r17'   => __( 'R – Maiores de 17 anos', 'hello-elementor-child' ),
		'r'     => __( 'R+ – Conteúdo Adulto Leve', 'hello-elementor-child' ),
		'rx'    => __( 'Rx – Hentai', 'hello-elementor-child' ),
	);
	$rating_label = isset( $rating_map[ $rating_raw ] ) ? $rating_map[ $rating_raw ] : '';

	// =========================================================================
	// RENDERIZAÇÃO DO TOP HERO
	// =========================================================================
	mm_render_component( 'organisms', 'hero-anime', array(
		'titulo'          => get_the_title(),
		'imagem_poster'   => $imagem_poster,
		'imagem_backdrop' => $imagem_poster, // Usa a mesma imagem com desfoque CSS no backdrop
		'nota'            => get_field( 'anime_nota_mal', $anime_id ) ? number_format( (float) get_field( 'anime_nota_mal', $anime_id ), 2 ) : '',
		'status'          => $status_slug,
		'tipo'            => get_field( 'anime_source', $anime_id ) ? ucfirst( get_field( 'anime_source', $anime_id ) ) : 'TV',
		'episodios'       => get_field( 'anime_total_episodios', $anime_id ),
		'duracao'         => get_field( 'anime_duracao', $anime_id ),
		'studio'          => get_field( 'anime_studio', $anime_id ),
		'ano'             => get_field( 'anime_ano', $anime_id ),
		'temporada'       => $temporada_label,
		'classificacao'   => $rating_label,
		'generos'         => $generos_mapped,
		'sinopse'         => get_field( 'anime_sinopse', $anime_id ) ? get_field( 'anime_sinopse', $anime_id ) : get_the_content(),
	) );
	?>

	<!-- Layout de Grade Principal -->
	<div class="anime-layout">
		
		<!-- A. CONTEÚDO PRINCIPAL (Esquerda/Centro) -->
		<main class="anime-layout__main" id="main-content">
			
			<!-- A1. Seção de Relações (Jikan API) -->
			<?php if ( ! empty( $relations ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-relacionados', array(
					'titulo' => __( 'Conteúdo Relacionado', 'hello-elementor-child' ),
					'items'  => $relations,
				) );
				?>
			<?php endif; ?>

			<!-- A2. Seção de Personagens (Jikan API) -->
			<?php if ( ! empty( $personagens ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-personagens', array(
					'titulo'      => __( 'Personagens', 'hello-elementor-child' ),
					'personagens' => $personagens,
				) );
				?>
			<?php endif; ?>

			<!-- A3. Seção de Dubladores (Jikan API) -->
			<?php if ( ! empty( $dubladores ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-dubladores', array(
					'titulo'     => __( 'Dubladores Principais', 'hello-elementor-child' ),
					'dubladores' => $dubladores,
				) );
				?>
			<?php endif; ?>

			<!-- A4. Seção de Reviews Editoriais Locais -->
			<?php if ( ! empty( $reviews_data ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-reviews', array(
					'titulo'      => __( 'Análise da Redação', 'hello-elementor-child' ),
					'reviews'     => $reviews_data,
					'total_count' => count( $reviews_data ),
					'max_reviews' => 3,
				) );
				?>
			<?php endif; ?>

			<!-- A5. Seção de Estatísticas (MAL Local) -->
			<?php
			$estatisticas_bloco = array(
				'score'       => get_field( 'anime_nota_mal', $anime_id ) ? number_format( (float) get_field( 'anime_nota_mal', $anime_id ), 2 ) : '0.00',
				'score_label' => __( 'Nota Média', 'hello-elementor-child' ),
				'score_votes' => get_field( 'anime_membros', $anime_id ) ? sprintf( __( '%s votos', 'hello-elementor-child' ), number_format_i18n( get_field( 'anime_membros', $anime_id ) ) ) : '',
				'rank'        => get_field( 'anime_ranking', $anime_id ) ? '#' . get_field( 'anime_ranking', $anime_id ) : 'N/A',
				'rank_label'  => __( 'Ranking Geral', 'hello-elementor-child' ),
				'popularity'  => get_field( 'anime_popularidade', $anime_id ) ? '#' . get_field( 'anime_popularidade', $anime_id ) : 'N/A',
				'pop_label'   => __( 'Popularidade', 'hello-elementor-child' ),
				'members'     => get_field( 'anime_membros', $anime_id ) ? number_format_i18n( get_field( 'anime_membros', $anime_id ) ) : '0',
				'members_label' => __( 'Membros', 'hello-elementor-child' ),
			);
			mm_render_component( 'organisms', 'secao-estatisticas', array(
				'titulo'       => __( 'Métricas e Estatísticas', 'hello-elementor-child' ),
				'ver_mais_url' => $mal_id > 0 ? "https://myanimelist.net/anime/{$mal_id}/stats" : '',
				'estatisticas' => array( $estatisticas_bloco ),
			) );
			?>

			<!-- A6. Seção de Recomendações (Jikan API) -->
			<?php if ( ! empty( $recommendations ) ) : ?>
				<?php
				mm_render_component( 'organisms', 'secao-recomendacoes', array(
					'titulo'        => __( 'Recomendado para quem gostou de ' . get_the_title(), 'hello-elementor-child' ),
					'recomendacoes' => $recommendations,
					'ver_mais_url'  => $mal_id > 0 ? "https://myanimelist.net/anime/{$mal_id}" : '',
				) );
				?>
			<?php endif; ?>

		</main>

		<!-- B. BARRA LATERAL (Direita) -->
		<div class="anime-layout__sidebar">
			<?php
			$sidebar_metadata = array(
				__( 'Estúdio de Animação', 'hello-elementor-child' ) => get_field( 'anime_studio', $anime_id ),
				__( 'Ano de Estreia', 'hello-elementor-child' )      => get_field( 'anime_ano', $anime_id ),
				__( 'Duração por Episódio', 'hello-elementor-child' ) => get_field( 'anime_duracao', $anime_id ),
				__( 'Quantidade de Episódios', 'hello-elementor-child' ) => get_field( 'anime_total_episodios', $anime_id ) ? get_field( 'anime_total_episodios', $anime_id ) : __( 'Em Exibição', 'hello-elementor-child' ),
				__( 'Obra Original', 'hello-elementor-child' )       => get_field( 'anime_source', $anime_id ) ? ucfirst( get_field( 'anime_source', $anime_id ) ) : '',
				__( 'Classificação Etária', 'hello-elementor-child' ) => $rating_label,
			);

			mm_render_component( 'organisms', 'sidebar-anime-info', array(
				'rank'       => get_field( 'anime_ranking', $anime_id ) ? '#' . get_field( 'anime_ranking', $anime_id ) : '',
				'popularity' => get_field( 'anime_popularidade', $anime_id ) ? '#' . get_field( 'anime_popularidade', $anime_id ) : '',
				'members'    => get_field( 'anime_membros', $anime_id ) ? number_format_i18n( get_field( 'anime_membros', $anime_id ) ) : '',
				'metadata'   => $sidebar_metadata,
			) );
			?>
		</div>

	</div>

	<?php
endwhile;

get_footer();
