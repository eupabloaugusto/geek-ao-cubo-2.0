<?php
/**
 * Organism: Seção de Anúncios (secao-anuncios)
 *
 * Container semântico reutilizável para blocos de anúncio AdSense.
 * Sem título. Aceita variação, slot e classe customizáveis via $args.
 *
 * @package geek-ao-cubo
 *
 * @param string $variacao Variação do anúncio (default: 'auto'). Ex: 'auto', 'banner', 'leaderboard'.
 * @param string $slot     Identificador do slot AdSense (default: 'secao-anuncios').
 * @param string $class    Classes CSS adicionais para o wrapper.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variacao = isset( $args['variacao'] ) ? esc_attr( $args['variacao'] ) : 'auto';
$slot     = isset( $args['slot'] )     ? esc_attr( $args['slot'] )     : 'secao-anuncios';
$class    = isset( $args['class'] )    ? esc_attr( $args['class'] )    : '';
?>
<section class="secao-anuncios<?php echo $class ? ' ' . $class : ''; ?>">
	<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
		'slot'    => $slot,
		'variacao' => $variacao,
	) ); ?>
</section>
