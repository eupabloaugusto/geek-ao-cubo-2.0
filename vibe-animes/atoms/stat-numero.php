<?php
/**
 * Atom: Estatística Numérica (stat-numero)
 *
 * Exibe métricas de destaque (ex: "1.2M membros", "Rank #3") com ícones e rótulos contextuais.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$number = isset( $args['number'] ) ? esc_html( $args['number'] ) : '';
$label  = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$icon   = isset( $args['icon'] ) ? $args['icon'] : ''; // SVG bruto semântico
$class  = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

if ( empty( $number ) || empty( $label ) ) {
	return;
}
?>
<div class="stat-numero <?php echo $class; ?>">
	<?php if ( ! empty( $icon ) ) : ?>
		<div class="stat-numero__icon" aria-hidden="true">
			<?php echo $icon; ?>
		</div>
	<?php endif; ?>
	
	<div class="stat-numero__content">
		<span class="stat-numero__label"><?php echo $label; ?></span>
		<div class="stat-numero__value"><?php echo $number; ?></div>
	</div>
</div>
