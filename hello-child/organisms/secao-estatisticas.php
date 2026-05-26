<?php
/**
 * Organism: Seção de Estatísticas (secao-estatisticas)
 *
 * Exibe blocos de estatísticas (stat-bloco) em trilho horizontal com
 * scroll snap nativo, idêntico ao padrão de secao-esteira-animes.
 * Cada slide contém um stat-bloco (score + rank + popularidade + membros).
 * Sem JavaScript.
 *
 * @package hello-elementor-child
 *
 * @param string $titulo        Título da seção. Default: 'Estatísticas'.
 * @param array  $estatisticas  Array de arrays com parâmetros de cada stat-bloco:
 *                              score, score_label, score_votes, rank, rank_label,
 *                              popularity, pop_label, members, members_label, class.
 * @param string $ver_mais_url  URL do link no cabeçalho (opcional).
 * @param string $ver_mais_label Label do link. Default: 'Ver no MAL'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo         = isset( $args['titulo'] )         ? esc_html( $args['titulo'] )        : __( 'Estatísticas', 'hello-elementor-child' );
$estatisticas   = isset( $args['estatisticas'] )   ? (array) $args['estatisticas']       : array();
$ver_mais_url   = isset( $args['ver_mais_url'] )   ? esc_url( $args['ver_mais_url'] )    : '';
$ver_mais_label = isset( $args['ver_mais_label'] ) ? esc_html( $args['ver_mais_label'] ) : __( 'Ver no MAL', 'hello-elementor-child' );

if ( empty( $estatisticas ) ) {
	return;
}
?>

<section class="secao-estatisticas" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-estatisticas__inner">

		<!-- =====================================================
		     CABEÇALHO: título + link opcional
		     ===================================================== -->
		<header class="secao-estatisticas__header">
			<h2 class="secao-estatisticas__title"><?php echo $titulo; ?></h2>

			<?php if ( ! empty( $ver_mais_url ) ) : ?>
				<a
					href="<?php echo $ver_mais_url; ?>"
					class="secao-estatisticas__link-all"
					rel="nofollow noopener"
					target="_blank"
					aria-label="<?php echo esc_attr( sprintf( __( 'Ver estatísticas completas de %s', 'hello-elementor-child' ), $titulo ) ); ?>"
				>
					<span><?php echo $ver_mais_label; ?></span>
					<svg class="secao-estatisticas__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
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
		foreach ( $estatisticas as $bloco ) :
			echo '<div class="secao-estatisticas__item js-trilho__slide">';
			mm_render_component( 'molecules', 'stat-bloco', (array) $bloco );
			echo '</div>';
		endforeach;
		$track_html = ob_get_clean();

		mm_render_component( 'molecules', 'trilho-infinito', array(
			'track_html'  => $track_html,
			'class'       => 'secao-estatisticas__wrapper',
			'track_class' => 'secao-estatisticas__trilho',
		) );
		?>

	</div>
</section>
