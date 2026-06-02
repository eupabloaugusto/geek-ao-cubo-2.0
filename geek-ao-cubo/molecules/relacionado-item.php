<?php
/**
 * Molecule: Item de Anime Relacionado (relacionado-item)
 *
 * Card horizontal compacto para exibir um anime relacionado.
 * Layout: [thumbnail 4rem 2:3] [tipo de relação + título]
 * O card inteiro é clicável quando anime_url é fornecido.
 *
 * @package geek-ao-cubo
 *
 * @param string $anime_title   Título do anime relacionado (obrigatório).
 * @param string $anime_image   URL da capa (opcional — fallback se ausente).
 * @param string $anime_url     URL da página do anime (opcional — torna o card clicável).
 * @param string $relation_type Tipo de relação ("Sequência", "Prequel", "Adaptação", etc.). Default: ''.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$anime_title   = isset( $args['anime_title'] )   ? esc_html( $args['anime_title'] )   : '';
$anime_image   = isset( $args['anime_image'] )   ? esc_url( $args['anime_image'] )    : '';
$anime_url     = isset( $args['anime_url'] )     ? esc_url( $args['anime_url'] )      : '';
$relation_type = isset( $args['relation_type'] ) ? esc_html( $args['relation_type'] ) : '';

if ( empty( $anime_title ) ) {
	return;
}

$tag   = ! empty( $anime_url ) ? 'a' : 'div';
$is_external = strpos( $anime_url, 'myanimelist.net' ) !== false;
$external_attr = $is_external ? 'target="_blank" rel="noopener noreferrer" ' : '';

$attrs = ! empty( $anime_url )
	? sprintf(
		'href="%s" aria-label="%s" %s',
		$anime_url,
		esc_attr( sprintf( __( 'Ver anime relacionado: %s', 'geek-ao-cubo' ), $anime_title ) ),
		$external_attr
	)
	: '';
?>

<<?php echo $tag; ?> <?php echo $attrs; ?>class="relacionado-item">

	<div class="relacionado-item__thumb">
		<?php
		mm_render_component( 'atoms', 'imagem-capa', array(
			'src' => $anime_image,
			'alt' => sprintf( __( 'Capa de %s', 'geek-ao-cubo' ), $anime_title ),
		) );
		?>
	</div>

	<div class="relacionado-item__info">
		<?php if ( ! empty( $relation_type ) ) : ?>
			<span class="relacionado-item__relation"><?php echo $relation_type; ?></span>
		<?php endif; ?>
		<span class="relacionado-item__title"><?php echo $anime_title; ?></span>
	</div>

</<?php echo $tag; ?>>
