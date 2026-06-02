<?php
/**
 * Atom: Biblioteca de Inputs (input-busca)
 *
 * Suporta input de busca com ícone de lupa, inputs de texto convencionais e dropdowns (select).
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Parâmetros e higienização
$type        = isset( $args['type'] ) ? sanitize_key( $args['type'] ) : 'search'; // 'search', 'text', 'select', 'email', 'tel'
$placeholder = isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';
$name        = isset( $args['name'] ) ? esc_attr( $args['name'] ) : '';
$id          = isset( $args['id'] ) ? esc_attr( $args['id'] ) : sanitize_title( $name );
$value       = isset( $args['value'] ) ? esc_attr( $args['value'] ) : '';
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$required    = isset( $args['required'] ) && $args['required'] ? ' required' : '';
$disabled    = isset( $args['disabled'] ) && $args['disabled'] ? true : false;
$options     = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();
$is_error    = isset( $args['is_error'] ) ? (bool) $args['is_error'] : false;
$helper_id   = isset( $args['helper_id'] ) ? esc_attr( $args['helper_id'] ) : '';

$wrapper_class = 'input-wrapper';
if ( 'search' === $type ) {
	$wrapper_class .= ' input-wrapper--search';
}
if ( 'select' === $type ) {
	$wrapper_class .= ' input-wrapper--select';
}
if ( $disabled ) {
	$wrapper_class .= ' input-wrapper--disabled';
}
$disabled_attr = $disabled ? ' disabled aria-disabled="true"' : '';
$aria_invalid  = $is_error ? ' aria-invalid="true"' : '';
$aria_desc     = ! empty( $helper_id ) ? ' aria-describedby="' . $helper_id . '"' : '';
?>

<div class="<?php echo $wrapper_class; ?> <?php echo $class; ?>">
	
	<?php if ( 'select' === $type ) : ?>
		
		<!-- Dropdown (Select) -->
		<select name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="input-field input-field--select"<?php echo $required; ?><?php echo $disabled_attr; ?><?php echo $aria_invalid; ?><?php echo $aria_desc; ?>>
			<?php if ( ! empty( $placeholder ) ) : ?>
				<option value="" disabled selected hidden><?php echo esc_html( $placeholder ); ?></option>
			<?php endif; ?>
			<?php foreach ( $options as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $value, $val ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		
		<!-- Ícone de seta para o select -->
		<span class="input-select-arrow" aria-hidden="true">
			<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
			</svg>
		</span>

	<?php else : ?>
		
		<!-- Input de Texto / Busca / Email / Etc. -->
		<input type="<?php echo esc_attr( $type ); ?>" 
			   name="<?php echo $name; ?>" 
			   id="<?php echo $id; ?>" 
			   class="input-field" 
			   placeholder="<?php echo $placeholder; ?>" 
			   value="<?php echo $value; ?>"
			   <?php echo $required; ?><?php echo $disabled_attr; ?><?php echo $aria_invalid; ?><?php echo $aria_desc; ?> />

		<?php if ( 'search' === $type ) : ?>
			<!-- Ícone de Lupa para Busca -->
			<span class="input-search-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
				</svg>
			</span>
		<?php endif; ?>

	<?php endif; ?>
</div>
