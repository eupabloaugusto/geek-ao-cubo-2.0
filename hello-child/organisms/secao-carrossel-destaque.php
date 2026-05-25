<?php
/**
 * Organism: Seção de Carrossel de Destaques (secao-carrossel-destaque)
 *
 * Exibe até 4 cards da Variação Destaque Horizontal (hero) rotacionando de forma fluida.
 * Utiliza o trilho Scroll Snap nativo de altíssima performance para Core Web Vitals.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts_carousel = isset( $args['posts_carousel'] ) ? $args['posts_carousel'] : array();

if ( empty( $posts_carousel ) || ! is_array( $posts_carousel ) ) {
	return;
}

// Limita rigorosamente ao máximo de 4 slides de Destaque
$posts_carousel = array_slice( $posts_carousel, 0, 4 );
$total_posts    = count( $posts_carousel );
$carousel_id    = 'carousel-destaque-' . wp_rand( 1000, 9999 );
?>
<section class="secao-carrossel-destaque js-carousel-container" id="<?php echo esc_attr( $carousel_id ); ?>" aria-label="<?php esc_attr_e( 'Carrossel de Notícias em Destaque', 'hello-elementor-child' ); ?>">
	
	<!-- Trilho de rolagem horizontal física/gestual (Scroll Snap Track) -->
	<div class="secao-carrossel-destaque__track js-carousel-track">
		<?php 
		$index = 0;
		foreach ( $posts_carousel as $post_args ) {
			// Força a variação Destaque / Hero Horizontal para este carrossel
			$post_args['variacao'] = 'hero';
			?>
			<div class="secao-carrossel-destaque__slide js-carousel-slide" data-slide-index="<?php echo $index; ?>">
				<?php mm_render_component( 'molecules', 'card-noticia', $post_args ); ?>
			</div>
			<?php
			$index++;
		}
		?>
	</div>

	<!-- Trilho Inferior Unificado de Navegação (Molecule) -->
	<div class="secao-carrossel-destaque__nav">
		<?php 
		mm_render_component( 'molecules', 'carousel-nav', array(
			'total'        => $total_posts,
			'active_index' => 0,
			'show_arrows'  => true,
			'carousel_id'  => $carousel_id
		) );
		?>
	</div>

</section>
