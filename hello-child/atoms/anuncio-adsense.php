<?php
/**
 * Atom: Container de Anúncio Adsense (anuncio-adsense)
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$slot  = isset( $args['slot'] ) ? esc_attr( $args['slot'] ) : 'default';
$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
?>

<div class="anuncio-adsense <?php echo $class; ?>" data-slot="<?php echo $slot; ?>">
	<!-- Tag de indicação técnica de anúncio -->
	<span class="anuncio-adsense__label"><?php _e( 'Publicidade', 'hello-elementor-child' ); ?></span>
	
	<div class="anuncio-adsense__container">
		<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
			<!-- Exibição amigável em modo de desenvolvimento local -->
			<div class="anuncio-adsense__placeholder">
				<span class="anuncio-adsense__slot-info">AdSense Slot: <?php echo $slot; ?></span>
				<span class="anuncio-adsense__size-info">Responsivo / Fluido</span>
			</div>
		<?php else : ?>
			<!-- Código assíncrono oficial simplificado do Google AdSense -->
			<ins class="adsbygoogle"
				 style="display:block"
				 data-ad-client="ca-pub-XXXXXXXXXXXXXXXX"
				 data-ad-slot="<?php echo $slot; ?>"
				 data-ad-format="auto"
				 data-full-width-responsive="true"></ins>
			<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		<?php endif; ?>
	</div>
</div>
