<?php
/**
 * Molecule: Card Assistir Agora (sidebar-assistir-agora)
 *
 * Bloco promocional lateral (CTA) para direcionar usuários a assistir o anime em canais oficiais.
 * Combina imagem-capa (fundo), textos de cabeçalho, sinopse da plataforma e btn-primary (ação).
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$title         = isset( $args['title'] ) ? esc_html( $args['title'] ) : __( 'ASSISTA AGORA', 'hello-elementor-child' );
$platform_name = isset( $args['platform_name'] ) ? esc_html( $args['platform_name'] ) : 'Crunchyroll';
$description   = isset( $args['description'] ) ? esc_html( $args['description'] ) : __( 'Temporadas completas com dublagem e legendas em português.', 'hello-elementor-child' );
$image_url     = isset( $args['image_url'] ) ? esc_url( $args['image_url'] ) : '';
$stream_url    = isset( $args['stream_url'] ) ? esc_url( $args['stream_url'] ) : '#';
$class         = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

?>
<div class="sidebar-assistir-agora <?php echo $class; ?>">
	<!-- 1. Imagem de Fundo (Capa Hero) -->
	<?php if ( ! empty( $image_url ) ) : ?>
		<div class="sidebar-assistir-agora__bg-frame">
			<?php 
			mm_render_component( 'atoms', 'imagem-capa', array(
				'src'    => $image_url,
				'alt'       => sprintf( 'Fundo promocional de %s', $platform_name ),
				'class'     => 'sidebar-assistir-agora__bg'
			) ); 
			?>
		</div>
	<?php endif; ?>

	<!-- 2. Overlay de Contraste Escuro -->
	<div class="sidebar-assistir-agora__overlay" aria-hidden="true"></div>

	<!-- 3. Conteúdo em Destaque -->
	<div class="sidebar-assistir-agora__content">
		<div class="sidebar-assistir-agora__header">
			<span class="sidebar-assistir-agora__tag"><?php echo $title; ?></span>
			<h3 class="sidebar-assistir-agora__platform"><?php echo $platform_name; ?></h3>
		</div>

		<p class="sidebar-assistir-agora__desc"><?php echo $description; ?></p>

		<!-- 4. Botão CTA Primário -->
		<div class="sidebar-assistir-agora__action">
			<?php 
			mm_render_component( 'atoms', 'btn-primary', array(
				'label' => sprintf( __( 'Assistir na %s', 'hello-elementor-child' ), $platform_name ),
				'url'   => $stream_url,
				'class' => 'sidebar-assistir-agora__btn'
			) ); 
			?>
		</div>
	</div>
</div>
