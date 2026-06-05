<?php
/**
 * Organism: Seção de Estatísticas (secao-estatisticas)
 *
 * Exibe blocos de estatísticas (stat-bloco) em trilho horizontal com
 * scroll snap nativo, idêntico ao padrão de secao-esteira-animes.
 * Cada slide contém um stat-bloco (score + rank + popularidade + membros).
 * Sem JavaScript.
 *
 * @package geek-ao-cubo
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

$titulo         = isset( $args['titulo'] )         ? esc_html( $args['titulo'] )        : __( 'Estatísticas', 'geek-ao-cubo' );
$estatisticas   = isset( $args['estatisticas'] )   ? (array) $args['estatisticas']       : array();
$ver_mais_url   = isset( $args['ver_mais_url'] )   ? esc_url( $args['ver_mais_url'] )    : '';
$ver_mais_label = isset( $args['ver_mais_label'] ) ? esc_html( $args['ver_mais_label'] ) : __( 'Ver no MAL', 'geek-ao-cubo' );

if ( empty( $estatisticas ) ) {
	return;
}
?>

<section class="secao-estatisticas" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-estatisticas__inner">

		<!-- =====================================================
		     CABEÇALHO: título + link opcional
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
