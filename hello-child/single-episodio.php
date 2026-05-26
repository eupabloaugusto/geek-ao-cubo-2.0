<?php
/**
 * Template Name: Detalhe do Episódio
 * Template Post Type: episodio
 *
 * Template dinâmico para a página de detalhes de um episódio.
 * Acopla o componente de vídeo, dados do anime pai e navegação sequencial.
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
	'mm-style-single-episodio',
	get_stylesheet_directory_uri() . '/single-episodio.css',
	array( 'mm-design-tokens' ),
	'1.0.0'
);

while ( have_posts() ) :
	the_post();

	$episodio_id     = get_the_ID();
	$anime_pai       = get_field( 'ep_anime_relacionado', $episodio_id );
	$ep_numero       = get_field( 'ep_numero', $episodio_id );
	$titulo_original = get_field( 'ep_titulo_original', $episodio_id );
	$data_lancamento = get_field( 'ep_data_lancamento', $episodio_id );
	$duracao         = get_field( 'ep_duracao', $episodio_id );
	$resumo          = get_field( 'ep_resumo', $episodio_id );
	$trailer_url     = get_field( 'ep_trailer_url', $episodio_id );
	$is_filler       = get_field( 'ep_filler', $episodio_id );
	$ep_nota         = get_field( 'ep_nota_media', $episodio_id );

	// Extrai ID do vídeo do YouTube se a URL estiver presente
	$video_id = '';
	if ( ! empty( $trailer_url ) ) {
		$video_id = mm_get_youtube_video_id( $trailer_url );
	}

	// Lógica de navegação sequencial (Próximo / Anterior)
	$prev_ep_url = '';
	$next_ep_url = '';
	$prev_ep_num = 0;
	$next_ep_num = 0;

	if ( $anime_pai ) {
		$anime_pai_id  = is_object( $anime_pai ) ? $anime_pai->ID : (int) $anime_pai;
		$all_eps_query = mm_query_episodios_do_anime( $anime_pai_id );
		
		if ( $all_eps_query->have_posts() ) {
			$eps = $all_eps_query->posts;
			$count = count( $eps );
			
			for ( $i = 0; $i < $count; $i++ ) {
				$item_ep_num = (int) get_field( 'ep_numero', $eps[$i]->ID );
				if ( $item_ep_num === (int) $ep_numero ) {
					if ( $i > 0 ) {
						$prev_ep_post = $eps[ $i - 1 ];
						$prev_ep_url  = get_permalink( $prev_ep_post->ID );
						$prev_ep_num  = get_field( 'ep_numero', $prev_ep_post->ID );
					}
					if ( $i < $count - 1 ) {
						$next_ep_post = $eps[ $i + 1 ];
						$next_ep_url  = get_permalink( $next_ep_post->ID );
						$next_ep_num  = get_field( 'ep_numero', $next_ep_post->ID );
					}
					break;
				}
			}
			wp_reset_postdata();
		}
	}
	?>

	<div class="episodio-page">
		
		<!-- 1. BREADCRUMB CUSTOMIZADO -->
		<div class="episodio-page__breadcrumb">
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
					// B. Categoria Animes
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => __( 'Animes', 'hello-elementor-child' ),
						'url'            => home_url( '/animes/' ),
						'show_separator' => true,
						'position'       => $position++,
					) );
					// C. Anime Pai
					if ( $anime_pai ) {
						$anime_title = is_object( $anime_pai ) ? $anime_pai->post_title : get_the_title( $anime_pai );
						$anime_url   = is_object( $anime_pai ) ? get_permalink( $anime_pai->ID ) : get_permalink( $anime_pai );
						mm_render_component( 'atoms', 'breadcrumb-item', array(
							'label'          => $anime_title,
							'url'            => $anime_url,
							'show_separator' => true,
							'position'       => $position++,
						) );
					}
					// D. Episódio Atual
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => sprintf( __( 'Episódio %s', 'hello-elementor-child' ), $ep_numero ),
						'is_current'     => true,
						'show_separator' => false,
						'position'       => $position++,
					) );
					?>
				</ol>
			</nav>
		</div>

		<!-- 2. CABEÇALHO DO EPISÓDIO -->
		<header class="episodio-header">
			<div class="episodio-header__badge-row">
				<?php if ( $is_filler ) : ?>
					<?php
					mm_render_component( 'atoms', 'badge-categoria', array(
						'categoria' => __( 'Filler', 'hello-elementor-child' ),
						'class'     => 'badge-categoria--filler',
					) );
					?>
				<?php else : ?>
					<?php
					mm_render_component( 'atoms', 'badge-categoria', array(
						'categoria' => __( 'Canon', 'hello-elementor-child' ),
					) );
					?>
				<?php endif; ?>
				
				<?php if ( ! empty( $duracao ) ) : ?>
					<span class="badge-horario"><?php echo esc_html( $duracao ); ?></span>
				<?php endif; ?>
			</div>

			<h1 class="episodio-header__title">
				<?php 
				$anime_title_header = is_object( $anime_pai ) ? $anime_pai->post_title : ( $anime_pai ? get_the_title( $anime_pai ) : '' );
				echo sprintf( __( '%s — Episódio %s', 'hello-elementor-child' ), esc_html( $anime_title_header ), esc_html( $ep_numero ) ); 
				?>
			</h1>

			<?php if ( ! empty( $titulo_original ) ) : ?>
				<p class="episodio-header__sub"><?php echo esc_html( $titulo_original ); ?></p>
			<?php endif; ?>
		</header>

		<!-- 3. ÁREA DE INCORPORAÇÃO DE VÍDEO (FACADE / TRAILER) -->
		<?php if ( ! empty( $video_id ) ) : ?>
			<div class="episodio-video">
				<?php
				mm_render_component( 'atoms', 'embed-video', array(
					'video_id' => $video_id,
					'title'    => sprintf( __( 'Preview do Episódio %s', 'hello-elementor-child' ), $ep_numero ),
				) );
				?>
			</div>
		<?php endif; ?>

		<!-- 4. DETALHES DO EPISÓDIO (DUAS COLUNAS) -->
		<div class="episodio-layout">
			
			<!-- A. Resumo principal -->
			<div class="episodio-content">
				<article class="episodio-content__body">
					<?php if ( ! empty( $resumo ) ) : ?>
						<?php echo wp_kses_post( $resumo ); ?>
					<?php else : ?>
						<p><?php _e( 'Nenhum resumo disponível para este episódio ainda.', 'hello-elementor-child' ); ?></p>
					<?php endif; ?>
				</article>
			</div>

			<!-- B. Barra lateral técnica -->
			<aside class="episodio-sidebar">
				<h4 class="episodio-sidebar__title"><?php _e( 'Detalhes Técnicos', 'hello-elementor-child' ); ?></h4>
				<dl class="episodio-sidebar__list">
					
					<?php if ( ! empty( $data_lancamento ) ) : ?>
						<div class="episodio-sidebar__item">
							<dt class="episodio-sidebar__label"><?php _e( 'Lançamento', 'hello-elementor-child' ); ?></dt>
							<dd class="episodio-sidebar__value"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $data_lancamento ) ) ); ?></dd>
						</div>
					<?php endif; ?>

					<div class="episodio-sidebar__item">
						<dt class="episodio-sidebar__label"><?php _e( 'Tipo de Conteúdo', 'hello-elementor-child' ); ?></dt>
						<dd class="episodio-sidebar__value"><?php echo $is_filler ? __( 'Filler (Não Canônico)', 'hello-elementor-child' ) : __( 'Canon (História Original)', 'hello-elementor-child' ); ?></dd>
					</div>

					<?php if ( ! empty( $ep_nota ) ) : ?>
						<div class="episodio-sidebar__item">
							<dt class="episodio-sidebar__label"><?php _e( 'Nota no MAL', 'hello-elementor-child' ); ?></dt>
							<dd class="episodio-sidebar__value"><?php echo number_format( (float) $ep_nota, 2 ); ?></dd>
						</div>
					<?php endif; ?>

					<?php if ( $anime_pai ) : ?>
						<div class="episodio-sidebar__item" style="margin-top: var(--space-400); padding-top: var(--space-400); border-top: 0.0625rem solid var(--neutral-700);">
							<?php
							mm_render_component( 'atoms', 'btn-secondary', array(
								'label' => __( 'Ver Anime Completo', 'hello-elementor-child' ),
								'url'   => is_object( $anime_pai ) ? get_permalink( $anime_pai->ID ) : get_permalink( $anime_pai ),
								'class' => 'w-100 text-center',
							) );
							?>
						</div>
					<?php endif; ?>

				</dl>
			</aside>

		</div>

		<!-- 5. CONTROLES DE NAVEGAÇÃO SEQUENCIAL -->
		<?php if ( ! empty( $prev_ep_url ) || ! empty( $next_ep_url ) ) : ?>
			<nav class="episodio-nav" aria-label="<?php esc_attr_e( 'Navegação de episódios', 'hello-elementor-child' ); ?>">
				
				<div class="episodio-nav__btn">
					<?php if ( ! empty( $prev_ep_url ) ) : ?>
						<?php
						mm_render_component( 'atoms', 'btn-secondary', array(
							'label' => sprintf( __( '← Episódio %s', 'hello-elementor-child' ), $prev_ep_num ),
							'url'   => $prev_ep_url,
							'class' => 'w-100 text-center',
						) );
						?>
					<?php endif; ?>
				</div>

				<div class="episodio-nav__btn text-right">
					<?php if ( ! empty( $next_ep_url ) ) : ?>
						<?php
						mm_render_component( 'atoms', 'btn-primary', array(
							'label' => sprintf( __( 'Episódio %s →', 'hello-elementor-child' ), $next_ep_num ),
							'url'   => $next_ep_url,
							'class' => 'w-100 text-center',
						) );
						?>
					<?php endif; ?>
				</div>

			</nav>
		<?php endif; ?>

	</div>

	<?php
endwhile;

get_footer();
