<?php
/**
 * Atom: Item de Breadcrumb (breadcrumb-item)
 *
 * Exibe um link individual ou texto estático com um separador integrado para trilhas de navegação.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Validação dos Argumentos
$label          = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$url            = isset( $args['url'] ) ? esc_url( $args['url'] ) : '';
$is_current     = isset( $args['is_current'] ) ? (bool) $args['is_current'] : false;
$show_separator = isset( $args['show_separator'] ) ? (bool) $args['show_separator'] : true;
$class          = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$position       = isset( $args['position'] ) ? (int) $args['position'] : 1;

// Autodetecta a URL da página atual se estiver vazia (para conformidade Schema/Microdata)
if ( empty( $url ) ) {
	if ( is_singular() || is_front_page() || is_home() ) {
		$url = get_permalink();
	} else {
		global $wp;
		$url = isset( $wp->request ) ? home_url( add_query_arg( array(), $wp->request ) ) : home_url( '/' );
	}
}

// Impede a renderização se o rótulo estiver vazio
if ( empty( $label ) ) {
	return;
}
?>
<li class="breadcrumb-item <?php echo $is_current ? 'breadcrumb-item--current' : ''; ?> <?php echo $class; ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
	<?php if ( ! empty( $url ) && ! $is_current ) : ?>
		<a href="<?php echo $url; ?>" class="breadcrumb-item__link" itemprop="item">
			<span itemprop="name"><?php echo $label; ?></span>
		</a>
	<?php else : ?>
		<span class="breadcrumb-item__current" aria-current="page">
			<span itemprop="name"><?php echo $label; ?></span>
		</span>
		<?php if ( ! empty( $url ) ) : ?>
			<link itemprop="item" href="<?php echo $url; ?>" />
		<?php endif; ?>
	<?php endif; ?>
	<meta itemprop="position" content="<?php echo $position; ?>" />

	<?php if ( $show_separator ) : ?>
		<span class="breadcrumb-item__separator" aria-hidden="true">
			<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
			</svg>
		</span>
	<?php endif; ?>
</li>
