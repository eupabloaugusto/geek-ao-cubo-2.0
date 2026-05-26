<?php
/**
 * Organism: Seção de Recomendações (secao-recomendacoes)
 *
 * Exibe animes recomendados em trilho horizontal com scroll snap nativo.
 * Cabeçalho com título H2 e link opcional "Ver todas". Sem JavaScript.
 *
 * @package hello-elementor-child
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

$titulo         = isset( $args['titulo'] )         ? esc_html( $args['titulo'] )        : __( 'Recomendações', 'hello-elementor-child' );
$recomendacoes  = isset( $args['recomendacoes'] )  ? (array) $args['recomendacoes']      : array();
$ver_mais_url   = isset( $args['ver_mais_url'] )   ? esc_url( $args['ver_mais_url'] )    : '';
$ver_mais_label = isset( $args['ver_mais_label'] ) ? esc_html( $args['ver_mais_label'] ) : __( 'Ver todas', 'hello-elementor-child' );

if ( empty( $recomendacoes ) ) {
	return;
}
?>

<section class="secao-recomendacoes" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-recomendacoes__inner">

		<!-- =====================================================
		     CABEÇALHO: título + link "Ver todas"
		     ===================================================== -->
		<header class="secao-recomendacoes__header">
			<h2 class="secao-recomendacoes__title"><?php echo $titulo; ?></h2>

			<?php if ( ! empty( $ver_mais_url ) ) : ?>
				<a
					href="<?php echo $ver_mais_url; ?>"
					class="secao-recomendacoes__link-all"
					rel="nofollow noopener"
					target="_blank"
					aria-label="<?php echo esc_attr( sprintf( __( 'Ver todas as recomendações de %s', 'hello-elementor-child' ), $titulo ) ); ?>"
				>
					<span><?php echo $ver_mais_label; ?></span>
					<svg class="secao-recomendacoes__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
						<polyline points="9 18 15 12 9 6"></polyline>
					</svg>
				</a>
			<?php endif; ?>
		</header>

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
