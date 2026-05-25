<?php
/**
 * Atom: Drawer Link
 * 
 * Link estilo bloco para uso dentro do Navigation Drawer (menu mobile).
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label        = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$url          = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$is_active    = isset( $args['is_active'] ) ? (bool) $args['is_active'] : false;
$icon         = isset( $args['icon'] ) ? $args['icon'] : '';
$has_dropdown = isset( $args['has_dropdown'] ) ? (bool) $args['has_dropdown'] : false;
$is_open      = isset( $args['is_open'] ) ? (bool) $args['is_open'] : false;

$classes = 'drawer-link';
if ( $is_active ) {
	$classes .= ' drawer-link--active';
}
if ( $is_open ) {
	$classes .= ' drawer-link--open';
}
?>
<a href="<?php echo $url; ?>" class="<?php echo esc_attr( $classes ); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?> <?php echo $has_dropdown ? 'aria-expanded="' . ( $is_open ? 'true' : 'false' ) . '"' : ''; ?>>
	<?php if ( ! empty( $icon ) ) : ?>
		<span class="drawer-link__icon" aria-hidden="true"><?php echo $icon; ?></span>
	<?php endif; ?>
	<span class="drawer-link__text"><?php echo $label; ?></span>
	
	<?php if ( $has_dropdown ) : ?>
		<span class="drawer-link__toggle" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
		</span>
	<?php endif; ?>
</a>
