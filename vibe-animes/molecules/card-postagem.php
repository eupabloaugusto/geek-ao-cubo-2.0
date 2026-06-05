<?php
/**
 * Molecule: Card de Postagem (card-postagem)
 *
 * Card horizontal para exibição de posts e notícias.
 * Layout: thumbnail 1:1 à esquerda | título, tag de segmentação e data à direita.
 *
 * @package geek-ao-cubo
 *
 * @param string $titulo      Título do post. Obrigatório.
 * @param string $url         URL do single do post. Default '#'.
 * @param string $imagem_url  URL da imagem. Se vazio, usa get_the_post_thumbnail_url().
 * @param int    $post_id     ID do post (para fallback da featured image). Default 0.
 * @param string $tag         Tag de segmentação (ex: Notícia, Artigo). Opcional.
 * @param string $data        Data de publicação. Opcional.
 * @param string $class       Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo     = isset( $args['titulo'] )     ? esc_html( $args['titulo'] )      : '';
$url        = isset( $args['url'] )        ? esc_url( $args['url'] )          : '#';
$imagem_url = isset( $args['imagem_url'] ) ? esc_url( $args['imagem_url'] )   : '';
$post_id    = isset( $args['post_id'] )    ? (int) $args['post_id']           : 0;
$tag        = isset( $args['tag'] )        ? esc_html( $args['tag'] )         : '';
$data       = isset( $args['data'] )       ? esc_html( $args['data'] )        : '';
$descricao  = isset( $args['descricao'] )  ? $args['descricao']               : '';
$class      = isset( $args['class'] )      ? esc_attr( $args['class'] )       : '';

if ( empty( $titulo ) ) {
	return;
}

// Fallback para Featured Image do WordPress se estiver vazio
if ( empty( $imagem_url ) && $post_id > 0 ) {
	$imagem_url = esc_url( get_the_post_thumbnail_url( $post_id, 'medium_large' ) );
}
?>
<article class="card-postagem <?php echo $class; ?>">
	<!-- Thumbnail -->
	<a
		href="<?php echo $url; ?>"
		class="card-postagem__media"
		aria-label="<?php echo esc_attr( sprintf( __( 'Ler post: %s', 'geek-ao-cubo' ), $titulo ) ); ?>"
		tabindex="-1"
	>
		<?php if ( ! empty( $imagem_url ) ) : ?>
			<img
				src="<?php echo $imagem_url; ?>"
				alt="<?php echo esc_attr( sprintf( __( 'Capa da postagem %s', 'geek-ao-cubo' ), $titulo ) ); ?>"
				class="card-postagem__img"
				loading="lazy"
				decoding="async"
			>
		<?php else : ?>
			<div class="card-postagem__img-placeholder" aria-hidden="true"></div>
		<?php endif; ?>
	</a>

	<!-- Conteúdo textual -->
	<div class="card-postagem__content">
		
		<?php if ( ! empty( $tag ) ) : ?>
			<span class="card-postagem__tag"><?php echo $tag; ?></span>
		<?php endif; ?>

		<h3 class="card-postagem__titulo">
			<a href="<?php echo $url; ?>" class="card-postagem__titulo-link">
				<?php echo $titulo; ?>
			</a>
		</h3>

		<?php if ( ! empty( $descricao ) ) : ?>
			<p class="card-postagem__descricao"><?php echo esc_html( $descricao ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $data ) ) : ?>
			<span class="card-postagem__data">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
				<?php echo $data; ?>
			</span>
		<?php endif; ?>

	</div>
</article>
