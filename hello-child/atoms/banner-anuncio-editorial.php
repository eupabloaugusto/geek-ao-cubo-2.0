<?php
/**
 * Atom: Banner de Anúncio Editorial (banner-anuncio-editorial)
 *
 * Exibe uma chamada promocional/editorial no meio do corpo dos artigos.
 * Apresenta design premium (com suporte a imagem de fundo ou mesh gradient) e alinhamento responsivo.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$title       = isset( $args['title'] ) ? esc_html( $args['title'] ) : '';
$description = isset( $args['description'] ) ? esc_html( $args['description'] ) : '';
$badge_text  = isset( $args['badge_text'] ) ? esc_html( $args['badge_text'] ) : 'EDITORIAL';
$cta_text    = isset( $args['cta_text'] ) ? esc_html( $args['cta_text'] ) : 'Clique Aqui';
$cta_link    = isset( $args['cta_link'] ) ? esc_url( $args['cta_link'] ) : '';
$image_url   = isset( $args['image_url'] ) ? esc_url( $args['image_url'] ) : '';
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Impedir renderização se não houver título ou link de ação
if ( empty( $title ) || empty( $cta_link ) ) {
	return;
}

// Construção de estilo inline condicional para imagem de fundo
$style_attribute = '';
if ( ! empty( $image_url ) ) {
	// Mescla a imagem com um gradiente linear para garantir legibilidade WCAG do texto superior
	$style_attribute = sprintf(
		'style="background-image: linear-gradient(135deg, rgba(13, 14, 17, 0.82) 0%%, rgba(13, 14, 17, 0.95) 100%%), url(\'%s\');"',
		$image_url
	);
}
?>
<div 
	class="banner-editorial <?php echo ! empty( $image_url ) ? 'banner-editorial--has-image' : ''; ?> <?php echo $class; ?>" 
	<?php echo $style_attribute; ?>
>
	<!-- Conteúdo de Texto à Esquerda (ou empilhado em Mobile) -->
	<div class="banner-editorial__content">
		<?php if ( ! empty( $badge_text ) ) : ?>
			<span class="banner-editorial__badge"><?php echo $badge_text; ?></span>
		<?php endif; ?>
		<h3 class="banner-editorial__title"><?php echo $title; ?></h3>
		<p class="banner-editorial__description"><?php echo $description; ?></p>
	</div>

	<!-- Ações à Direita (ou empilhado em Mobile) -->
	<div class="banner-editorial__actions">
		<a href="<?php echo $cta_link; ?>" class="banner-editorial__btn">
			<span class="banner-editorial__btn-text"><?php echo $cta_text; ?></span>
			<svg class="banner-editorial__btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<line x1="5" y1="12" x2="19" y2="12"></line>
				<polyline points="12 5 19 12 12 19"></polyline>
			</svg>
		</a>
	</div>
</div>
