<?php
/**
 * Atom: Ícone de Link Externo (icone-externo-link)
 *
 * Ícone + label para links externos (ANN, Wiki, etc.).
 * Indica visualmente que o link abre em nova aba/janela.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label        = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$url          = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$icon_name    = isset( $args['icon_name'] ) ? esc_attr( $args['icon_name'] ) : 'external';
$target       = isset( $args['target'] ) ? esc_attr( $args['target'] ) : '_blank';
$rel          = isset( $args['rel'] ) ? esc_attr( $args['rel'] ) : 'noopener noreferrer';
$class        = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$aria_label   = isset( $args['aria_label'] ) ? esc_attr( $args['aria_label'] ) : sprintf( __( 'Abrir %s em nova aba', 'hello-elementor-child' ), $label );
?>
<a 
	href="<?php echo $url; ?>" 
	class="icone-externo-link<?php echo $class ? ' ' . $class : ''; ?>" 
	target="<?php echo $target; ?>" 
	rel="<?php echo $rel; ?>"
	aria-label="<?php echo $aria_label; ?>"
>
	<span class="icone-externo-link__icon" aria-hidden="true">
		<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
			<path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/>
		</svg>
	</span>
	<?php if ( $label ) : ?>
		<span class="icone-externo-link__label"><?php echo $label; ?></span>
	<?php endif; ?>
</a>
