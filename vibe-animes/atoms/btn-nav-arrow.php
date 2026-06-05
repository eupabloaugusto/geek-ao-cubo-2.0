<?php
/**
 * Atom: Botão de Seta de Navegação (btn-nav-arrow)
 *
 * Botão circular de alta fidelidade visual (glassmorphism) com ícones chevron embutidos.
 * Fornece alvos táteis amplos e acessíveis para controle de sliders.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$direction = isset( $args['direction'] ) && in_array( $args['direction'], array( 'prev', 'next' ), true ) ? $args['direction'] : 'next';
$classes   = 'btn-nav-arrow btn-nav-arrow--' . $direction;

if ( isset( $args['class'] ) ) {
	$classes .= ' ' . esc_attr( $args['class'] );
}

$aria_label = ( $direction === 'prev' ) ? __( 'Slide anterior', 'geek-ao-cubo' ) : __( 'Próximo slide', 'geek-ao-cubo' );
?>
<button type="button" 
        class="<?php echo esc_attr( $classes ); ?>" 
        aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<?php if ( $direction === 'prev' ) : ?>
		<!-- Chevron Esquerdo SVG -->
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="btn-nav-arrow__icon" aria-hidden="true">
			<polyline points="15 18 9 12 15 6"></polyline>
		</svg>
	<?php else : ?>
		<!-- Chevron Direito SVG -->
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="btn-nav-arrow__icon" aria-hidden="true">
			<polyline points="9 18 15 12 9 6"></polyline>
		</svg>
	<?php endif; ?>
</button>
