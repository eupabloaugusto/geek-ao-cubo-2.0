<?php
/**
 * Atom: Meta Autor (meta-autor)
 *
 * Exibe o avatar circular do autor do post e o seu nome linkado para seu perfil.
 * Possui suporte a fallbacks automáticos nativos do loop do WordPress.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$author_name = isset( $args['author_name'] ) ? esc_html( $args['author_name'] ) : '';
$author_link = isset( $args['author_link'] ) ? esc_url( $args['author_link'] ) : '';
$avatar_url  = isset( $args['avatar_url'] ) ? esc_url( $args['avatar_url'] ) : '';
$label       = isset( $args['label'] ) ? esc_html( $args['label'] ) : 'Por';
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// 2. Fallbacks elegantes baseados no loop do WordPress
if ( empty( $author_name ) && function_exists( 'get_the_author' ) ) {
	$author_name = get_the_author();
}
if ( empty( $author_link ) && function_exists( 'get_author_posts_url' ) && function_exists( 'get_the_author_meta' ) ) {
	$author_link = get_author_posts_url( get_the_author_meta( 'ID' ) );
}
if ( empty( $avatar_url ) && function_exists( 'get_avatar_url' ) && function_exists( 'get_the_author_meta' ) ) {
	$avatar_url = get_avatar_url( get_the_author_meta( 'ID' ), array( 'size' => 64 ) );
}

// Se não houver nome de autor a exibir, impede a renderização
if ( empty( $author_name ) ) {
	return;
}

// Extrai a inicial em caixa alta para caso de fallback sem imagem
$initial_letter = strtoupper( substr( html_entity_decode( $author_name, ENT_QUOTES, 'UTF-8' ), 0, 1 ) );
?>
<div class="meta-autor <?php echo $class; ?>">
	<!-- 1. Contêiner do Avatar (Imagem ou Capitular Fallback) -->
	<div class="meta-autor__avatar-frame">
		<?php if ( ! empty( $avatar_url ) ) : ?>
			<img 
				class="meta-autor__avatar" 
				src="<?php echo $avatar_url; ?>" 
				alt="" 
				loading="lazy" 
				width="32" 
				height="32"
			/>
		<?php else : ?>
			<div class="meta-autor__avatar-fallback" aria-hidden="true">
				<?php echo esc_html( $initial_letter ); ?>
			</div>
		<?php endif; ?>
	</div>
	
	<!-- 2. Textos e Links do Autor -->
	<div class="meta-autor__info">
		<?php if ( ! empty( $label ) ) : ?>
			<span class="meta-autor__label"><?php echo $label; ?></span>
		<?php endif; ?>
		
		<?php if ( ! empty( $author_link ) ) : ?>
			<a class="meta-autor__name-link" href="<?php echo $author_link; ?>"><?php echo $author_name; ?></a>
		<?php else : ?>
			<span class="meta-autor__name"><?php echo $author_name; ?></span>
		<?php endif; ?>
	</div>
</div>
