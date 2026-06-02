<?php
/**
 * Template Name: Detalhe da Análise (Review)
 * Template Post Type: review
 *
 * Template dinâmico para a página de análise crítica (review).
 * Acopla a nota atômica, a caixa de prós e contras e o veredicto da redação.
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
		'sim'     => __( 'Altamente Recomendado', 'geek-ao-cubo' ),
		'depende' => __( 'Recomendado com Ressalvas', 'geek-ao-cubo' ),
		'nao'     => __( 'Não Recomendado', 'geek-ao-cubo' ),
	);
	$recomenda_label = isset( $recomenda_labels[ $recomendacao ] ) ? $recomenda_labels[ $recomendacao ] : __( 'Avaliação Editorial', 'geek-ao-cubo' );

	$recomenda_colors = array(
		'sim'     => 'color-success',
		'depende' => 'color-warning',
		'nao'     => 'color-error',
	);
	$recomenda_color = isset( $recomenda_colors[ $recomendacao ] ) ? $recomenda_colors[ $recomendacao ] : '';
	?>

	<main id="main-content" class="review-page">
		
		<!-- 1. BREADCRUMBS -->
		<div class="review-page__breadcrumb">
			<?php
			$breadcrumb_items = array(
				array( 'label' => __( 'Home', 'geek-ao-cubo' ), 'url' => home_url( '/' ) ),
				array( 'label' => __( 'Reviews', 'geek-ao-cubo' ), 'url' => home_url( '/reviews/' ) ),
			);
			
			if ( $anime_relacionado ) {
				$anime_title = is_object( $anime_relacionado ) ? $anime_relacionado->post_title : get_the_title( $anime_relacionado );
				$anime_url   = is_object( $anime_relacionado ) ? get_permalink( $anime_relacionado->ID ) : get_permalink( $anime_relacionado );
				$breadcrumb_items[] = array( 'label' => $anime_title, 'url' => $anime_url );
			}
			
			$breadcrumb_items[] = array( 'label' => __( 'Análise Editorial', 'geek-ao-cubo' ), 'url' => '' );

			mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => $breadcrumb_items,
			) );
			?>
		</div>

		<!-- 2. CABEÇALHO DA ANÁLISE -->
		<header class="review-header">
			<div class="review-header__badge-row">
				<?php
				mm_render_component( 'atoms', 'badge-categoria', array(
					'categoria' => __( 'Análise', 'geek-ao-cubo' ),
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
				<span class="review-meta__author"><?php _e( 'Por', 'geek-ao-cubo' ); ?> <strong><?php the_author(); ?></strong></span>
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
					<div class="review-spoiler-alert__title"><?php _e( 'Alerta de Spoilers', 'geek-ao-cubo' ); ?></div>
					<div><?php _e( 'Esta análise contém revelações importantes da trama e do enredo. Prossiga por sua conta e risco!', 'geek-ao-cubo' ); ?></div>
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
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="review-box__icon review-box__icon--success"><polyline points="20 6 9 17 4 12"></polyline></svg>
									<?php _e( 'Pontos Positivos', 'geek-ao-cubo' ); ?>
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
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="review-box__icon review-box__icon--error"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
									<?php _e( 'Pontos Negativos', 'geek-ao-cubo' ); ?>
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
						<h3 class="review-veredicto__title"><?php _e( 'Veredicto Final', 'geek-ao-cubo' ); ?></h3>
						<div class="review-veredicto__text">
							<?php echo wp_kses_post( $veredicto ); ?>
						</div>
					</div>
				<?php endif; ?>

			</div>

			<!-- B. Barra lateral técnica da Review -->
			<aside class="review-sidebar">
				
				<!-- B1. Nota & Selo de Recomendação -->
				<div class="review-sidebar__card review-sidebar__card--centered text-center">
					<?php
					mm_render_component( 'atoms', 'rating-score', array(
						'score' => number_format( (float) $nota, 1 ),
						'label' => __( 'Nota Editorial', 'geek-ao-cubo' ),
						'votes' => $recomenda_label,
					) );
					?>
				</div>

				<!-- B2. Ficha do Anime avaliado -->
				<?php if ( $anime_relacionado ) : ?>
					<?php
					$anime_post_id = is_object( $anime_relacionado ) ? $anime_relacionado->ID : (int) $anime_relacionado;
					$mal_id = (int) get_field( 'anime_id_mal', $anime_post_id );
					$jikan_data = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();

					$anime_img = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
					if ( empty( $anime_img ) ) {
						$anime_img = get_the_post_thumbnail_url( $anime_post_id, 'medium' );
					}
					?>
					<div class="review-sidebar__card review-sidebar__card--centered text-center">
						<h4 class="review-sidebar__heading"><?php _e( 'Obra Analisada', 'geek-ao-cubo' ); ?></h4>
						
						<?php
						mm_render_component( 'atoms', 'imagem-capa', array(
							'src'          => $anime_img,
							'alt'          => get_the_title( $anime_post_id ),
							'mostrar_nota' => false,
						) );
						?>

						<div class="review-sidebar__anime-title"><?php echo get_the_title( $anime_post_id ); ?></div>

						<?php
						mm_render_component( 'atoms', 'btn-primary', array(
							'label' => __( 'Ficha Técnica Completa', 'geek-ao-cubo' ),
							'url'   => get_permalink( $anime_post_id ),
							'class' => 'w-100 text-center',
						) );
						?>
					</div>
				<?php endif; ?>

				<!-- B3. Público-Alvo -->
				<?php if ( ! empty( $publico_alvo ) ) : ?>
					<div class="review-sidebar__card">
						<h4 class="review-sidebar__heading review-sidebar__heading--spaced"><?php _e( 'Indicado para', 'geek-ao-cubo' ); ?></h4>
						<p class="review-sidebar__text">
							<?php echo esc_html( $publico_alvo ); ?>
						</p>
					</div>
				<?php endif; ?>

			</aside>

		</div>

	</main>

	<?php
endwhile;

get_footer();
