<?php
/**
 * Organism: Seção Trilha Sonora
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$openings = isset( $args['openings'] ) ? (array) $args['openings'] : array();
$endings  = isset( $args['endings'] ) ? (array) $args['endings'] : array();

if ( empty( $openings ) && empty( $endings ) ) {
	return;
}
?>
<section class="secao-trilha-sonora">
	<?php mm_render_component( 'organisms', 'secao-titulo', array(
		'titulo' => __( 'Trilha Sonora', 'geek-ao-cubo' ),
	) ); ?>
	
	<div class="secao-trilha-sonora__grid">
		<?php 
		// Renderiza todas as Aberturas
		if ( ! empty( $openings ) ) {
			foreach ( $openings as $op ) {
				mm_render_component( 'molecules', 'card-musica', array(
					'titulo' => $op,
					'label'  => __( 'Abertura', 'geek-ao-cubo' )
				) );
			}
		}

		// Renderiza todos os Encerramentos
		if ( ! empty( $endings ) ) {
			foreach ( $endings as $ed ) {
				mm_render_component( 'molecules', 'card-musica', array(
					'titulo' => $ed,
					'label'  => __( 'Encerramento', 'geek-ao-cubo' )
				) );
			}
		}
		?>
	</div>
</section>
