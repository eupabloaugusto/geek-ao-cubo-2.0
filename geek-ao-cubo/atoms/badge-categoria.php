<?php
/**
 * Atom: Badge de Categoria (badge-categoria)
 *
 * Exibe uma tag editorial colorida para categorização do post (Notícias, Análise, Guia, etc.).
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Validação dos Argumentos
$categoria = isset( $args['categoria'] ) ? esc_html( $args['categoria'] ) : '';
$url       = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$class     = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Impede a renderização se o nome da categoria estiver vazio
if ( empty( $categoria ) ) {
	return;
}

// Gerar uma classe específica baseada no slug da categoria para estilização dedicada
$category_slug = sanitize_title( $categoria );
$theme_class   = 'badge-categoria--' . $category_slug;
?>
<a href="<?php echo $url; ?>" class="badge-categoria <?php echo $theme_class; ?> <?php echo $class; ?>" title="<?php echo esc_attr( sprintf( __( 'Ver todos os artigos em %s', 'geek-ao-cubo' ), $categoria ) ); ?>">
	<?php echo $categoria; ?>
</a>
