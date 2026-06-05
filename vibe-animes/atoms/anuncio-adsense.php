<?php
/**
 * Atom: Container de Anúncio Adsense (anuncio-adsense)
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$slot        = isset( $args['slot'] ) ? esc_attr( $args['slot'] ) : 'default';
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$variacao    = isset( $args['variacao'] ) ? esc_attr( $args['variacao'] ) : 'auto';
$visibilidade = isset( $args['visibilidade'] ) ? esc_attr( $args['visibilidade'] ) : 'all'; // 'all' | 'mobile' | 'desktop'

// Mapeia variação → atributos do AdSense e label de tamanho para debug
$variacao_map = array(
	'auto'             => array( 'format' => 'auto',      'layout' => '',           'full_width' => 'true',  'label_tamanho' => 'Responsivo / Auto' ),
	'leaderboard'      => array( 'format' => 'horizontal', 'layout' => '',           'full_width' => 'true',  'label_tamanho' => '728×90 → 320×50 (Horizontal)' ),
	'banner'           => array( 'format' => 'horizontal', 'layout' => '',           'full_width' => 'true',  'label_tamanho' => 'Banner Horizontal (Full Width)' ),
	'retangulo'        => array( 'format' => 'rectangle',  'layout' => '',           'full_width' => 'false', 'label_tamanho' => '300×250 (Médio Retângulo)' ),
	'retangulo-grande' => array( 'format' => 'rectangle',  'layout' => '',           'full_width' => 'false', 'label_tamanho' => '336×280 (Grande Retângulo)' ),
	'meia-pagina'      => array( 'format' => 'vertical',   'layout' => '',           'full_width' => 'false', 'label_tamanho' => '300×600 (Meia Página)' ),
	'quadrado'         => array( 'format' => 'rectangle',  'layout' => '',           'full_width' => 'false', 'label_tamanho' => '250×250 (Quadrado)' ),
	'artigo'           => array( 'format' => 'fluid',      'layout' => 'in-article', 'full_width' => 'true',  'label_tamanho' => 'In-Article (Nativo Fluido)' ),
	'multiplex'        => array( 'format' => 'autorelaxed','layout' => '',           'full_width' => 'true',  'label_tamanho' => 'Multiplex / Relax (Grade Nativa)' ),
	'in-feed'          => array( 'format' => 'fluid',      'layout' => 'in-feed',    'full_width' => 'true',  'label_tamanho' => 'In-Feed (Nativo de Lista)' ),
);

$ad_config = isset( $variacao_map[ $variacao ] ) ? $variacao_map[ $variacao ] : $variacao_map['auto'];
$container_class = 'anuncio-adsense anuncio-adsense--' . $variacao . ( $class ? ' ' . $class : '' );
if ( in_array( $visibilidade, array( 'mobile', 'desktop' ), true ) ) {
	$container_class .= ' anuncio-adsense--' . $visibilidade . '-only';
}
?>

<div class="<?php echo esc_attr( $container_class ); ?>" data-slot="<?php echo $slot; ?>" data-variacao="<?php echo $variacao; ?>" aria-hidden="true" role="complementary">
	<span class="anuncio-adsense__label"><?php _e( 'Publicidade', 'geek-ao-cubo' ); ?></span>

	<div class="anuncio-adsense__frame">
		<div class="anuncio-adsense__container">
			<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
				<div class="anuncio-adsense__placeholder">
					<span class="anuncio-adsense__slot-info">Slot: <?php echo $slot; ?></span>
					<span class="anuncio-adsense__size-info"><?php echo esc_html( $ad_config['label_tamanho'] ); ?></span>
				</div>
			<?php else : ?>
				<ins class="adsbygoogle"
					 style="display:block<?php echo $ad_config['layout'] === 'in-article' ? ';text-align:center' : ''; ?>"
					 data-ad-client="ca-pub-XXXXXXXXXXXXXXXX"
					 data-ad-slot="<?php echo $slot; ?>"
					 data-ad-format="<?php echo esc_attr( $ad_config['format'] ); ?>"
					 data-full-width-responsive="<?php echo esc_attr( $ad_config['full_width'] ); ?>"
					 <?php if ( ! empty( $ad_config['layout'] ) ) : ?>data-ad-layout="<?php echo esc_attr( $ad_config['layout'] ); ?>"<?php endif; ?>></ins>
				<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
			<?php endif; ?>
		</div>
	</div>
</div>
