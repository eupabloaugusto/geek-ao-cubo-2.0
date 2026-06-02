<?php
/**
 * Molecule: Card de Anime para Personagem (card-anime-personagem)
 *
 * Card vertical (poster 2:3) exibindo um anime em que o personagem atua,
 * contendo overlay escuro na base, nome do anime e o papel (Principal/Secundário).
 *
 * @package geek-ao-cubo
 *
 * @param string $title     Nome do anime.
 * @param string $image_url URL do poster do anime.
 * @param string $permalink URL de destino.
 * @param string $role      Papel do personagem na obra (Main/Supporting).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title     = isset( $args['title'] )     ? esc_html( $args['title'] )     : '';
$image_url = isset( $args['image_url'] ) ? esc_url( $args['image_url'] )  : '';
$permalink = isset( $args['permalink'] ) ? esc_url( $args['permalink'] )  : '';
$role      = isset( $args['role'] )      ? esc_html( $args['role'] )      : '';

if ( empty( $title ) ) {
	return;
}

// Fallback tag se não tiver permalink
$tag = ! empty( $permalink ) ? 'a' : 'article';
$attrs = ! empty( $permalink ) ? sprintf( 'href="%s" aria-label="%s"', $permalink, esc_attr( $title ) ) : '';
?>

<<?php echo $tag; ?> <?php echo $attrs; ?> class="card-anime-personagem">
	<div class="card-anime-personagem__poster">
		<?php if ( ! empty( $image_url ) ) : ?>
			<img src="<?php echo $image_url; ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" class="card-anime-personagem__img">
		<?php else: ?>
			<div style="width:100%; height:100%; background:var(--neutral-700); display:flex; align-items:center; justify-content:center; color:var(--neutral-400);">
				S/ Imagem
			</div>
		<?php endif; ?>
		<div class="card-anime-personagem__overlay"></div>
	</div>
	<div class="card-anime-personagem__info">
		<?php if ( ! empty( $role ) ) : ?>
			<span class="card-anime-personagem__role"><?php echo $role; ?></span>
		<?php endif; ?>
		<h3 class="card-anime-personagem__title"><?php echo $title; ?></h3>
	</div>
</<?php echo $tag; ?>>
