<?php
/**
 * Atom: Botão Gatilho de Busca (btn-busca-trigger)
 *
 * Botão semântico que se assemelha visualmente a um input de busca clássico,
 * servindo como gatilho interativo para abrir o modal de pesquisa.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id          = isset( $args['id'] ) ? esc_attr( $args['id'] ) : 'header-search-trigger';
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$placeholder = isset( $args['placeholder'] ) ? esc_html( $args['placeholder'] ) : __( 'Pesquisar no portal...', 'geek-ao-cubo' );
?>

<button id="<?php echo $id; ?>" 
		class="btn-busca-trigger js-open-search-modal <?php echo $class; ?>" 
		type="button" 
		aria-label="<?php esc_attr_e( 'Abrir barra de pesquisa', 'geek-ao-cubo' ); ?>"
		aria-haspopup="dialog"
		aria-expanded="false">
	<span class="btn-busca-trigger__icon" aria-hidden="true">
		<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
			<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
		</svg>
	</span>
	<span class="btn-busca-trigger__text"><?php echo $placeholder; ?></span>
</button>
