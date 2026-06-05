<?php
/**
 * Organism: Painel de Filtros e Busca de Publicações (barra-filtros-publicacoes)
 *
 * Compõe múltiplas moléculas de form-field para busca, filtros por categorias e ordenação.
 *
 * @package vibe-animes
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
$cat_options = array( '' => __( 'Todas as Categorias', 'vibe-animes' ) );
foreach( $categories as $cat ) {
	$cat_options[ $cat->slug ] = $cat->name;
}

// Opções para Ordenação
$ordem_options = array(
	'recentes'  => __( 'Mais Recentes', 'vibe-animes' ),
	'antigos'   => __( 'Mais Antigos', 'vibe-animes' ),
	'populares' => __( 'Mais Populares', 'vibe-animes' ),
);
?>

<div class="barra-filtros <?php echo $class; ?>">
	<form method="get" action="<?php echo $action; ?>" class="barra-filtros__form" role="search">
		<?php
		$lang = vibe_multilingual_get_current_language();
		if ( $lang && $lang !== 'pt-BR' ) {
			echo '<input type="hidden" name="app_lang" value="' . esc_attr( $lang ) . '" />';
		}
		?>
		
		<!-- 1. Campo de Busca Textual -->
		<div class="barra-filtros__item barra-filtros__item--search">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'busca',
				'placeholder' => __( 'Buscar publicação...', 'vibe-animes' ),
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
				'name'        => 'categoria_slug',
				'placeholder' => __( 'Categorias...', 'vibe-animes' ),
				'type'        => 'select',
				'options'     => $cat_options,
				'value'       => isset( $_GET['categoria_slug'] ) ? sanitize_text_field( $_GET['categoria_slug'] ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 3. Dropdown: Ordenação -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'ordem',
				'placeholder' => __( 'Ordenar por...', 'vibe-animes' ),
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
				<?php esc_html_e( 'Filtrar', 'vibe-animes' ); ?>
			</button>
		</div>

	</form>
</div>

