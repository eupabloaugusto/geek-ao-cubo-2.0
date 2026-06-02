<?php
/**
 * Molecule: Card de Notícia Relacionada (card-noticia-relacionada)
 *
 * Card horizontal premium inserido no meio do texto do artigo.
 * Combina imagem-capa (esq) com badge-categoria, título clicável e data (dir).
 * Possui fallback dinâmico robusto para o loop do WordPress.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$title         = isset( $args['title'] ) ? esc_html( $args['title'] ) : '';
$url           = isset( $args['url'] ) ? esc_url( $args['url'] ) : '';
$image_url     = isset( $args['image_url'] ) ? esc_url( $args['image_url'] ) : '';
$category_args = isset( $args['category'] ) ? $args['category'] : array();
$date_args     = isset( $args['date'] ) ? $args['date'] : array();
$class         = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// 2. Fallbacks dinâmicos completos para Loop do WP
if ( empty( $title ) && function_exists( 'get_the_title' ) ) {
	$title = get_the_title();
}
if ( empty( $url ) && function_exists( 'get_permalink' ) ) {
	$url = get_permalink();
}
if ( empty( $image_url ) && function_exists( 'get_the_post_thumbnail_url' ) ) {
	$image_url = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
}

// Se não houver título, impede renderização
if ( empty( $title ) ) {
	return;
}
?>
<div class="card-noticia-relacionada <?php echo $class; ?>">
	<!-- 1. Coluna da Esquerda: Mini Capa Clicável -->
	<a href="<?php echo $url; ?>" class="card-noticia-relacionada__image-link">
		<?php 
		mm_render_component( 'atoms', 'imagem-capa', array(
			'src'   => $image_url,
			'alt'   => sprintf( 'Miniatura de %s', $title ),
			'class' => 'card-noticia-relacionada__capa'
		) ); 
		?>
	</a>

	<!-- 2. Coluna da Direita: Conteúdos de Texto -->
	<div class="card-noticia-relacionada__content">
		<div class="card-noticia-relacionada__header">
			<?php mm_render_component( 'atoms', 'badge-categoria', $category_args ); ?>
		</div>
		
		<h4 class="card-noticia-relacionada__title">
			<a class="card-noticia-relacionada__title-link" href="<?php echo $url; ?>"><?php echo $title; ?></a>
		</h4>
		
		<div class="card-noticia-relacionada__footer">
			<?php mm_render_component( 'atoms', 'meta-data', $date_args ); ?>
		</div>
	</div>
</div>
