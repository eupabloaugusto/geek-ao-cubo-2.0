<?php
/**
 * Molecule: Trilho Infinito (trilho-infinito)
 *
 * Wrapper reutilizável de scroll horizontal infinito.
 * Renderiza setas btn-nav-arrow (prev/next) + trilho rolável com slot de conteúdo.
 * O scroll infinito (clonagem de slides), drag-to-scroll e cliques nas setas são
 * controlados pelo JS compartilhado (trilho-infinito.js).
 *
 * @package geek-ao-cubo
 *
 * @param string $track_html HTML pré-renderizado dos slides.
 *                           Cada slide deve ter a classe .js-trilho__slide.
 * @param string $class      Classe(s) extra(s) para o wrapper externo (opcional).
 * @param string $track_class Classe(s) extra(s) para o div do trilho (opcional).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$track_html  = isset( $args['track_html'] )  ? $args['track_html']              : '';
$extra_class = isset( $args['class'] )       ? ' ' . esc_attr( $args['class'] ) : '';
$track_class = isset( $args['track_class'] ) ? ' ' . esc_attr( $args['track_class'] ) : '';

if ( empty( $track_html ) ) {
	return;
}
?>

<div class="trilho-infinito js-trilho<?php echo $extra_class; ?>">

	<?php
	mm_render_component( 'atoms', 'btn-nav-arrow', array(
		'direction' => 'prev',
		'class'     => 'trilho-infinito__arrow trilho-infinito__arrow--prev js-trilho__prev',
	) );
	?>

	<div class="trilho-infinito__track js-trilho__track<?php echo $track_class; ?>">
		<?php echo $track_html; ?>
	</div>

	<?php
	mm_render_component( 'atoms', 'btn-nav-arrow', array(
		'direction' => 'next',
		'class'     => 'trilho-infinito__arrow trilho-infinito__arrow--next js-trilho__next',
	) );
	?>

</div>
