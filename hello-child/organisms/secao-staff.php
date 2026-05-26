<?php
/**
 * Organism: Seção Equipe de Produção (secao-staff)
 *
 * Exibe membros da equipe de produção do anime agrupados dinamicamente
 * por cargo (role_group). Cada grupo tem subtítulo H3 com barra laranja
 * e exibe até $max_per_group cards de card-staff. Botão opcional no rodapé.
 *
 * @package hello-elementor-child
 *
 * @param string $titulo         Título da seção. Default: 'Equipe de Produção'.
 * @param array  $staff          Array plano de membros. Cada item aceita os parâmetros
 *                               de card-staff (staff_name, staff_image, staff_url, staff_role)
 *                               mais o campo role_group para agrupamento.
 * @param int    $max_per_group  Limite de cards exibidos por grupo. Default: 6.
 * @param string $ver_mais_url   URL do botão "Ver equipe completa" (opcional).
 * @param string $ver_mais_label Label do botão. Default: 'Ver equipe completa'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo         = isset( $args['titulo'] )         ? esc_html( $args['titulo'] )        : __( 'Equipe de Produção', 'hello-elementor-child' );
$staff          = isset( $args['staff'] )          ? (array) $args['staff']              : array();
$max_per_group  = isset( $args['max_per_group'] )  ? (int) $args['max_per_group']        : 6;
$ver_mais_url   = isset( $args['ver_mais_url'] )   ? esc_url( $args['ver_mais_url'] )    : '';
$ver_mais_label = isset( $args['ver_mais_label'] ) ? esc_html( $args['ver_mais_label'] ) : __( 'Ver equipe completa', 'hello-elementor-child' );

if ( empty( $staff ) ) {
	return;
}

// Agrupamento dinâmico por role_group — ordem de primeira aparição preservada
$grouped = array();
foreach ( $staff as $member ) {
	if ( empty( $member['staff_name'] ) ) {
		continue;
	}
	$group = ! empty( $member['role_group'] ) ? esc_html( $member['role_group'] ) : __( 'Outros', 'hello-elementor-child' );
	$grouped[ $group ][] = $member;
}

if ( empty( $grouped ) ) {
	return;
}
?>

<section
	class="secao-staff"
	aria-label="<?php echo esc_attr( $titulo ); ?>"
>
	<div class="secao-staff__inner">

		<!-- =====================================================
		     TÍTULO PRINCIPAL
		     ===================================================== -->
		<h2 class="secao-staff__title"><?php echo $titulo; ?></h2>

		<!-- =====================================================
		     GRUPOS POR CARGO
		     ===================================================== -->
		<div class="secao-staff__groups">
			<?php foreach ( $grouped as $group_name => $members ) : ?>
				<div class="secao-staff__group">

					<h3 class="secao-staff__group-title"><?php echo $group_name; ?></h3>

					<div class="secao-staff__grid">
						<?php
						$group_slice = array_slice( $members, 0, $max_per_group );
						foreach ( $group_slice as $member ) :
							// Remove role_group antes de passar ao card — não é parâmetro da molécula
							$card_args = $member;
							unset( $card_args['role_group'] );
							mm_render_component( 'molecules', 'card-staff', $card_args );
						endforeach;
						?>
					</div>

				</div>
			<?php endforeach; ?>
		</div>

		<!-- =====================================================
		     RODAPÉ: botão "Ver equipe completa"
		     ===================================================== -->
		<?php if ( ! empty( $ver_mais_url ) ) : ?>
			<footer class="secao-staff__footer">
				<a
					href="<?php echo $ver_mais_url; ?>"
					class="btn btn--secondary secao-staff__btn"
					rel="nofollow noopener"
					target="_blank"
					aria-label="<?php echo esc_attr( $ver_mais_label ); ?>"
				>
					<?php echo $ver_mais_label; ?>
					<svg class="secao-staff__btn-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M3 8H13M9 4L13 8L9 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			</footer>
		<?php endif; ?>

	</div>
</section>
