<?php
/**
 * Atom: Imagem de Capa
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Fallbacks e higienização de parâmetros
$src          = isset( $args['src'] ) ? esc_url( $args['src'] ) : '';
$alt          = isset( $args['alt'] ) ? esc_attr( $args['alt'] ) : __( 'Capa de anime do blog Geek ao Cubo', 'geek-ao-cubo' );
$class        = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$mostrar_nota = isset( $args['mostrar_nota'] ) ? (bool) $args['mostrar_nota'] : false;
$nota         = isset( $args['nota'] ) ? esc_html( $args['nota'] ) : '';
$horario      = isset( $args['horario'] ) ? esc_html( $args['horario'] ) : '';

?>

<div class="imagem-capa-container <?php echo $class; ?>">
	<?php if ( ! empty( $horario ) ) : ?>
		<div class="imagem-capa-horario">
			<?php 
			// Renderiza o badge de horário
			mm_render_component( 'atoms', 'badge-horario', array( 'horario' => $horario ) ); 
			?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $src ) ) : ?>
		<img class="imagem-capa" 
			 src="<?php echo $src; ?>" 
			 alt="<?php echo $alt; ?>" 
			 loading="lazy" 
			 decoding="async" />
	<?php else : ?>
		<!-- Placeholder de imagem premium se não houver capa -->
		<div class="imagem-capa-placeholder" aria-label="<?php echo esc_attr( __( 'Capa não disponível', 'geek-ao-cubo' ) ); ?>">
			<span class="imagem-capa-placeholder__text"><?php _e( 'Sem Imagem', 'geek-ao-cubo' ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( $mostrar_nota && ! empty( $nota ) ) : ?>
		<div class="imagem-capa-overlay">
			<?php 
			// Renderiza a nota do MAL como componente interno
			mm_render_component( 'atoms', 'nota-mal', array( 'nota' => $nota ) ); 
			?>
		</div>
	<?php endif; ?>
</div>
