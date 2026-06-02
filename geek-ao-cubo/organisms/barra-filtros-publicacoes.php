<?php
/**
 * Organism: Painel de Filtros e Busca de Publicações (barra-filtros-publicacoes)
 *
 * Compõe múltiplas moléculas de form-field para busca, filtros por categorias e ordenação.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$class  = isset( $args['class'] )      ? esc_attr( $args['class'] )  : '';
$action = isset( $args['action_url'] )
	? esc_url( $args['action_url'] )
	: esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/' ) );

// Opções para Filtro de Categorias (Dinâmico)
$categories = get_categories( array( 'hide_empty' => false ) );
$cat_options = array( '' => __( 'Todas as Categorias', 'geek-ao-cubo' ) );
foreach( $categories as $cat ) {
	$cat_options[ $cat->slug ] = $cat->name;
}

// Opções para Ordenação
$ordem_options = array(
	'recentes'  => __( 'Mais Recentes', 'geek-ao-cubo' ),
	'antigos'   => __( 'Mais Antigos', 'geek-ao-cubo' ),
	'populares' => __( 'Mais Populares', 'geek-ao-cubo' ),
);
?>

<div class="barra-filtros <?php echo $class; ?>">
	<form method="get" action="<?php echo $action; ?>" class="barra-filtros__form" role="search">
		
		<!-- 1. Campo de Busca Textual -->
		<div class="barra-filtros__item barra-filtros__item--search">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'busca',
				'placeholder' => __( 'Buscar publicação...', 'geek-ao-cubo' ),
				'type'        => 'search',
				'value'       => isset( $_GET['busca'] ) ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : ( is_search() ? get_search_query() : '' ),
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 2. Dropdown: Categorias -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'category_name',
				'placeholder' => __( 'Categorias...', 'geek-ao-cubo' ),
				'type'        => 'select',
				'options'     => $cat_options,
				'value'       => isset( $_GET['category_name'] ) ? sanitize_text_field( $_GET['category_name'] ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 3. Dropdown: Ordenação -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'ordem',
				'placeholder' => __( 'Ordenar por...', 'geek-ao-cubo' ),
				'type'        => 'select',
				'options'     => $ordem_options,
				'value'       => isset( $_GET['ordem'] ) ? sanitize_text_field( $_GET['ordem'] ) : 'recentes',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- Botão de Submit -->
		<div class="barra-filtros__item barra-filtros__item--submit">
			<button type="submit" class="btn btn--primary">
				<?php esc_html_e( 'Filtrar', 'geek-ao-cubo' ); ?>
			</button>
		</div>

	</form>
</div>
