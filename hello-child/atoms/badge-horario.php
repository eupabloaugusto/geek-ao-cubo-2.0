<?php
/**
 * Atom: Badge de Horário
 *
 * Exibe o horário de lançamento de um episódio (ex: "21:00") com um ícone de relógio SVG embutido.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Fallback e higienização
$horario = isset( $args['horario'] ) ? esc_html( $args['horario'] ) : '';
$class   = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

if ( empty( $horario ) ) {
	return;
}
?>

<span class="badge-horario <?php echo $class; ?>" title="<?php echo esc_attr( sprintf( __( 'Episódio às %s', 'hello-elementor-child' ), $horario ) ); ?>">
	<svg class="badge-horario__icon" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
		<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
	</svg>
	<span class="badge-horario__text"><?php echo $horario; ?></span>
</span>
