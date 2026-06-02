<?php
/**
 * Molecule: Card de Atributo (card-atributo)
 *
 * Exibe uma informação em formato de card escuro com ícone laranja e label superior.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label    = isset( $args['label'] ) ? $args['label'] : '';
$is_badge = isset( $args['is_badge'] ) ? (bool) $args['is_badge'] : false;
$icon     = isset( $args['icon'] ) ? $args['icon'] : 'person'; // 'person' ou 'lightning'
$value    = isset( $args['value'] ) ? $args['value'] : '';

if ( empty( $value ) ) {
	return;
}
?>
<div class="card-atributo">
	<?php if ( ! empty( $label ) ) : ?>
		<span class="card-atributo__label <?php echo $is_badge ? 'card-atributo__label--badge' : ''; ?>">
			<?php echo esc_html( $label ); ?>
		</span>
	<?php endif; ?>
	
	<div class="card-atributo__box">
		<div class="card-atributo__icon-wrapper">
			<?php if ( $icon === 'lightning' ) : ?>
				<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
					<path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" />
				</svg>
			<?php else : ?>
				<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
					<path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" />
				</svg>
			<?php endif; ?>
		</div>
		<span class="card-atributo__value"><?php echo esc_html( $value ); ?></span>
	</div>
</div>
