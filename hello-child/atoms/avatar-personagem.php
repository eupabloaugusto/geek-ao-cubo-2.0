<?php
/**
 * Atom: Avatar de Personagem (avatar-personagem)
 *
 * Exibe a imagem de avatar de um personagem de anime de forma circular com fallback elegante de silhueta.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$image_url      = isset( $args['image_url'] ) ? esc_url( $args['image_url'] ) : '';
$character_name = isset( $args['character_name'] ) ? esc_html( $args['character_name'] ) : 'Personagem';
$size           = isset( $args['size'] ) ? intval( $args['size'] ) : 80;
$class          = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Garante limites razoáveis de tamanho
if ( $size <= 0 ) {
	$size = 80;
}

// Construção de estilos inline para o tamanho customizado
$size_style = sprintf( 'width: %1$dpx; height: %1$dpx; min-width: %1$dpx; min-height: %1$dpx;', $size );
?>
<div class="avatar-personagem <?php echo $class; ?>" style="<?php echo $size_style; ?>" title="<?php echo $character_name; ?>">
	<?php if ( ! empty( $image_url ) ) : ?>
		<img 
			class="avatar-personagem__image" 
			src="<?php echo $image_url; ?>" 
			alt="<?php echo sprintf( 'Avatar de %s', $character_name ); ?>" 
			loading="lazy" 
			width="<?php echo $size; ?>" 
			height="<?php echo $size; ?>"
		/>
	<?php else : ?>
		<div class="avatar-personagem__fallback" aria-label="<?php echo sprintf( 'Avatar de %s (Sem Foto)', $character_name ); ?>" role="img">
			<svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
				<!-- Círculo de fundo escuro discreto -->
				<circle cx="50" cy="50" r="50" fill="var(--neutral-800)"/>
				<!-- Silhueta estilizada de ombros e cabeça com toque de anime (cabelo levemente espetado) -->
				<path d="M50 20C42.3 20 36 26.3 36 34C36 37.6 37.4 40.9 39.7 43.3C30.4 46.2 23.5 54.8 23.0 65.3C22.9 66.8 24.1 68 25.6 68H74.4C75.9 68 77.1 66.8 77.0 65.3C76.5 54.8 69.6 46.2 60.3 43.3C62.6 40.9 64 37.6 64 34C64 26.3 57.7 20 50 20ZM50 26C54.4 26 58 29.6 58 34C58 38.4 54.4 42 50 42C45.6 42 42 38.4 42 34C42 29.6 45.6 26 50 26ZM30.4 62C32.7 54.7 39.4 49.3 47.5 48.1C44.7 49.8 42.8 52.7 42.8 56C42.8 56.6 42.9 57.1 43.0 57.7C40.6 58.7 38.7 60.2 37.4 62H30.4ZM52.5 48.1C60.6 49.3 67.3 54.7 69.6 62H62.6C61.3 60.2 59.4 58.7 57.0 57.7C57.1 57.1 57.2 56.6 57.2 56C57.2 52.7 55.3 49.8 52.5 48.1Z" fill="var(--neutral-600)"/>
				<!-- Ponto de brilho/destaque da marca nos olhos ou topo para um ar premium -->
				<path d="M50 31C49.4 31 49 31.4 49 32V34C49 34.6 49.4 35 50 35C50.6 35 51 34.6 51 34V32C51 32.4 50.6 31 50 31Z" fill="var(--color-primary)"/>
			</svg>
		</div>
	<?php endif; ?>
</div>
