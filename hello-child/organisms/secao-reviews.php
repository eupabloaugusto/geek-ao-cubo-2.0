<?php
/**
 * Organism: Seção de Reviews (secao-reviews)
 *
 * Lista reviews de usuários usando a molécula review-card. Renderiza até
 * $max_reviews cards e exibe um botão "Ver mais reviews" opcional quando
 * há mais avaliações disponíveis ou quando ver_mais_url é fornecida.
 *
 * @package hello-elementor-child
 *
 * @param string $titulo          Título da seção. Default: 'Reviews'.
 * @param array  $reviews         Array de arrays com os parâmetros de cada review-card (obrigatório).
 * @param int    $total_count     Total de reviews para exibir no cabeçalho (ex: 2341). Default: 0.
 * @param int    $max_reviews     Número máximo de cards renderizados. Default: 6.
 * @param string $ver_mais_url    URL do botão "Ver mais reviews" (opcional).
 * @param string $ver_mais_label  Label do botão. Default: 'Ver todas as reviews'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo         = isset( $args['titulo'] )         ? esc_html( $args['titulo'] )         : __( 'Reviews', 'hello-elementor-child' );
$reviews        = isset( $args['reviews'] )        ? (array) $args['reviews']             : array();
$total_count    = isset( $args['total_count'] )    ? (int) $args['total_count']           : 0;
$max_reviews    = isset( $args['max_reviews'] )    ? (int) $args['max_reviews']           : 6;
$ver_mais_url   = isset( $args['ver_mais_url'] )   ? esc_url( $args['ver_mais_url'] )     : '';
$ver_mais_label = isset( $args['ver_mais_label'] ) ? esc_html( $args['ver_mais_label'] )  : __( 'Ver todas as reviews', 'hello-elementor-child' );

if ( empty( $reviews ) ) {
	return;
}

// Limita os cards renderizados ao máximo configurado
$reviews_slice   = array_slice( $reviews, 0, $max_reviews );
$show_ver_mais   = ! empty( $ver_mais_url );
?>

<section
	class="secao-reviews"
	aria-label="<?php echo esc_attr( $titulo ); ?>"
	itemscope
	itemtype="https://schema.org/ItemList"
>
	<meta itemprop="name" content="<?php echo esc_attr( $titulo ); ?>">

	<div class="secao-reviews__inner">

		<!-- =====================================================
		     CABEÇALHO: título + contador total
		     ===================================================== -->
		<header class="secao-reviews__header">
			<h2 class="secao-reviews__title"><?php echo $titulo; ?></h2>

			<?php if ( $total_count > 0 ) : ?>
				<span class="secao-reviews__count" aria-label="<?php echo esc_attr( sprintf( __( '%s avaliações no total', 'hello-elementor-child' ), number_format_i18n( $total_count ) ) ); ?>">
					<?php echo number_format_i18n( $total_count ); ?>
					<span class="secao-reviews__count-label"><?php _e( 'reviews', 'hello-elementor-child' ); ?></span>
				</span>
			<?php endif; ?>
		</header>

		<!-- =====================================================
		     LISTA DE REVIEWS
		     ===================================================== -->
		<div class="secao-reviews__list" role="list">
			<?php foreach ( $reviews_slice as $review ) : ?>
				<div class="secao-reviews__item" role="listitem" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<?php mm_render_component( 'molecules', 'review-card', $review ); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- =====================================================
		     RODAPÉ: botão "Ver mais reviews"
		     ===================================================== -->
		<?php if ( $show_ver_mais ) : ?>
			<footer class="secao-reviews__footer">
				<a
					href="<?php echo $ver_mais_url; ?>"
					class="btn btn--secondary secao-reviews__btn"
					rel="nofollow noopener"
					target="_blank"
					aria-label="<?php echo esc_attr( $ver_mais_label ); ?>"
				>
					<?php echo $ver_mais_label; ?>
					<svg class="secao-reviews__btn-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M3 8H13M9 4L13 8L9 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			</footer>
		<?php endif; ?>

	</div>
</section>
