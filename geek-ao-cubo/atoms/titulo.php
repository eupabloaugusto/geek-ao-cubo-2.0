<?php
/**
 * Atom: Titulo (titulo)
 *
 * Átomo semântico para exibição de cabeçalhos de texto.
 *
 * @package geek-ao-cubo
 * @since   4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tag    = ! empty( $args['tag'] ) ? sanitize_key( $args['tag'] ) : 'h2';
$texto  = ! empty( $args['texto'] ) ? $args['texto'] : '';
$class  = ! empty( $args['class'] ) ? sanitize_html_class( $args['class'] ) : '';
$id     = ! empty( $args['id'] ) ? sanitize_title( $args['id'] ) : '';

$id_attr    = ! empty( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';
$class_attr = 'class="titulo ' . esc_attr( $class ) . '"';
?>
<<?php echo $tag; ?> <?php echo $id_attr; ?> <?php echo $class_attr; ?>>
	<?php echo esc_html( $texto ); ?>
</<?php echo $tag; ?>>
