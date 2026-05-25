<?php
/**
 * Atom: Nav Link
 * 
 * Link de navegação (principalmente para header/menu).
 * Suporta estado ativo, hover com sublinhado animado e ícones SVG.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label     = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$url       = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$is_active = isset( $args['is_active'] ) ? (bool) $args['is_active'] : false;
$icon      = isset( $args['icon'] ) ? $args['icon'] : '';

$classes = 'nav-link';
if ( $is_active ) {
	$classes .= ' nav-link--active';
}
?>

<a href="<?php echo $url; ?>" class="<?php echo esc_attr( $classes ); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
	<?php if ( ! empty( $icon ) ) : ?>
		<span class="nav-link__icon" aria-hidden="true"><?php echo $icon; ?></span>
	<?php endif; ?>
	<span class="nav-link__text"><?php echo $label; ?></span>
</a>
