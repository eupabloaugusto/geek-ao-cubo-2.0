<?php
/**
 * Template Name: Detalhe da Temporada
 * Template Post Type: temporada
 *
 * Template dinâmico para a página de detalhes de uma temporada sazonal.
 * Exibe os animes estreantes em uma grade fluida e responsiva com suporte a destaque de cabeçalho.
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
	'mm-style-single-temporada',
	get_stylesheet_directory_uri() . '/single-temporada.css',
	array( 'mm-design-tokens' ),
	'1.0.0'
);

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
		'inverno'   => array( 'label' => __( 'Inverno', 'hello-elementor-child' ), 'emoji' => '❄️' ),
		'primavera' => array( 'label' => __( 'Primavera', 'hello-elementor-child' ), 'emoji' => '🌸' ),
		'verao'     => array( 'label' => __( 'Verão', 'hello-elementor-child' ), 'emoji' => '☀️' ),
		'outono'    => array( 'label' => __( 'Outono', 'hello-elementor-child' ), 'emoji' => '🍂' ),
	);

	$estacao_info = isset( $estacoes_map[ $periodo_raw ] ) ? $estacoes_map[ $periodo_raw ] : array( 'label' => ucfirst( $periodo_raw ), 'emoji' => '📅' );
	$titulo_temporada = sprintf( __( 'Temporada de %s %s', 'hello-elementor-child' ), $estacao_info['label'], $ano ) . ' ' . $estacao_info['emoji'];
	?>

	<div class="temporada-page">
		
		<!-- 1. BREADCRUMBS -->
		<div class="temporada-page__breadcrumb" style="margin-bottom: var(--space-500);">
			<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'hello-elementor-child' ); ?>">
				<ol class="breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
					<?php
					$position = 1;
					// A. Home
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => __( 'Home', 'hello-elementor-child' ),
						'url'            => home_url( '/' ),
						'show_separator' => true,
						'position'       => $position++,
					) );
					// B. Temporadas Archive Link (se cadastrado)
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => __( 'Temporadas', 'hello-elementor-child' ),
						'url'            => home_url( '/temporadas/' ),
						'show_separator' => true,
						'position'       => $position++,
					) );
					// C. Temporada Atual
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => $estacao_info['label'] . ' ' . $ano,
						'is_current'     => true,
						'show_separator' => false,
						'position'       => $position++,
					) );
					?>
				</ol>
			</nav>
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
			
			// Coleta os gêneros do destaque
			$dest_terms = get_the_terms( $dest_id, 'genero' );
			$dest_genres_mapped = array();
			if ( ! empty( $dest_terms ) && ! is_wp_error( $dest_terms ) ) {
				foreach ( $dest_terms as $term ) {
					$dest_genres_mapped[] = array(
						'name' => $term->name,
						'url'  => get_term_link( $term ),
					);
				}
			}

			// Coleta o status
			$dest_status_terms = get_the_terms( $dest_id, 'status_exibicao' );
			$dest_status = ( ! empty( $dest_status_terms ) && ! is_wp_error( $dest_status_terms ) ) ? $dest_status_terms[0]->slug : '';

			$dest_img = get_the_post_thumbnail_url( $dest_id, 'large' );
			if ( empty( $dest_img ) ) {
				$dest_img = get_field( 'anime_imagem_capa_url', $dest_id );
			}
			?>
			<div class="temporada-spotlight">
				<span class="temporada-spotlight__title"><?php _e( 'Destaque Sazonal', 'hello-elementor-child' ); ?></span>
				
				<?php
				mm_render_component( 'organisms', 'hero-anime', array(
					'titulo'          => get_the_title( $dest_id ),
					'imagem_poster'   => $dest_img,
					'imagem_backdrop' => $dest_img,
					'nota'            => get_field( 'anime_nota_mal', $dest_id ) ? number_format( (float) get_field( 'anime_nota_mal', $dest_id ), 2 ) : '',
					'status'          => $dest_status,
					'tipo'            => get_field( 'anime_source', $dest_id ) ? ucfirst( get_field( 'anime_source', $dest_id ) ) : 'TV',
					'episodios'       => get_field( 'anime_total_episodios', $dest_id ),
					'duracao'         => get_field( 'anime_duracao', $dest_id ),
					'studio'          => get_field( 'anime_studio', $dest_id ),
					'ano'             => get_field( 'anime_ano', $dest_id ),
					'temporada'       => $estacao_info['label'] . ' ' . $ano,
					'classificacao'   => '',
					'generos'         => $dest_genres_mapped,
					'sinopse'         => get_field( 'anime_sinopse', $dest_id ) ? get_field( 'anime_sinopse', $dest_id ) : get_the_content( null, false, $dest_id ),
					'url_assistir'    => get_permalink( $dest_id ),
				) );
				?>
			</div>
		<?php endif; ?>

		<!-- 4. GRADE DE ANIMES ESTREANTES -->
		<h2 class="temporada-grid-title"><?php _e( 'Estreias e Lançamentos da Estação', 'hello-elementor-child' ); ?></h2>
		
		<?php if ( ! empty( $animes_raw ) ) : ?>
			<div class="temporada-grid">
				<?php
				$animes_list = (array) $animes_raw;
				foreach ( $animes_list as $anime_post ) {
					$a_id = $anime_post->ID;
					
					// Busca gêneros
					$a_terms = get_the_terms( $a_id, 'genero' );
					$a_genres = array();
					if ( ! empty( $a_terms ) && ! is_wp_error( $a_terms ) ) {
						foreach ( $a_terms as $t ) {
							$a_genres[] = array(
								'name' => $t->name,
								'url'  => get_term_link( $t ),
							);
						}
					}

					// Busca imagem de capa
					$a_img = get_the_post_thumbnail_url( $a_id, 'large' );
					if ( empty( $a_img ) ) {
						$a_img = get_field( 'anime_imagem_capa_url', $a_id );
					}

					// Nota
					$a_nota = get_field( 'anime_nota_mal', $a_id );
					$a_nota_formatted = ! empty( $a_nota ) ? number_format( (float) $a_nota, 2 ) : '';

					// Estúdio de animação como subtítulo/horário secundário
					$a_studio = get_field( 'anime_studio', $a_id );

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
			<p style="text-align: center; color: var(--neutral-400); padding-block: var(--space-800);">
				<?php _e( 'Nenhum anime cadastrado para esta temporada ainda.', 'hello-elementor-child' ); ?>
			</p>
		<?php endif; ?>

	</div>

	<?php
endwhile;

get_footer();
