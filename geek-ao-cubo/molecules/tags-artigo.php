<?php
/**
 * Molecule: Linha de Tags do Artigo (tags-artigo)
 *
 * Agrupa de forma fluida as tags clicáveis de taxonomia (tag-artigo) no rodapé do post.
 * Possui suporte a fallbacks automáticos nativos do loop do WordPress.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$tags  = isset( $args['tags'] ) ? $args['tags'] : array();
$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// 2. Fallback elegante para obter tags reais do post no WP
if ( empty( $tags ) && function_exists( 'get_the_tags' ) ) {
	$wp_tags = get_the_tags();
	if ( ! empty( $wp_tags ) && ! is_wp_error( $wp_tags ) ) {
		foreach ( $wp_tags as $wp_tag ) {
			$tags[] = array(
				'name' => $wp_tag->name,
				'url'  => get_tag_link( $wp_tag->term_id )
			);
		}
	}
}

// Se não houver tags a exibir, impede a renderização
if ( empty( $tags ) ) {
	return;
}
?>
<div class="tags-artigo <?php echo $class; ?>">
	<!-- Rótulo Descritivo Semântico -->
	<span class="tags-artigo__label"><?php _e( 'Tags:', 'geek-ao-cubo' ); ?></span>
	
	<!-- Lista de Tags Clicáveis -->
	<div class="tags-artigo__list">
		<?php foreach ( $tags as $tag ) : ?>
			<?php 
			mm_render_component( 'atoms', 'tag-artigo', array(
				'name' => isset( $tag['name'] ) ? $tag['name'] : '',
				'url'  => isset( $tag['url'] ) ? $tag['url'] : '#'
			) ); 
			?>
		<?php endforeach; ?>
	</div>
</div>
