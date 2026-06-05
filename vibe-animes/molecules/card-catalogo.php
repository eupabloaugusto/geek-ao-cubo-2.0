<?php
/**
 * Molecule: Card do Catálogo (card-catalogo)
 *
 * Card horizontal para exibição de anime no catálogo com listagem alfabética.
 * Layout: thumbnail widescreen à esquerda | título + sinopse + idioma à direita.
 * Sinopse é ocultada no mobile via CSS para priorizar densidade de conteúdo.
 *
 * Thumbnail: tenta ACF (anime_capa_url), fallback para Featured Image do WP.
 *
 * @package geek-ao-cubo
 *
 * @param string $titulo      Título do anime. Obrigatório.
 * @param string $url         URL do single do anime. Default '#'.
 * @param string $imagem_url  URL da imagem. Se vazio, usa get_the_post_thumbnail_url().
 * @param int    $post_id     ID do post (para fallback da featured image). Default 0.
 * @param string $sinopse     Sinopse/excerpt truncada do anime. Opcional.
 * @param string $idioma      Label de idioma: 'Legendado', 'Dublado' ou 'Leg | Dub'. Opcional.
 * @param array  $generos     Array com nomes dos gêneros do anime. Opcional.
 * @param string $class       Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo     = isset( $args['titulo'] )     ? esc_html( $args['titulo'] )      : '';
$url        = isset( $args['url'] )        ? esc_url( $args['url'] )          : '#';
$imagem_url = isset( $args['imagem_url'] ) ? esc_url( $args['imagem_url'] )   : '';
$post_id    = isset( $args['post_id'] )    ? (int) $args['post_id']           : 0;
$sinopse    = isset( $args['sinopse'] )    ? $args['sinopse']                 : '';
$idioma      = isset( $args['idioma'] )      ? esc_html( $args['idioma'] )       : '';
$idioma_slug = isset( $args['idioma_slug'] ) ? sanitize_html_class( $args['idioma_slug'] ) : '';
$banner_url = isset( $args['banner_url'] ) ? esc_url( $args['banner_url'] )    : '';
$generos    = isset( $args['generos'] )    ? (array) $args['generos']         : array();
$class      = isset( $args['class'] )      ? esc_attr( $args['class'] )       : '';

if ( empty( $titulo ) ) {
	return;
}

// Fallback para Featured Image do WordPress se ACF estiver vazio
if ( empty( $imagem_url ) && $post_id > 0 ) {
	$imagem_url = esc_url( get_the_post_thumbnail_url( $post_id, 'medium' ) );
}

// Trunca sinopse para ~150 caracteres se vier muito longa
if ( ! empty( $sinopse ) && mb_strlen( strip_tags( $sinopse ) ) > 180 ) {
	$sinopse = mb_substr( strip_tags( $sinopse ), 0, 180 ) . '…';
}
?>
<article class="card-catalogo <?php echo $class; ?>">
	<!-- Thumbnail -->
	<a
		href="<?php echo $url; ?>"
		class="card-catalogo__media"
		aria-label="<?php echo esc_attr( sprintf( __( 'Ver detalhes: %s', 'geek-ao-cubo' ), $titulo ) ); ?>"
		tabindex="-1"
	>
		<?php if ( ! empty( $imagem_url ) ) : ?>
			<img
				src="<?php echo $imagem_url; ?>"
				alt="<?php echo esc_attr( sprintf( __( 'Capa do anime %s', 'geek-ao-cubo' ), $titulo ) ); ?>"
				class="card-catalogo__img"
				loading="lazy"
				decoding="async"
			>
		<?php else : ?>
			<div class="card-catalogo__img-placeholder" aria-hidden="true"></div>
		<?php endif; ?>
	</a>

	<!-- Conteúdo textual -->
	<div class="card-catalogo__content">
		<h3 class="card-catalogo__titulo">
			<a href="<?php echo $url; ?>" class="card-catalogo__titulo-link">
				<?php echo $titulo; ?>
			</a>
		</h3>

		<?php if ( ! empty( $sinopse ) ) : ?>
			<p class="card-catalogo__sinopse"><?php echo esc_html( $sinopse ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $idioma ) ) : ?>
			<span class="card-catalogo__idioma<?php echo $idioma_slug ? ' card-catalogo__idioma--' . $idioma_slug : ''; ?>"><?php echo $idioma; ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $generos ) ) : ?>
			<div class="card-catalogo__generos">
				<?php foreach ( $generos as $genero ) : ?>
					<span class="card-catalogo__genero"><?php echo esc_html( $genero ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</article>
