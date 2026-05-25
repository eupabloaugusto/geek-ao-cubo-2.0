<?php
/**
 * Molecule: Controles de Carrossel (carousel-nav)
 *
 * Agrupa de forma coesa as setas direcionais (btn-nav-arrow) e os dots indicadores (carousel-dot).
 * Centraliza a ergonomia de toque e a navegação por teclado.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total        = isset( $args['total'] ) ? (int) $args['total'] : 4;
$active_index = isset( $args['active_index'] ) ? (int) $args['active_index'] : 0;
$show_arrows  = isset( $args['show_arrows'] ) ? (bool) $args['show_arrows'] : true;
$carousel_id  = isset( $args['carousel_id'] ) ? esc_attr( $args['carousel_id'] ) : '';
?>
<div class="carousel-nav" <?php echo $carousel_id ? 'data-target="' . $carousel_id . '"' : ''; ?> role="group" aria-label="<?php esc_attr_e( 'Controles de navegação do carrossel', 'hello-elementor-child' ); ?>">
	
	<?php if ( $show_arrows ) : ?>
		<!-- Seta Anterior -->
		<?php mm_render_component( 'atoms', 'btn-nav-arrow', array( 'direction' => 'prev', 'class' => 'carousel-nav__arrow js-carousel-prev' ) ); ?>
	<?php endif; ?>

	<!-- Trilho de Bolinhas / Indicadores -->
	<div class="carousel-nav__dots js-carousel-dots">
		<?php 
		for ( $i = 0; $i < $total; $i++ ) {
			mm_render_component( 'atoms', 'carousel-dot', array(
				'index'     => $i,
				'is_active' => ( $i === $active_index )
			) );
		}
		?>
	</div>

	<?php if ( $show_arrows ) : ?>
		<!-- Seta Próxima -->
		<?php mm_render_component( 'atoms', 'btn-nav-arrow', array( 'direction' => 'next', 'class' => 'carousel-nav__arrow js-carousel-next' ) ); ?>
	<?php endif; ?>

</div>
