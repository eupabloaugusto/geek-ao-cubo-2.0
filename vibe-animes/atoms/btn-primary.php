<?php
/**
 * Atom: Botão Primário
 *
 * Suporta duas formas de renderização:
 * - Como <a> (link): padrão, ideal para navegação.
 * - Como <button>: quando type='submit' ou type='button', ideal para formulários.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label    = isset( $args['label'] ) ? esc_html( $args['label'] ) : __( 'Ver mais', 'geek-ao-cubo' );
$type     = isset( $args['type'] ) ? sanitize_key( $args['type'] ) : 'link'; // 'link', 'submit', 'button'
$url      = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$target   = isset( $args['target'] ) ? esc_attr( $args['target'] ) : '_self';
$class    = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$id       = isset( $args['id'] ) ? esc_attr( $args['id'] ) : '';
$disabled = isset( $args['disabled'] ) ? (bool) $args['disabled'] : false;

$btn_id_attr  = ! empty( $id ) ? ' id="' . $id . '"' : '';
$disabled_cls = $disabled ? ' btn--disabled' : '';
$is_button    = in_array( $type, array( 'submit', 'button', 'reset' ), true );

// SEO: se for link de afiliado, deve ter rel="sponsored".
$rel_attrs = array();
if ( isset( $args['is_affiliate'] ) && $args['is_affiliate'] ) {
	$rel_attrs[] = 'sponsored';
}
if ( '_blank' === $target ) {
	$rel_attrs[] = 'noopener';
	$rel_attrs[] = 'noreferrer';
}
$rel_attr = ! empty( $rel_attrs ) ? ' rel="' . esc_attr( implode( ' ', $rel_attrs ) ) . '"' : '';
?>

<?php if ( $is_button ) : ?>
	<button type="<?php echo esc_attr( $type ); ?>"<?php echo $btn_id_attr; ?>
		class="btn btn--primary <?php echo $class . $disabled_cls; ?>"
		<?php echo $disabled ? 'disabled aria-disabled="true"' : ''; ?>>  
		<?php echo $label; ?>
	</button>
<?php else : ?>
	<a href="<?php echo $disabled ? '#' : $url; ?>"<?php echo $btn_id_attr; ?>
		class="btn btn--primary <?php echo $class . $disabled_cls; ?>"
		target="<?php echo $target; ?>"
		<?php echo $disabled ? 'aria-disabled="true" tabindex="-1"' : ''; ?>
		<?php echo $rel_attr; ?>>
		<?php echo $label; ?>
	</a>
<?php endif; ?>
