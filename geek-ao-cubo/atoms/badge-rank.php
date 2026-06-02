<?php
/**
 * Atom: Badge de Ranking (badge-rank)
 *
 * Badge especial de ranking (#1, Top 10) com cor dourada para destaque.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rank         = isset( $args['rank'] ) ? esc_html( $args['rank'] ) : '#1';
$variant      = isset( $args['variant'] ) ? esc_attr( $args['variant'] ) : 'default';
$class        = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$aria_label   = isset( $args['aria_label'] ) ? esc_attr( $args['aria_label'] ) : sprintf( __( 'Ranking %s', 'geek-ao-cubo' ), $rank );

// Determinar classe de variante
$variant_class = '';
if ( $variant === 'top10' ) {
	$variant_class = 'badge-rank--top10';
}
?>
<span class="badge-rank <?php echo $variant_class; ?> <?php echo $class; ?>" aria-label="<?php echo $aria_label; ?>">
	<?php echo $rank; ?>
</span>
