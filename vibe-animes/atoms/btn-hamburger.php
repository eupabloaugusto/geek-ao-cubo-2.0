<?php
/**
 * Atom: Botão Hamburger (Menu Mobile)
 * 
 * Botão animado para abrir e fechar menus (mobile/offcanvas).
 * Suporta estado ativo (`is_active`) para animar e virar um "X".
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_active = isset( $args['is_active'] ) ? (bool) $args['is_active'] : false;
$id        = isset( $args['id'] ) ? esc_attr( $args['id'] ) : '';
$class     = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

$classes = 'btn-hamburger ' . $class;
if ( $is_active ) {
	$classes .= ' btn-hamburger--active';
}

$btn_id_attr = ! empty( $id ) ? ' id="' . $id . '"' : '';
?>

<button type="button" <?php echo $btn_id_attr; ?> class="<?php echo esc_attr( trim( $classes ) ); ?>" aria-label="<?php esc_attr_e( 'Alternar menu', 'geek-ao-cubo' ); ?>" aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>">
	<span class="btn-hamburger__box">
		<span class="btn-hamburger__inner"></span>
	</span>
</button>
