<?php
/**
 * Molecule: Card de Personagem (card-personagem)
 *
 * Card premium em formato de pôster para exibição de personagens de anime.
 * Estrutura: imagem de fundo full-bleed com overlay glassmorphic na base,
 * badge de papel (Principal / Secundário) e suporte a link individual.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Higienização e Resolução de Parâmetros
$name        = isset( $args['name'] ) ? esc_html( $args['name'] ) : '';
$name_kanji  = isset( $args['name_kanji'] ) ? esc_html( $args['name_kanji'] ) : '';
$image_url   = isset( $args['image_url'] ) ? esc_url( $args['image_url'] ) : '';
$role        = isset( $args['role'] ) ? esc_html( $args['role'] ) : '';
$url         = isset( $args['url'] ) ? esc_url( $args['url'] ) : '';
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$aria_label  = isset( $args['aria_label'] )
	? esc_attr( $args['aria_label'] )
	: esc_attr( sprintf( __( 'Personagem: %s', 'hello-elementor-child' ), $name ) );

// 2. Mapeamento de variante do badge por papel
$role_slug = '';
if ( ! empty( $role ) ) {
	$role_lower = mb_strtolower( $role );
	if ( str_contains( $role_lower, 'principal' ) || str_contains( $role_lower, 'main' ) ) {
		$role_slug = 'main';
	} elseif ( str_contains( $role_lower, 'secundário' ) || str_contains( $role_lower, 'supporting' ) ) {
		$role_slug = 'supporting';
	} else {
		$role_slug = 'other';
	}
}

// 3. Guarda se tem link para decidir o wrapper
$has_link = ! empty( $url );

// 4. Impede renderização sem nome
if ( empty( $name ) ) {
	return;
}

$wrapper_tag   = $has_link ? 'a' : 'div';
$wrapper_attrs = $has_link
	? sprintf( 'href="%s" ', $url )
	: '';
?>
<<?php echo $wrapper_tag; ?> <?php echo $wrapper_attrs; ?>class="card-personagem<?php echo $class ? ' ' . $class : ''; ?><?php echo $role_slug ? ' card-personagem--' . $role_slug : ''; ?>" aria-label="<?php echo $aria_label; ?>">

	<div class="card-personagem__poster">
		<?php if ( ! empty( $image_url ) ) : ?>
			<img
				class="card-personagem__image"
				src="<?php echo $image_url; ?>"
				alt="<?php echo esc_attr( sprintf( __( 'Imagem do personagem %s', 'hello-elementor-child' ), $name ) ); ?>"
				loading="lazy"
				decoding="async"
			/>
		<?php else : ?>
			<!-- Fallback sem imagem: gradiente com inicial -->
			<div class="card-personagem__fallback" aria-hidden="true">
				<span class="card-personagem__initial"><?php echo mb_substr( $name, 0, 1 ); ?></span>
			</div>
		<?php endif; ?>

		<!-- Gradient overlay para estética e hover -->
		<div class="card-personagem__overlay" aria-hidden="true"></div>

		<?php if ( ! empty( $role ) ) : ?>
			<span class="card-personagem__badge card-personagem__badge--<?php echo $role_slug; ?>">
				<?php echo $role; ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="card-personagem__info">
		<p class="card-personagem__name"><?php echo $name; ?></p>
		<?php if ( ! empty( $name_kanji ) ) : ?>
			<p class="card-personagem__name-kanji" lang="ja"><?php echo $name_kanji; ?></p>
		<?php endif; ?>
	</div>

</<?php echo $wrapper_tag; ?>>
