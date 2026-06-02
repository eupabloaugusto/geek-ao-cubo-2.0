<?php
/**
 * Atom: Drawer Overlay
 * 
 * Fundo escuro (backdrop) que cobre a tela quando o Navigation Drawer está aberto.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_active = isset( $args['is_active'] ) ? (bool) $args['is_active'] : false;
$id        = isset( $args['id'] ) ? esc_attr( $args['id'] ) : 'drawer-overlay';
$classes   = 'drawer-overlay';

if ( $is_active ) {
	$classes .= ' drawer-overlay--active';
}
?>
<div id="<?php echo $id; ?>" class="<?php echo esc_attr( $classes ); ?>" aria-hidden="true"></div>
