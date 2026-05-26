<?php
/**
 * Atom: Aviso Adblock
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
?>

<div class="aviso-adblock <?php echo $class; ?>" role="alert" aria-live="polite">
	<button class="aviso-adblock__close" aria-label="<?php esc_attr_e( 'Fechar aviso', 'hello-elementor-child' ); ?>">&times;</button>
	<div class="aviso-adblock__header">
		<div class="aviso-adblock__icon" aria-hidden="true">⚡</div>
		<h4 class="aviso-adblock__title"><?php _e( 'Apoie o Geek ao Cubo!', 'hello-elementor-child' ); ?></h4>
	</div>
	
	<p class="aviso-adblock__text">
		<?php _e( 'Detectamos que você está usando Adblock. Nosso portal é gratuito e mantido por anúncios. Considere desativar o bloqueador para nos apoiar, ou conheça nossa vitrine de afiliados com ofertas incríveis de animes e mangás!', 'hello-elementor-child' ); ?>
	</p>
	
	<div class="aviso-adblock__buttons">
		<?php 
		// Renderiza o botão primário para o guia de desativação
		mm_render_component( 'atoms', 'btn-primary', array(
			'label' => __( 'Como Desativar', 'hello-elementor-child' ),
			'url'   => '#como-desativar',
		) ); 
		
		// Renderiza o botão secundário para a vitrine de ofertas
		mm_render_component( 'atoms', 'btn-secondary', array(
			'label' => __( 'Ver Mangás / Ofertas', 'hello-elementor-child' ),
			'url'   => '#vitrine-afiliados',
		) ); 
		?>
	</div>
</div>
