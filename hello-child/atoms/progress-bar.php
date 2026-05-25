<?php
/**
 * Atom: Barra de Progresso (progress-bar)
 *
 * Barra de progresso genérica para exibir percentuais (usuários, estatísticas, etc.).
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$percentage   = isset( $args['percentage'] ) ? (int) $args['percentage'] : 0;
$label         = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$show_label    = isset( $args['show_label'] ) ? (bool) $args['show_label'] : true;
$show_percent = isset( $args['show_percent'] ) ? (bool) $args['show_percent'] : true;
$class         = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$aria_label   = isset( $args['aria_label'] ) ? esc_attr( $args['aria_label'] ) : sprintf( __( 'Progresso: %d%%', 'hello-elementor-child' ), $percentage );

// Validar percentual (0-100)
$percentage = max( 0, min( 100, $percentage ) );
?>
<div class="progress-bar <?php echo $class; ?>" role="progressbar" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo $aria_label; ?>">
	<?php if ( $show_label && $label ) : ?>
		<span class="progress-bar__label"><?php echo $label; ?></span>
	<?php endif; ?>
	
	<div class="progress-bar__track">
		<div class="progress-bar__fill" style="width: <?php echo $percentage; ?>%;"></div>
	</div>
	
	<?php if ( $show_percent ) : ?>
		<span class="progress-bar__percent"><?php echo $percentage; ?>%</span>
	<?php endif; ?>
</div>
