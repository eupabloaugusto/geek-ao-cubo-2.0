<?php
/**
 * Atom: Nota MAL (MyAnimeList)
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Fallback e higienização da nota
$nota  = isset( $args['nota'] ) ? esc_html( $args['nota'] ) : 'N/A';
$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Lógica dinâmica para notas abaixo de 5.0 (Error Color)
$score_modifier = '';
if ( is_numeric( $nota ) && (float) $nota < 5.0 ) {
	$score_modifier = 'nota-mal--error';
}
?>

<span class="nota-mal <?php echo $score_modifier; ?> <?php echo $class; ?>" title="<?php echo esc_attr( __( 'Nota no MyAnimeList', 'geek-ao-cubo' ) ); ?>">
	<svg class="nota-mal__icon" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
		<path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
	</svg>
	<span class="nota-mal__val"><?php echo $nota; ?></span>
</span>
