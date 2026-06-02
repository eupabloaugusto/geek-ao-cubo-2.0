<?php
/**
 * Atom: Botão Toggle de Filtros Mobile (btn-filtros-toggle)
 *
 * Botão que abre/fecha o bottom sheet de filtros na barra-filtros-mobile.
 * Exibe ícone de funil + label "Filtros" + badge com contagem de filtros ativos.
 *
 * @package geek-ao-cubo
 *
 * @param string $target ID do bottom sheet a controlar. Default: 'barra-filtros-sheet'.
 * @param int    $count  Número de filtros ativos. Exibe badge se > 0. Default: 0.
 * @param string $class  Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$target = isset( $args['target'] ) ? esc_attr( $args['target'] ) : 'barra-filtros-sheet';
$count  = isset( $args['count'] )  ? (int) $args['count']        : 0;
$class  = isset( $args['class'] )  ? esc_attr( $args['class'] )  : '';

$has_active = $count > 0;
$btn_class  = 'btn-filtros-toggle' . ( $has_active ? ' btn-filtros-toggle--ativo' : '' ) . ( $class ? ' ' . $class : '' );
?>
<button
	type="button"
	class="<?php echo $btn_class; ?>"
	aria-controls="<?php echo $target; ?>"
	aria-expanded="false"
	data-filtros-toggle="<?php echo $target; ?>"
	aria-label="<?php echo $has_active
		? esc_attr( sprintf( __( 'Abrir filtros — %d ativos', 'geek-ao-cubo' ), $count ) )
		: esc_attr( __( 'Abrir filtros', 'geek-ao-cubo' ) ); ?>"
>
	<!-- Ícone de funil / linhas de filtro -->
	<svg class="btn-filtros-toggle__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<line x1="3" y1="6"  x2="21" y2="6"/>
		<line x1="6" y1="12" x2="18" y2="12"/>
		<line x1="10" y1="18" x2="14" y2="18"/>
	</svg>

	<span class="btn-filtros-toggle__label"><?php _e( 'Filtros', 'geek-ao-cubo' ); ?></span>

	<?php if ( $has_active ) : ?>
		<span class="btn-filtros-toggle__badge" aria-hidden="true"><?php echo $count; ?></span>
	<?php endif; ?>
</button>
