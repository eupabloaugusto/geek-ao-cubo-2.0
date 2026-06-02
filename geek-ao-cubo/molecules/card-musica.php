<?php
/**
 * Molecule: Card de Música
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo = isset( $args['titulo'] ) ? wp_kses_post( $args['titulo'] ) : '';
$label  = isset( $args['label'] )  ? esc_html( $args['label'] )  : __( 'Música', 'geek-ao-cubo' );

if ( empty( $titulo ) ) {
	return;
}
?>
<div class="card-musica-wrapper">
	<span class="card-musica__label"><?php echo $label; ?></span>
	<div class="card-musica">
		<div class="card-musica__icon-wrapper">
			<svg class="card-musica__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M9 18V5l12-2v13"></path>
				<circle cx="6" cy="18" r="3"></circle>
				<circle cx="18" cy="16" r="3"></circle>
			</svg>
		</div>
		<div class="card-musica__content">
			<h4 class="card-musica__title"><?php echo $titulo; ?></h4>
		</div>
	</div>
</div>
