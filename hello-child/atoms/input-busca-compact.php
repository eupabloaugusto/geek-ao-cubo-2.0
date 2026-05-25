<?php
/**
 * Atom: Input de Busca Compacto (input-busca-compact)
 *
 * Input de busca simplificado para header sem botão integrado.
 * Quando clicado (readonly), abre o modal de busca completo.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$placeholder = isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : __( 'Pesquisar...', 'hello-elementor-child' );
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$readonly    = isset( $args['readonly'] ) ? (bool) $args['readonly'] : true;
$name        = isset( $args['name'] ) ? esc_attr( $args['name'] ) : 's';
$id          = isset( $args['id'] ) ? esc_attr( $args['id'] ) : '';
$aria_label  = isset( $args['aria_label'] ) ? esc_attr( $args['aria_label'] ) : __( 'Abrir pesquisa', 'hello-elementor-child' );
?>

<div class="input-busca-compact-wrapper <?php echo $class ? ' ' . $class . '-wrapper' : ''; ?>">
	<input 
		type="search" 
		name="<?php echo $name; ?>" 
		<?php if ( $id ) : ?>id="<?php echo $id; ?>"<?php endif; ?>
		class="input-busca-compact js-open-search-modal<?php echo $class ? ' ' . $class : ''; ?>" 
		placeholder="<?php echo $placeholder; ?>" 
		value=""
		autocomplete="off"
		<?php if ( $readonly ) : ?>readonly<?php endif; ?>
		aria-label="<?php echo $aria_label; ?>" />
	<span class="input-busca-compact__icon" aria-hidden="true">
		<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
			<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
		</svg>
	</span>
</div>
