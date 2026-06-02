<?php
/**
 * Organism: Seção de Trailers (secao-trailers)
 *
 * Exibe trailers e PVs de um anime em formato de slide scroll-snap sem autoplay.
 * Com 1 vídeo: renderiza o atom embed-video diretamente (melhor performance/SEO).
 * Com 2+ vídeos: slider com navegação por labels textuais (títulos dos vídeos).
 *
 * @package geek-ao-cubo
 *
 * @param array  $args['videos']      Array normalizado: [['id', 'title', 'thumb'], ...]
 * @param string $args['anime_title'] Título do anime (para aria-label de acessibilidade)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$videos      = isset( $args['videos'] ) && is_array( $args['videos'] ) ? $args['videos'] : array();
$anime_title = isset( $args['anime_title'] ) ? esc_attr( $args['anime_title'] ) : '';
$total       = count( $videos );

if ( 0 === $total ) {
	return;
}

// — 1 vídeo: atom direto, sem slider overhead —
if ( 1 === $total ) {
	$v = $videos[0];
	mm_render_component( 'atoms', 'embed-video', array(
		'video_id'        => $v['id'],
		'placeholder_url' => $v['thumb'],
		'title'           => $v['title'] . ( $anime_title ? ' — ' . $anime_title : '' ),
	) );
	return;
}

// — 2+ vídeos: slider completo —
$carousel_id = 'trailers-' . wp_rand( 1000, 9999 );
?>
<section
	class="secao-trailers js-trailers-container"
	id="<?php echo esc_attr( $carousel_id ); ?>"
	aria-label="<?php echo esc_attr( sprintf( __( 'Trailers de %s', 'geek-ao-cubo' ), $anime_title ) ); ?>"
>

	<!-- Trilho scroll-snap -->
	<div class="secao-trailers__track js-trailers-track">
		<?php foreach ( $videos as $index => $v ) : ?>
			<div
				class="secao-trailers__slide js-trailers-slide"
				data-slide-index="<?php echo (int) $index; ?>"
			>
				<?php
				mm_render_component( 'atoms', 'embed-video', array(
					'video_id'        => esc_attr( $v['id'] ),
					'placeholder_url' => $v['thumb'],
					'title'           => esc_attr( $v['title'] ) . ( $anime_title ? ' — ' . $anime_title : '' ),
				) );
				?>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Barra de navegação: setas + pills de label -->
	<div class="secao-trailers__nav" role="group" aria-label="<?php esc_attr_e( 'Navegação de trailers', 'geek-ao-cubo' ); ?>">

		<!-- Seta anterior -->
		<?php mm_render_component( 'atoms', 'btn-nav-arrow', array(
			'direction' => 'prev',
			'class'     => 'secao-trailers__arrow js-trailers-prev',
		) ); ?>

		<!-- Pills de label -->
		<div class="secao-trailers__labels" role="tablist">
			<?php foreach ( $videos as $index => $v ) : ?>
				<button
					type="button"
					role="tab"
					class="secao-trailers__label<?php echo 0 === $index ? ' is-active' : ''; ?>"
					data-slide="<?php echo (int) $index; ?>"
					aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $carousel_id ); ?>"
				><?php echo esc_html( $v['title'] ); ?></button>
			<?php endforeach; ?>
		</div>

		<!-- Seta próxima -->
		<?php mm_render_component( 'atoms', 'btn-nav-arrow', array(
			'direction' => 'next',
			'class'     => 'secao-trailers__arrow js-trailers-next',
		) ); ?>

	</div>

</section>
