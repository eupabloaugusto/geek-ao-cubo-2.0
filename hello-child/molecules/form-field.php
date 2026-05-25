<?php
/**
 * Molecule: Grupo de Input / Campo de Formulário (form-field)
 *
 * Compõe os átomos: input-label + input-busca + input-helper.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Parâmetros e higienização
$name         = isset( $args['name'] ) ? esc_attr( $args['name'] ) : '';
$id           = isset( $args['id'] ) ? esc_attr( $args['id'] ) : sanitize_title( $name );
$label        = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$type         = isset( $args['type'] ) ? sanitize_key( $args['type'] ) : 'text';
$placeholder  = isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';
$value        = isset( $args['value'] ) ? esc_attr( $args['value'] ) : '';
$required     = isset( $args['required'] ) ? (bool) $args['required'] : false;
$disabled     = isset( $args['disabled'] ) ? (bool) $args['disabled'] : false;
$options      = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();
$helper_text  = isset( $args['helper_text'] ) ? esc_html( $args['helper_text'] ) : '';
$is_error     = isset( $args['is_error'] ) ? (bool) $args['is_error'] : false;
$class        = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Caso ocorra erro no input, adiciona uma classe modificadora no campo de busca
$input_class = $is_error ? 'input-field--has-error' : '';
$helper_id   = ! empty( $helper_text ) ? $id . '-helper' : '';
?>

<div class="form-field <?php echo $is_error ? 'form-field--error' : ''; ?> <?php echo $disabled ? 'form-field--disabled' : ''; ?> <?php echo $class; ?>">
	
	<?php 
	// 1. Átomo de Rótulo (Label)
	if ( ! empty( $label ) ) {
		mm_render_component( 'atoms', 'input-label', array(
			'label' => $label,
			'for'   => $id,
		) );
	}
	?>

	<div class="form-field__control">
		<?php 
		// 2. Átomo do Campo de Input Principal
		mm_render_component( 'atoms', 'input-busca', array(
			'type'        => $type,
			'placeholder' => $placeholder,
			'name'        => $name,
			'id'          => $id,
			'value'       => $value,
			'required'    => $required,
			'disabled'    => $disabled,
			'options'     => $options,
			'class'       => $input_class,
			'is_error'    => $is_error,
			'helper_id'   => $helper_id,
		) );
		?>
	</div>

	<?php 
	// 3. Átomo de Texto Auxiliar ou Erro (Helper)
	if ( ! empty( $helper_text ) ) {
		mm_render_component( 'atoms', 'input-helper', array(
			'text'     => $helper_text,
			'is_error' => $is_error,
			'id'       => $helper_id,
		) );
	}
	?>

</div>
