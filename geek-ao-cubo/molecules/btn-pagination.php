<?php
/**
 * Molecule: Botões de Paginação JS (btn-pagination)
 *
 * Renderiza o par de botões "Ver mais" e "Ver menos" com os SVGs padrões.
 * Utilizado pelos scripts locais de paginação progressiva.
 *
 * @package geek-ao-cubo
 *
 * @param string $prefix      Prefixo para as classes JS (ex: 'char' gerará 'js-char-more', 'btn-char-action').
 * @param int    $total_items O total de itens disponíveis para data-total.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$prefix      = isset( $args['prefix'] ) ? sanitize_key( $args['prefix'] ) : 'item';
$total_items = isset( $args['total_items'] ) ? absint( $args['total_items'] ) : 0;
$label_more  = isset( $args['label_more'] ) ? esc_html( $args['label_more'] ) : __( 'Ver mais', 'geek-ao-cubo' );
$label_less  = isset( $args['label_less'] ) ? esc_html( $args['label_less'] ) : __( 'Ver menos', 'geek-ao-cubo' );
?>

<button 
	type="button" 
	class="btn-<?php echo $prefix; ?>-action btn-<?php echo $prefix; ?>-action--more js-<?php echo $prefix; ?>-more"
	data-total="<?php echo $total_items; ?>"
	data-current="0"
>
	<span><?php echo $label_more; ?></span>
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		<polyline points="6 9 12 15 18 9"></polyline>
	</svg>
</button>

<button 
	type="button" 
	class="btn-<?php echo $prefix; ?>-action btn-<?php echo $prefix; ?>-action--less js-<?php echo $prefix; ?>-less"
	style="display: none;"
>
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		<polyline points="18 15 12 9 6 15"></polyline>
	</svg>
	<span><?php echo $label_less; ?></span>
</button>
