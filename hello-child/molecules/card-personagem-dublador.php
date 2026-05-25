<?php
/**
 * Molecule: Card de Personagem e Dublador (card-personagem-dublador)
 *
 * Card horizontal compacto no estilo MAL clássico.
 * Lado esquerdo: avatar + nome + role do personagem.
 * Lado direito (espelhado): idioma + nome + avatar do voice actor.
 * Cada lado é individualmente clicável se uma URL for fornecida.
 *
 * @package hello-elementor-child
 *
 * @param string $character_name  Nome do personagem (obrigatório).
 * @param string $character_image URL da imagem do personagem.
 * @param string $character_role  Role do personagem. Default: 'Principal'.
 * @param string $character_url   URL da página do personagem (opcional).
 * @param string $va_name         Nome do voice actor.
 * @param string $va_image        URL da imagem do voice actor.
 * @param string $va_language     Idioma do VA. Default: 'Japonês'.
 * @param string $va_url          URL da página do VA (opcional).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$character_name  = isset( $args['character_name'] ) ? esc_html( $args['character_name'] ) : '';
$character_image = isset( $args['character_image'] ) ? esc_url( $args['character_image'] ) : '';
$character_role  = isset( $args['character_role'] ) ? esc_html( $args['character_role'] ) : 'Principal';
$character_url   = isset( $args['character_url'] ) ? esc_url( $args['character_url'] ) : '';
$va_name         = isset( $args['va_name'] ) ? esc_html( $args['va_name'] ) : '';
$va_image        = isset( $args['va_image'] ) ? esc_url( $args['va_image'] ) : '';
$va_language     = isset( $args['va_language'] ) ? esc_html( $args['va_language'] ) : 'Japonês';
$va_url          = isset( $args['va_url'] ) ? esc_url( $args['va_url'] ) : '';

if ( empty( $character_name ) ) {
	return;
}

$role_lower = mb_strtolower( $character_role );
if ( str_contains( $role_lower, 'principal' ) || str_contains( $role_lower, 'main' ) ) {
	$role_slug = 'main';
} elseif ( str_contains( $role_lower, 'secund' ) || str_contains( $role_lower, 'supporting' ) ) {
	$role_slug = 'supporting';
} else {
	$role_slug = 'other';
}

$char_tag   = ! empty( $character_url ) ? 'a' : 'div';
$char_attrs = ! empty( $character_url )
	? sprintf( 'href="%s" aria-label="%s" ', $character_url, esc_attr( sprintf( __( 'Ver personagem: %s', 'hello-elementor-child' ), $character_name ) ) )
	: '';

$va_tag   = ! empty( $va_url ) ? 'a' : 'div';
$va_attrs = ! empty( $va_url )
	? sprintf( 'href="%s" aria-label="%s" ', $va_url, esc_attr( sprintf( __( 'Ver dublador: %s', 'hello-elementor-child' ), $va_name ) ) )
	: '';
?>

<div class="card-personagem-dublador">

	<!-- Lado Esquerdo: Personagem -->
	<<?php echo $char_tag; ?> <?php echo $char_attrs; ?>class="card-personagem-dublador__side card-personagem-dublador__side--character">

		<?php
		mm_render_component( 'atoms', 'avatar-personagem', array(
			'image_url'      => $character_image,
			'character_name' => $character_name,
			'size'           => 54,
			'class'          => 'card-personagem-dublador__avatar',
		) );
		?>

		<div class="card-personagem-dublador__info">
			<span class="card-personagem-dublador__name"><?php echo $character_name; ?></span>
			<span class="card-personagem-dublador__role card-personagem-dublador__role--<?php echo $role_slug; ?>"><?php echo $character_role; ?></span>
		</div>

	</<?php echo $char_tag; ?>>

	<?php if ( ! empty( $va_name ) ) : ?>

		<!-- Separador Vertical -->
		<div class="card-personagem-dublador__divider" aria-hidden="true"></div>

		<!-- Lado Direito: Voice Actor (espelhado) -->
		<<?php echo $va_tag; ?> <?php echo $va_attrs; ?>class="card-personagem-dublador__side card-personagem-dublador__side--va">

			<div class="card-personagem-dublador__info card-personagem-dublador__info--va">
				<span class="card-personagem-dublador__name"><?php echo $va_name; ?></span>
				<span class="card-personagem-dublador__language"><?php echo $va_language; ?></span>
			</div>

			<?php
			mm_render_component( 'atoms', 'avatar-personagem', array(
				'image_url'      => $va_image,
				'character_name' => $va_name,
				'size'           => 54,
				'class'          => 'card-personagem-dublador__avatar',
			) );
			?>

		</<?php echo $va_tag; ?>>

	<?php endif; ?>

</div>
