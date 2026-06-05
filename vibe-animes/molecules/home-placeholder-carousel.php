<?php
/**
 * Molecule: Home Placeholder Carousel (home-placeholder-carousel)
 *
 * Exibe uma mensagem de fallback elegante caso não haja artigos no banco de dados.
 *
 * @package geek-ao-cubo
 * @since   4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="home-placeholder-carousel">
	<div class="home-placeholder-carousel__slide">
		<div class="home-placeholder-carousel__badge"><?php _e( 'EM DESTAQUE', 'geek-ao-cubo' ); ?></div>
		<h2 class="home-placeholder-carousel__title"><?php _e( 'Nenhum artigo encontrado no banco de dados local.', 'geek-ao-cubo' ); ?></h2>
		<p class="home-placeholder-carousel__desc"><?php _e( 'Por favor, execute o script de sincronização ou crie alguns posts de teste no seu painel administrativo local do WordPress.', 'geek-ao-cubo' ); ?></p>
		<span class="home-placeholder-carousel__meta"><?php _e( 'Sem posts para exibir', 'geek-ao-cubo' ); ?></span>
	</div>
</div>
