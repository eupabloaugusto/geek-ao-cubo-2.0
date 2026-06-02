<?php
/**
 * Organism: Seção de Atributos em Grid (secao-atributos-grid)
 *
 * Exibe uma lista de atributos como cards.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : '';
$items  = isset( $args['items'] ) ? (array) $args['items'] : array();

if ( empty( $items ) ) {
	return;
}
?>

<section class="secao-atributos-grid" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<?php if ( ! empty( $titulo ) ) : ?>
		<h2 class="secao-atributos-grid__titulo"><?php echo esc_html( $titulo ); ?></h2>
	<?php endif; ?>

	<div class="secao-atributos-grid__list">
		<?php foreach ( $items as $item ) : ?>
			<?php mm_render_component( 'molecules', 'card-atributo', $item ); ?>
		<?php endforeach; ?>
	</div>
</section>
