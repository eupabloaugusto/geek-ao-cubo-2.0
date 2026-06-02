<?php
/**
 * Molecule: Formulário de Busca (form-busca)
 *
 * Compõe os átomos: input-busca + btn-primary (usando tag button submit com estilos equivalentes).
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$placeholder = isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : __( 'Buscar animes...', 'geek-ao-cubo' );
$action      = esc_url( home_url( '/' ) );
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
?>

<form role="search" method="get" class="form-busca <?php echo $class; ?>" action="<?php echo $action; ?>">
	<div class="form-busca__wrapper">
		
		<?php 
		// 1. Átomo de Input de Busca
		mm_render_component( 'atoms', 'input-busca', array(
			'type'        => 'search',
			'placeholder' => $placeholder,
			'name'        => 's',
			'value'       => get_search_query(),
			'class'       => 'form-busca__input',
		) ); 
		?>
		
		<!-- 2. Botão de Envio Estilizado com as Classes do Botão Primário -->
		<button type="submit" class="btn btn--primary form-busca__btn" aria-label="<?php esc_attr_e( 'Pesquisar', 'geek-ao-cubo' ); ?>">
			<?php _e( 'Buscar', 'geek-ao-cubo' ); ?>
		</button>

	</div>
</form>
