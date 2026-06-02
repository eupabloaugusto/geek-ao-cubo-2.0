<?php
/**
 * Atom: Texto Auxiliar / Mensagem de Erro (input-helper)
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$text     = isset( $args['text'] ) ? esc_html( $args['text'] ) : '';
$is_error = isset( $args['is_error'] ) ? (bool) $args['is_error'] : false;
$class    = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$id       = isset( $args['id'] ) ? esc_attr( $args['id'] ) : '';

if ( empty( $text ) ) {
	return;
}

$helper_class = 'input-helper';
if ( $is_error ) {
	$helper_class .= ' input-helper--error';
}
?>

<p <?php echo ! empty( $id ) ? 'id="' . $id . '"' : ''; ?> class="<?php echo $helper_class; ?> <?php echo $class; ?>" <?php echo $is_error ? 'role="alert" aria-live="assertive"' : ''; ?>>
	<?php echo $text; ?>
</p>
