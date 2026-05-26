<?php
/**
 * Organism: Seção Dubladores (secao-dubladores)
 *
 * Exibe uma grade de cards de voice actors (card-personagem-dublador).
 * Desktop (≥ 64rem): grid de 4 colunas, cada card horizontal.
 * Tablet + Mobile (< 64rem): scroll horizontal com snap.
 *
 * @package hello-elementor-child
 *
 * @param string $titulo      Título da seção. Default: 'Dubladores'.
 * @param array  $dubladores  Array de arrays com os parâmetros de cada card:
 *                            va_name, va_image, va_url, va_language,
 *                            character_name, episodios, ano_inicio, ano_fim.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo    = isset( $args['titulo'] )    ? esc_html( $args['titulo'] )  : __( 'Dubladores', 'hello-elementor-child' );
$dubladores = isset( $args['dubladores'] ) ? (array) $args['dubladores'] : array();

if ( empty( $dubladores ) ) {
	return;
}
?>

<section class="secao-dubladores" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-dubladores__inner">

		<h2 class="secao-dubladores__titulo"><?php echo $titulo; ?></h2>

		<div class="secao-dubladores__grid">
			<?php foreach ( $dubladores as $dublador ) : ?>
				<?php
				mm_render_component( 'molecules', 'card-personagem-dublador', (array) $dublador );
				?>
			<?php endforeach; ?>
		</div>

	</div>
</section>
