<?php
/**
 * Organism: Seção Conteúdo Relacionado (secao-relacionados)
 *
 * Agrupa itens de animes/mangás relacionados por seu tipo de relação e exibe
 * em grades responsivas organizadas por subtítulos.
 *
 * @package hello-elementor-child
 *
 * @param string $titulo Título principal da seção. Default: 'Conteúdo Relacionado'.
 * @param array  $items  Array plano contendo os itens de relação:
 *                       anime_title (string, required), anime_image, anime_url, relation_type.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : __( 'Conteúdo Relacionado', 'hello-elementor-child' );
$items  = isset( $args['items'] )  ? (array) $args['items']      : array();

if ( empty( $items ) ) {
	return;
}

// Agrupamento dinâmico por relation_type
$grouped = array();
foreach ( $items as $item ) {
	if ( empty( $item['anime_title'] ) ) {
		continue;
	}
	$relation = ! empty( $item['relation_type'] ) ? esc_html( $item['relation_type'] ) : __( 'Outros', 'hello-elementor-child' );
	$grouped[ $relation ][] = $item;
}

if ( empty( $grouped ) ) {
	return;
}
?>

<section class="secao-relacionados" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-relacionados__inner">

		<h2 class="secao-relacionados__title"><?php echo $titulo; ?></h2>

		<div class="secao-relacionados__groups">
			<?php foreach ( $grouped as $relation_name => $group_items ) : ?>
				<div class="secao-relacionados__group">
					<h3 class="secao-relacionados__group-title"><?php echo $relation_name; ?></h3>
					<div class="secao-relacionados__grid">
						<?php foreach ( $group_items as $group_item ) : ?>
							<?php mm_render_component( 'molecules', 'relacionado-item', $group_item ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
