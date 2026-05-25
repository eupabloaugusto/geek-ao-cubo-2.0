<?php
/**
 * Atom: Drawer Sub-Link
 * 
 * Link secundário para uso dentro de grupos de sub-navegação no Navigation Drawer.
 * Deve ser usado dentro de um wrapper <ul class="drawer-sub-nav">.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label     = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$url       = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$is_active = isset( $args['is_active'] ) ? (bool) $args['is_active'] : false;

$classes = 'drawer-sub-link';
if ( $is_active ) {
	$classes .= ' drawer-sub-link--active';
}
?>
<li class="drawer-sub-nav__item">
	<a href="<?php echo $url; ?>" class="<?php echo esc_attr( $classes ); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
		<span class="drawer-sub-link__dot" aria-hidden="true"></span>
		<span class="drawer-sub-link__text"><?php echo $label; ?></span>
	</a>
</li>
