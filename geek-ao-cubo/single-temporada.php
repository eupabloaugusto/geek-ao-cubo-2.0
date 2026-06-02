<?php
/**
 * Template Name: Detalhe da Temporada
 * Template Post Type: temporada
 *
 * Template dinâmico para a página de detalhes de uma temporada sazonal.
 * Exibe os animes estreantes em uma grade fluida e responsiva com suporte a destaque de cabeçalho.
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

	$temporada_id = get_the_ID();
	$periodo_raw  = get_field( 'temp_periodo', $temporada_id );
	$ano          = get_field( 'temp_ano', $temporada_id );
	$descricao    = get_field( 'temp_descricao', $temporada_id );
	$destaque     = get_field( 'temp_destaque', $temporada_id ); // Retorna objeto WP_Post (Anime Destaque)
	$animes_raw   = get_field( 'temp_animes', $temporada_id );   // Retorna array de objetos WP_Post (Animes)

	// Dicionário de estações amigável em Português com emojis correspondentes
	$estacoes_map = array(
		'inverno'   => array( 'label' => __( 'Inverno', 'geek-ao-cubo' ), 'emoji' => '❄️' ),
		'primavera' => array( 'label' => __( 'Primavera', 'geek-ao-cubo' ), 'emoji' => '🌸' ),
		'verao'     => array( 'label' => __( 'Verão', 'geek-ao-cubo' ), 'emoji' => '☀️' ),
		'outono'    => array( 'label' => __( 'Outono', 'geek-ao-cubo' ), 'emoji' => '🍂' ),
	);

	$estacao_info = isset( $estacoes_map[ $periodo_raw ] ) ? $estacoes_map[ $periodo_raw ] : array( 'label' => ucfirst( $periodo_raw ), 'emoji' => '📅' );
	$titulo_temporada = sprintf( __( 'Temporada de %s %s', 'geek-ao-cubo' ), $estacao_info['label'], $ano ) . ' ' . $estacao_info['emoji'];
	?>

	<main id="main-content" class="temporada-page">
		
		<!-- 1. BREADCRUMBS -->
		<div class="temporada-page__breadcrumb">
			<?php
			mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'geek-ao-cubo' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Temporadas', 'geek-ao-cubo' ), 'url' => home_url( '/temporadas/' ) ),
					array( 'label' => $estacao_info['label'] . ' ' . $ano, 'url' => '' ),
				),
			) );
			?>
		</div>

		<!-- 2. CABEÇALHO DA TEMPORADA -->
		<header class="temporada-header">
			<h1 class="temporada-header__title"><?php echo esc_html( $titulo_temporada ); ?></h1>
			<?php if ( ! empty( $descricao ) ) : ?>
				<p class="temporada-header__desc"><?php echo esc_html( $descricao ); ?></p>
			<?php endif; ?>
		</header>

		<!-- 3. SPOTLIGHT ANIME (ANIME EM DESTAQUE) -->
		<?php if ( ! empty( $destaque ) ) : ?>
			<?php
			$destaque_post = is_array( $destaque ) ? $destaque[0] : $destaque;
			$dest_id       = $destaque_post->ID;
			$dest_mal_id   = (int) get_field( 'anime_id_mal', $dest_id );
			$jikan_dest    = $dest_mal_id > 0 ? Jikan_API::get_anime_full( $dest_mal_id ) : array();
			
			// Coleta os gêneros do destaque da API
			$dest_genres_mapped = array();
			if ( ! empty( $jikan_dest['genres'] ) ) {
				foreach ( $jikan_dest['genres'] as $gen ) {
					$dest_genres_mapped[] = array(
						'name' => Jikan_API::translate_genre( $gen['name'] ),
						'url'  => '#',
					);
				}
			}

			// Coleta o status
			$dest_status = ! empty( $jikan_dest['status'] ) ? Jikan_API::translate_status( $jikan_dest['status'] ) : '';

			$dest_img = $jikan_dest['images']['webp']['large_image_url'] ?? ( $jikan_dest['images']['jpg']['large_image_url'] ?? '' );
			if ( empty( $dest_img ) ) {
				$dest_img = get_the_post_thumbnail_url( $dest_id, 'large' );
			}
			?>
			<div class="temporada-spotlight">
				<span class="temporada-spotlight__title"><?php _e( 'Destaque Sazonal', 'geek-ao-cubo' ); ?></span>
				
				<?php
				mm_render_component( 'organisms', 'hero-anime', array(
					'titulo'          => get_the_title( $dest_id ),
					'imagem_poster'   => $dest_img,
					'imagem_backdrop' => $dest_img,
					'nota'            => ! empty( $jikan_dest['score'] ) ? number_format( (float) $jikan_dest['score'], 2 ) : '',
					'status'          => $dest_status,
					'tipo'            => ! empty( $jikan_dest['type'] ) ? $jikan_dest['type'] : 'TV',
					'episodios'       => $jikan_dest['episodes'] ?? '',
					'duracao'         => $jikan_dest['duration'] ?? '',
					'studio'          => ! empty( $jikan_dest['studios'] ) ? $jikan_dest['studios'][0]['name'] : '',
					'ano'             => $jikan_dest['year'] ?? '',
					'temporada'       => $estacao_info['label'] . ' ' . $ano,
					'classificacao'   => '',
					'generos'         => $dest_genres_mapped,
					'sinopse'         => get_field('anime_sinopse', $dest_id) ?: (! empty( $jikan_dest['synopsis'] ) ? $jikan_dest['synopsis'] : get_the_content( null, false, $dest_id )),
					'url_assistir'    => get_permalink( $dest_id ),
					'anime_id_mal'    => $dest_mal_id,
					'membros'         => $jikan_dest['members'] ?? '',
				) );
				?>
			</div>
		<?php endif; ?>

		<!-- 4. GRADE DE ANIMES ESTREANTES -->
		<h2 class="temporada-grid-title"><?php _e( 'Estreias e Lançamentos da Estação', 'geek-ao-cubo' ); ?></h2>
		
		<?php if ( ! empty( $animes_raw ) ) : ?>
			<div class="temporada-grid">
				<?php
				$animes_list = (array) $animes_raw;
				foreach ( $animes_list as $anime_post ) {
					$a_id = $anime_post->ID;
					$mal_id = (int) get_field( 'anime_id_mal', $a_id );
					$jikan_data = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();
					
					// Busca gêneros
					$a_genres = array();
					if ( ! empty( $jikan_data['genres'] ) ) {
						foreach ( $jikan_data['genres'] as $gen ) {
							$a_genres[] = array(
								'name' => Jikan_API::translate_genre( $gen['name'] ),
								'url'  => '#',
							);
						}
					}

					// Busca imagem de capa
					$a_img = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
					if ( empty( $a_img ) ) {
						$a_img = get_the_post_thumbnail_url( $a_id, 'large' );
					}

					// Nota
					$a_nota_formatted = ! empty( $jikan_data['score'] ) ? number_format( (float) $jikan_data['score'], 2 ) : '';

					// Estúdio de animação como subtítulo/horário secundário
					$a_studio = ! empty( $jikan_data['studios'] ) ? $jikan_data['studios'][0]['name'] : '';

					// Renderiza a molécula card-anime
					mm_render_component( 'molecules', 'card-anime', array(
						'titulo'     => get_the_title( $a_id ),
						'url'        => get_permalink( $a_id ),
						'imagem_url' => $a_img,
						'nota'       => $a_nota_formatted,
						'horario'    => $a_studio,
						'generos'    => $a_genres,
					) );
				}
				?>
			</div>
		<?php else : ?>
			<p class="temporada-empty">
				<?php _e( 'Nenhum anime cadastrado para esta temporada ainda.', 'geek-ao-cubo' ); ?>
			</p>
		<?php endif; ?>

	</main>

	<?php
endwhile;

get_footer();
