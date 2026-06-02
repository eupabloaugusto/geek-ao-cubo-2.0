<?php
/**
 * Atom: Carousel Dot (carousel-dot)
 *
 * Indicador visual de pílula dinâmica para controle de carrossel e sliders.
 * Em conformidade com acessibilidade WCAG (aria-label, aria-current).
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$index     = isset( $args['index'] ) ? (int) $args['index'] : 0;
$is_active = isset( $args['is_active'] ) && $args['is_active'];
$classes   = 'carousel-dot' . ( $is_active ? ' is-active' : '' );
?>
<button type="button" 
        class="<?php echo esc_attr( $classes ); ?>" 
        data-slide="<?php echo $index; ?>" 
        aria-label="<?php echo esc_attr( sprintf( __( 'Ir para o slide %s', 'geek-ao-cubo' ), $index + 1 ) ); ?>"
        aria-current="<?php echo $is_active ? 'true' : 'false'; ?>">
</button>
