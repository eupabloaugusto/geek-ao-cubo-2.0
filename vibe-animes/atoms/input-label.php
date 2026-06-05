<?php
/**
 * Atom: Rótulo de Input (input-label)
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$label = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$for   = isset( $args['for'] ) ? esc_attr( $args['for'] ) : '';
$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

if ( empty( $label ) ) {
	return;
}
?>

<label class="input-label <?php echo $class; ?>" <?php echo ! empty( $for ) ? 'for="' . $for . '"' : ''; ?>>
	<?php echo $label; ?>
</label>
