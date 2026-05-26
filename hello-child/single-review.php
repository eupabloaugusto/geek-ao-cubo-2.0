<?php
/**
 * Template Name: Detalhe da Análise (Review)
 * Template Post Type: review
 *
 * Template dinâmico para a página de análise crítica (review).
 * Acopla a nota atômica, a caixa de prós e contras e o veredicto da redação.
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
	'mm-style-single-review',
	get_stylesheet_directory_uri() . '/single-review.css',
	array( 'mm-design-tokens' ),
	'1.0.0'
);

while ( have_posts() ) :
	the_post();

	$review_id          = get_the_ID();
	$anime_relacionado  = get_field( 'review_anime_relacionado', $review_id ); // WP_Post
	$nota               = get_field( 'review_nota', $review_id );
	$recomendacao       = get_field( 'review_recomenda', $review_id );
	$pros               = get_field( 'review_pros', $review_id ); // Repeater: array de subfields ['item']
	$contras            = get_field( 'review_contras', $review_id ); // Repeater: array de subfields ['item']
	$veredicto          = get_field( 'review_veredicto', $review_id );
	$publico_alvo       = get_field( 'review_publico_alvo', $review_id );
	$contem_spoilers    = get_field( 'review_spoilers', $review_id );
	$temporada_avaliada = get_field( 'review_temporada_avaliada', $review_id );

	// Tradução de recomendação para selos textuais
	$recomenda_labels = array(
		'sim'     => __( 'Altamente Recomendado', 'hello-elementor-child' ),
		'depende' => __( 'Recomendado com Ressalvas', 'hello-elementor-child' ),
		'nao'     => __( 'Não Recomendado', 'hello-elementor-child' ),
	);
	$recomenda_label = isset( $recomenda_labels[ $recomendacao ] ) ? $recomenda_labels[ $recomendacao ] : __( 'Avaliação Editorial', 'hello-elementor-child' );

	$recomenda_colors = array(
		'sim'     => 'color-success',
		'depende' => 'color-warning',
		'nao'     => 'color-error',
	);
	$recomenda_color = isset( $recomenda_colors[ $recomendacao ] ) ? $recomenda_colors[ $recomendacao ] : '';
	?>

	<div class="review-page">
		
		<!-- 1. BREADCRUMBS -->
		<div class="review-page__breadcrumb" style="margin-bottom: var(--space-500);">
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
					// B. Categoria Reviews
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => __( 'Reviews', 'hello-elementor-child' ),
						'url'            => home_url( '/reviews/' ),
						'show_separator' => true,
						'position'       => $position++,
					) );
					// C. Anime Relacionado
					if ( $anime_relacionado ) {
						$anime_title = is_object( $anime_relacionado ) ? $anime_relacionado->post_title : get_the_title( $anime_relacionado );
						$anime_url   = is_object( $anime_relacionado ) ? get_permalink( $anime_relacionado->ID ) : get_permalink( $anime_relacionado );
						mm_render_component( 'atoms', 'breadcrumb-item', array(
							'label'          => $anime_title,
							'url'            => $anime_url,
							'show_separator' => true,
							'position'       => $position++,
						) );
					}
					// D. Review Atual
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => __( 'Análise Editorial', 'hello-elementor-child' ),
						'is_current'     => true,
						'show_separator' => false,
						'position'       => $position++,
					) );
					?>
				</ol>
			</nav>
		</div>

		<!-- 2. CABEÇALHO DA ANÁLISE -->
		<header class="review-header">
			<div class="review-header__badge-row">
				<?php
				mm_render_component( 'atoms', 'badge-categoria', array(
					'categoria' => __( 'Análise', 'hello-elementor-child' ),
				) );
				?>
				<?php if ( ! empty( $temporada_avaliada ) ) : ?>
					<span class="badge-horario"><?php echo esc_html( $temporada_avaliada ); ?></span>
				<?php endif; ?>
			</div>

			<h1 class="review-header__title">
				<?php the_title(); ?>
			</h1>

			<div class="review-header__meta">
				<span class="review-meta__author"><?php _e( 'Por', 'hello-elementor-child' ); ?> <strong><?php the_author(); ?></strong></span>
				<span class="review-meta__divider">•</span>
				<span class="review-meta__date"><?php the_date(); ?></span>
			</div>
		</header>

		<!-- 3. ALERTA DE SPOILER (VIBRANTE SE APLICÁVEL) -->
		<?php if ( $contem_spoilers ) : ?>
			<div class="review-spoiler-alert" role="alert">
				<svg class="review-spoiler-alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
					<line x1="12" y1="9" x2="12" y2="13"></line>
					<line x1="12" y1="17" x2="12.01" y2="17"></line>
				</svg>
				<div class="review-spoiler-alert__content">
					<div class="review-spoiler-alert__title"><?php _e( 'Alerta de Spoilers', 'hello-elementor-child' ); ?></div>
					<div><?php _e( 'Esta análise contém revelações importantes da trama e do enredo. Prossiga por sua conta e risco!', 'hello-elementor-child' ); ?></div>
				</div>
			</div>
		<?php endif; ?>

		<!-- 4. GRADE DE CONTEÚDO (DUAS COLUNAS) -->
		<div class="review-layout">
			
			<!-- A. Conteúdo crítico e Prós/Contras -->
			<div class="review-content">
				
				<!-- A1. Texto crítico principal -->
				<div class="review-body">
					<?php the_content(); ?>
				</div>

				<!-- A2. Blocos de Prós e Contras (Lado a Lado no Desktop) -->
				<?php if ( ! empty( $pros ) || ! empty( $contras ) ) : ?>
					<div class="review-pros-contras">
						
						<!-- Prós -->
						<?php if ( ! empty( $pros ) ) : ?>
							<div class="review-box review-box--pros">
								<h3 class="review-box__title">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="review-spoiler-alert__icon" style="color: var(--color-success, #10B981); width: 1.25rem; height: 1.25rem;"><polyline points="20 6 9 17 4 12"></polyline></svg>
									<?php _e( 'Pontos Positivos', 'hello-elementor-child' ); ?>
								</h3>
								<ul class="review-box__list">
									<?php foreach ( (array) $pros as $item ) : ?>
										<li class="review-box__item"><?php echo esc_html( $item['item'] ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

						<!-- Contras -->
						<?php if ( ! empty( $contras ) ) : ?>
							<div class="review-box review-box--contras">
								<h3 class="review-box__title">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="review-spoiler-alert__icon" style="color: var(--color-error, #EF4444); width: 1.25rem; height: 1.25rem;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
									<?php _e( 'Pontos Negativos', 'hello-elementor-child' ); ?>
								</h3>
								<ul class="review-box__list">
									<?php foreach ( (array) $contras as $item ) : ?>
										<li class="review-box__item"><?php echo esc_html( $item['item'] ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

					</div>
				<?php endif; ?>

				<!-- A3. Veredicto Final da Redação -->
				<?php if ( ! empty( $veredicto ) ) : ?>
					<div class="review-veredicto">
						<h3 class="review-veredicto__title"><?php _e( 'Veredicto Final', 'hello-elementor-child' ); ?></h3>
						<div class="review-veredicto__text">
							<?php echo wp_kses_post( $veredicto ); ?>
						</div>
					</div>
				<?php endif; ?>

			</div>

			<!-- B. Barra lateral técnica da Review -->
			<aside class="review-sidebar">
				
				<!-- B1. Nota & Selo de Recomendação -->
				<div class="review-sidebar__card text-center" style="display: flex; flex-direction: column; align-items: center; gap: var(--space-400);">
					<?php
					mm_render_component( 'atoms', 'rating-score', array(
						'score' => number_format( (float) $nota, 1 ),
						'label' => __( 'Nota Editorial', 'hello-elementor-child' ),
						'votes' => $recomenda_label,
					) );
					?>
				</div>

				<!-- B2. Ficha do Anime avaliado -->
				<?php if ( $anime_relacionado ) : ?>
					<?php
					$anime_post_id = is_object( $anime_relacionado ) ? $anime_relacionado->ID : (int) $anime_relacionado;
					$anime_img = get_the_post_thumbnail_url( $anime_post_id, 'medium' );
					if ( empty( $anime_img ) ) {
						$anime_img = get_field( 'anime_imagem_capa_url', $anime_post_id );
					}
					?>
					<div class="review-sidebar__card text-center" style="display: flex; flex-direction: column; align-items: center; gap: var(--space-400);">
						<h4 style="font-weight: 700; color: var(--neutral-100);"><?php _e( 'Obra Analisada', 'hello-elementor-child' ); ?></h4>
						
						<?php
						mm_render_component( 'atoms', 'imagem-capa', array(
							'src'          => $anime_img,
							'alt'          => get_the_title( $anime_post_id ),
							'mostrar_nota' => false,
						) );
						?>

						<div style="font-weight: 600; color: var(--neutral-200); margin-top: var(--space-200);"><?php echo get_the_title( $anime_post_id ); ?></div>

						<?php
						mm_render_component( 'atoms', 'btn-primary', array(
							'label' => __( 'Ficha Técnica Completa', 'hello-elementor-child' ),
							'url'   => get_permalink( $anime_post_id ),
							'class' => 'w-100 text-center',
						) );
						?>
					</div>
				<?php endif; ?>

				<!-- B3. Público-Alvo -->
				<?php if ( ! empty( $publico_alvo ) ) : ?>
					<div class="review-sidebar__card">
						<h4 style="font-weight: 700; color: var(--neutral-100); margin-bottom: var(--space-200);"><?php _e( 'Indicado para', 'hello-elementor-child' ); ?></h4>
						<p style="font-size: var(--font-size-sm, 0.875rem); color: var(--neutral-300); line-height: 1.5; font-style: italic;">
							<?php echo esc_html( $publico_alvo ); ?>
						</p>
					</div>
				<?php endif; ?>

			</aside>

		</div>

	</div>

	<?php
endwhile;

get_footer();
