<?php
/**
 * Atom: Tag de Artigo (tag-artigo)
 *
 * Exibe uma tag plana e clicável no rodapé do artigo, otimizada para navegação de taxonomia.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Validação dos Argumentos
$tag   = isset( $args['tag'] ) ? esc_html( $args['tag'] ) : ( isset( $args['name'] ) ? esc_html( $args['name'] ) : '' );
$url   = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Impede a renderização se o nome da tag estiver vazio
if ( empty( $tag ) ) {
	return;
}
?>
<a href="<?php echo $url; ?>" class="tag-artigo <?php echo $class; ?>" title="<?php echo esc_attr( sprintf( __( 'Ver mais artigos com a tag %s', 'geek-ao-cubo' ), $tag ) ); ?>">
	<span class="tag-artigo__hash" aria-hidden="true">#</span><?php echo $tag; ?>
</a>
