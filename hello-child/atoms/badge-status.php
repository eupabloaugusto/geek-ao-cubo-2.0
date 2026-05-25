<?php
/**
 * Atom: Badge de Status
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Fallbacks e higienização
$status = isset( $args['status'] ) ? sanitize_key( $args['status'] ) : 'completed';
$class  = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Labels padrões com internacionalização baseada no status
$status_labels = array(
	'airing'    => __( 'Em exibição', 'hello-elementor-child' ),
	'completed' => __( 'Finalizado', 'hello-elementor-child' ),
	'upcoming'  => __( 'Em breve', 'hello-elementor-child' ),
);

$label = isset( $args['label'] ) ? esc_html( $args['label'] ) : ( isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : ucfirst( $status ) );
?>

<span class="badge-status badge-status--<?php echo $status; ?> <?php echo $class; ?>">
	<?php echo $label; ?>
</span>
