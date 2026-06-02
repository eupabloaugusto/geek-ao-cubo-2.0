<?php
/**
 * Organism: Seção de Obras do Personagem (secao-obras-personagem)
 *
 * Exibe a lista de animes em que o personagem atua em uma grid responsiva.
 * Possui navegação (paginação) dinâmica computada:
 * - Desktop: limite inicial de 3 linhas (12 animes).
 * - Mobile: limite inicial de 4 animes.
 * Inclui botões "Ver mais" e "Ver menos".
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : __( 'Obras em que atua', 'geek-ao-cubo' );
$obras  = isset( $args['obras'] ) ? (array) $args['obras'] : array();

if ( empty( $obras ) ) {
	return;
}
?>

<section class="secao-obras-personagem js-obras-container">
	<?php if ( ! empty( $titulo ) ) : ?>
		<?php mm_render_component( 'organisms', 'secao-titulo', array(
			'titulo' => $titulo,
		) ); ?>
	<?php endif; ?>

	<div class="secao-obras-personagem__grid js-obras-grid">
		<?php foreach ( $obras as $index => $obra ) : ?>
			<div class="secao-obras-personagem__item js-obras-card" data-index="<?php echo esc_attr( $index ); ?>">
				<?php mm_render_component( 'molecules', 'card-anime-personagem', array(
					'title'     => $obra['title'],
					'image_url' => $obra['image_url'],
					'permalink' => $obra['permalink'],
					'role'      => $obra['role'],
				) ); ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $obras ) > 4 ) : ?>
		<div class="secao-obras-personagem__actions js-obras-actions" style="display: none;" data-current="0" data-total="<?php echo esc_attr( count( $obras ) ); ?>">
			<div class="secao-obras-personagem__less-wrapper js-obras-less" style="display: none;">
				<?php mm_render_component( 'atoms', 'btn-secondary', array(
					'label' => __( 'Ver menos', 'geek-ao-cubo' ),
					'class' => 'secao-obras-personagem__btn-less',
					'type'  => 'button'
				) ); ?>
			</div>
			<?php mm_render_component( 'atoms', 'btn-secondary', array(
				'label' => __( 'Ver mais obras', 'geek-ao-cubo' ),
				'class' => 'js-obras-more',
				'type'  => 'button'
			) ); ?>
		</div>
	<?php endif; ?>
</section>
