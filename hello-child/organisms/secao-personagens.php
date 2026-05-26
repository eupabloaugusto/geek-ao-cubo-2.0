<?php
/**
 * Organism: Seção Personagens (secao-personagens)
 *
 * Grade de cards cinematográficos de personagem (card-personagem).
 * Mobile (< 48rem): scroll horizontal com snap.
 * Tablet (≥ 48rem): grid auto-fill minmax(8rem, 1fr).
 * Desktop (≥ 64rem): grid auto-fill minmax(9rem, 1fr).
 *
 * @package hello-elementor-child
 *
 * @param string $titulo      Título da seção. Default: 'Personagens'.
 * @param array  $personagens Array de arrays com os parâmetros de cada card-personagem:
 *                            name, name_kanji, image_url, role, url.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo      = isset( $args['titulo'] )      ? esc_html( $args['titulo'] )    : __( 'Personagens', 'hello-elementor-child' );
$personagens = isset( $args['personagens'] ) ? (array) $args['personagens']   : array();

if ( empty( $personagens ) ) {
	return;
}
?>

<section class="secao-personagens" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-personagens__inner">

		<h2 class="secao-personagens__titulo"><?php echo $titulo; ?></h2>

		<div class="secao-personagens__grid">
			<?php foreach ( $personagens as $personagem ) : ?>
				<?php
				mm_render_component( 'molecules', 'card-personagem', (array) $personagem );
				?>
			<?php endforeach; ?>
		</div>

	</div>
</section>
