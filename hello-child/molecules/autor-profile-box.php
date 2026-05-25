<?php
/**
 * Molecule: Caixa de Perfil de Autor (autor-profile-box)
 *
 * Exibe biografia detalhada do autor com avatar maior (80px),
 * nome com link e descrição/biografia.
 * Possui suporte a fallbacks automáticos nativos do loop do WordPress.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$author_name = isset( $args['author_name'] ) ? esc_html( $args['author_name'] ) : '';
$author_link = isset( $args['author_link'] ) ? esc_url( $args['author_link'] ) : '';
$avatar_url  = isset( $args['avatar_url'] ) ? esc_url( $args['avatar_url'] ) : '';
$bio         = isset( $args['bio'] ) ? esc_html( $args['bio'] ) : '';
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// 2. Fallbacks dinâmicos completos para o Loop do WP
if ( empty( $author_name ) && function_exists( 'get_the_author' ) ) {
	$author_name = get_the_author();
}
if ( empty( $author_link ) && function_exists( 'get_author_posts_url' ) && function_exists( 'get_the_author_meta' ) ) {
	$author_link = get_author_posts_url( get_the_author_meta( 'ID' ) );
}
if ( empty( $avatar_url ) && function_exists( 'get_avatar_url' ) && function_exists( 'get_the_author_meta' ) ) {
	$avatar_url = get_avatar_url( get_the_author_meta( 'ID' ), array( 'size' => 160 ) ); // Dobro de 80 para Retina
}
if ( empty( $bio ) && function_exists( 'get_the_author_meta' ) ) {
	$bio = get_the_author_meta( 'description' );
}

// Se não houver nome de autor a exibir, impede a renderização
if ( empty( $author_name ) ) {
	return;
}

// Extrai a inicial em caixa alta para caso de fallback sem imagem
$initial_letter = strtoupper( substr( html_entity_decode( $author_name, ENT_QUOTES, 'UTF-8' ), 0, 1 ) );
?>
<div class="autor-profile-box <?php echo $class; ?>">
	<!-- 1. Avatar Grande (80px) -->
	<div class="autor-profile-box__avatar-frame">
		<?php if ( ! empty( $avatar_url ) ) : ?>
			<img 
				class="autor-profile-box__avatar" 
				src="<?php echo $avatar_url; ?>" 
				alt="<?php echo sprintf( 'Avatar de %s', $author_name ); ?>" 
				loading="lazy" 
				width="80" 
				height="80"
			/>
		<?php else : ?>
			<div class="autor-profile-box__avatar-fallback" aria-hidden="true">
				<?php echo esc_html( $initial_letter ); ?>
			</div>
		<?php endif; ?>
	</div>
	
	<!-- 2. Conteúdo de Biografia -->
	<div class="autor-profile-box__content">
		<span class="autor-profile-box__role-label"><?php _e( 'Autor do Artigo', 'hello-elementor-child' ); ?></span>
		
		<h4 class="autor-profile-box__name">
			<?php if ( ! empty( $author_link ) ) : ?>
				<a class="autor-profile-box__name-link" href="<?php echo $author_link; ?>"><?php echo $author_name; ?></a>
			<?php else : ?>
				<?php echo $author_name; ?>
			<?php endif; ?>
		</h4>
		
		<?php if ( ! empty( $bio ) ) : ?>
			<p class="autor-profile-box__bio"><?php echo $bio; ?></p>
		<?php else : ?>
			<p class="autor-profile-box__bio autor-profile-box__bio--empty">
				<?php echo sprintf( '%s é colunista e apaixonado pelo universo de animes no Geek ao Cubo.', $author_name ); ?>
			</p>
		<?php endif; ?>
	</div>
</div>
