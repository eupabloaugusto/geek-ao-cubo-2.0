<?php
/**
 * Atom: Aviso Adblock
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
?>

<div class="aviso-adblock <?php echo $class; ?>" role="alert" aria-live="polite">
	<button class="aviso-adblock__close" aria-label="<?php esc_attr_e( 'Fechar aviso', 'geek-ao-cubo' ); ?>">&times;</button>
	<div class="aviso-adblock__header">
		<div class="aviso-adblock__icon" aria-hidden="true">⚡</div>
		<h4 class="aviso-adblock__title"><?php _e( 'Apoie o Geek ao Cubo!', 'geek-ao-cubo' ); ?></h4>
	</div>
	
	<p class="aviso-adblock__text">
		<?php _e( 'Detectamos que você está usando Adblock. Nosso portal é gratuito e mantido por anúncios. Considere desativar o bloqueador para nos apoiar, ou conheça nossa vitrine de afiliados com ofertas incríveis de animes e mangás!', 'geek-ao-cubo' ); ?>
	</p>
	
	<div class="aviso-adblock__buttons">
		<?php 
		// Renderiza o botão primário para o guia de desativação
		mm_render_component( 'atoms', 'btn-primary', array(
			'label' => __( 'Como Desativar', 'geek-ao-cubo' ),
			'url'   => '#como-desativar',
		) ); 
		
		// Renderiza o botão secundário para a vitrine de ofertas
		mm_render_component( 'atoms', 'btn-secondary', array(
			'label' => __( 'Ver Mangás / Ofertas', 'geek-ao-cubo' ),
			'url'   => '#vitrine-afiliados',
		) ); 
		?>
	</div>
</div>
