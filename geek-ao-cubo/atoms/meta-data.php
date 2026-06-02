<?php
/**
 * Atom: Meta Data (meta-data)
 *
 * Exibe a data de publicação de um post, formatada e acompanhada de um ícone de calendário.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$date_display = isset( $args['date'] ) ? esc_html( $args['date'] ) : '';
$datetime     = isset( $args['datetime'] ) ? esc_attr( $args['datetime'] ) : '';
$class        = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$show_icon    = ! isset( $args['show_icon'] ) || (bool) $args['show_icon'];

// Fallback elegante para o loop do WordPress
if ( empty( $date_display ) && function_exists( 'get_the_date' ) ) {
	$date_display = get_the_date();
}
if ( empty( $datetime ) && function_exists( 'get_the_date' ) ) {
	$datetime = get_the_date( 'c' );
}

// Se não houver data a exibir, impede a renderização
if ( empty( $date_display ) ) {
	return;
}
?>
<time class="meta-data <?php echo $class; ?>" datetime="<?php echo $datetime; ?>">
	<?php if ( $show_icon ) : ?>
		<span class="meta-data__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
			</svg>
		</span>
	<?php endif; ?>
	<span class="meta-data__text"><?php echo $date_display; ?></span>
</time>
