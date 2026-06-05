<?php
/**
 * Organism: Seção de Recomendações (secao-recomendacoes)
 *
 * Exibe animes recomendados em trilho horizontal com scroll snap nativo.
 * Cabeçalho com título H2 e link opcional "Ver todas". Sem JavaScript.
 *
 * @package geek-ao-cubo
 *
 * @param string $titulo         Título da seção. Default: 'Recomendações'.
 * @param array  $recomendacoes  Array de arrays com parâmetros de cada card-recomendacao:
 *                               anime_title (string, required), anime_image, anime_url, rec_count.
 * @param string $ver_mais_url   URL do link "Ver todas" no cabeçalho (opcional).
 * @param string $ver_mais_label Label do link. Default: 'Ver todas'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo         = isset( $args['titulo'] )         ? esc_html( $args['titulo'] )        : __( 'Recomendações', 'geek-ao-cubo' );
$recomendacoes  = isset( $args['recomendacoes'] )  ? (array) $args['recomendacoes']      : array();
$ver_mais_url   = isset( $args['ver_mais_url'] )   ? esc_url( $args['ver_mais_url'] )    : '';
$ver_mais_label = isset( $args['ver_mais_label'] ) ? esc_html( $args['ver_mais_label'] ) : __( 'Ver todas', 'geek-ao-cubo' );

if ( empty( $recomendacoes ) ) {
	return;
}
?>

<section class="secao-recomendacoes" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-recomendacoes__inner">

		<!-- =====================================================
		     CABEÇALHO: título + link "Ver todas"
		     ===================================================== -->
		<?php mm_render_component( 'organisms', 'secao-titulo', array(
			'titulo'       => $titulo,
			'ver_tudo_url' => $ver_mais_url,
			'ver_tudo_lbl' => $ver_mais_label,
		) ); ?>

		<!-- =====================================================
		     TRILHO HORIZONTAL com setas e scroll infinito
		     ===================================================== -->
		<?php
		ob_start();
		foreach ( $recomendacoes as $rec ) :
			echo '<div class="secao-recomendacoes__item js-trilho__slide">';
			mm_render_component( 'molecules', 'card-recomendacao', (array) $rec );
			echo '</div>';
		endforeach;
		$track_html = ob_get_clean();

		mm_render_component( 'molecules', 'trilho-infinito', array(
			'track_html'  => $track_html,
			'class'       => 'secao-recomendacoes__wrapper',
			'track_class' => 'secao-recomendacoes__trilho',
		) );
		?>

	</div>
</section>
