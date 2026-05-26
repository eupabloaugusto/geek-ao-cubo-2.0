<?php
/**
 * Molecule: Card de Membro da Equipe (card-staff)
 *
 * Card horizontal compacto para exibir um membro da equipe de produção.
 * Layout: [avatar] [nome + cargo]
 * O card inteiro é clicável se uma URL for fornecida.
 *
 * @package hello-elementor-child
 *
 * @param string $staff_name  Nome do membro da equipe (obrigatório).
 * @param string $staff_image URL da foto do membro (opcional — exibe fallback se ausente).
 * @param string $staff_role  Cargo na produção (ex.: "Diretor", "Compositor"). Default: ''.
 * @param string $staff_url   URL do perfil do membro (opcional — torna o card clicável).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$staff_name  = isset( $args['staff_name'] )  ? esc_html( $args['staff_name'] )  : '';
$staff_image = isset( $args['staff_image'] ) ? esc_url( $args['staff_image'] )  : '';
$staff_role  = isset( $args['staff_role'] )  ? esc_html( $args['staff_role'] )  : '';
$staff_url   = isset( $args['staff_url'] )   ? esc_url( $args['staff_url'] )    : '';

if ( empty( $staff_name ) ) {
	return;
}

$tag   = ! empty( $staff_url ) ? 'a' : 'div';
$attrs = ! empty( $staff_url )
	? sprintf(
		'href="%s" aria-label="%s" ',
		$staff_url,
		esc_attr( sprintf( __( 'Ver perfil de %s', 'hello-elementor-child' ), $staff_name ) )
	)
	: '';
?>

<<?php echo $tag; ?> <?php echo $attrs; ?>class="card-staff">

	<?php
	mm_render_component( 'atoms', 'avatar-personagem', array(
		'image_url'      => $staff_image,
		'character_name' => $staff_name,
		'size'           => 0,
		'class'          => 'card-staff__avatar',
	) );
	?>

	<div class="card-staff__info">
		<span class="card-staff__name"><?php echo $staff_name; ?></span>
		<?php if ( ! empty( $staff_role ) ) : ?>
			<span class="card-staff__role"><?php echo $staff_role; ?></span>
		<?php endif; ?>
	</div>

</<?php echo $tag; ?>>
