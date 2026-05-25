<?php
/**
 * Organism: Painel de Filtros e Busca de Animes (barra-filtros)
 *
 * Compõe múltiplas moléculas de form-field para busca, filtros por gênero, status e ordenação.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$action = esc_url( home_url( '/' ) );

// Opções para Filtro de Gêneros
$genero_options = array(
	'acao'    => __( 'Ação', 'hello-elementor-child' ),
	'romance' => __( 'Romance', 'hello-elementor-child' ),
	'shonen'  => __( 'Shonen', 'hello-elementor-child' ),
	'isekai'  => __( 'Isekai', 'hello-elementor-child' ),
	'slice'   => __( 'Slice of Life', 'hello-elementor-child' ),
);

// Opções para Filtro de Status
$status_options = array(
	'airing'    => __( 'Em exibição', 'hello-elementor-child' ),
	'completed' => __( 'Finalizado', 'hello-elementor-child' ),
	'upcoming'  => __( 'Em breve', 'hello-elementor-child' ),
);

// Opções para Ordenação
$ordem_options = array(
	'nota'       => __( 'Melhor Nota (MAL)', 'hello-elementor-child' ),
	'populares'  => __( 'Mais Populares', 'hello-elementor-child' ),
	'recentes'   => __( 'Lançamentos Recentes', 'hello-elementor-child' ),
);
?>

<div class="barra-filtros <?php echo $class; ?>">
	<form method="get" action="<?php echo $action; ?>" class="barra-filtros__form" role="search">
		
		<!-- 1. Campo de Busca Textual -->
		<div class="barra-filtros__item barra-filtros__item--search">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 's',
				'placeholder' => __( 'Buscar anime...', 'hello-elementor-child' ),
				'type'        => 'search',
				'value'       => get_search_query(),
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 2. Dropdown: Gêneros -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'genero',
				'placeholder' => __( 'Gêneros...', 'hello-elementor-child' ),
				'type'        => 'select',
				'options'     => $genero_options,
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 3. Dropdown: Status -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'status_anime',
				'placeholder' => __( 'Status...', 'hello-elementor-child' ),
				'type'        => 'select',
				'options'     => $status_options,
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 4. Dropdown: Ordenação -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'ordem',
				'placeholder' => __( 'Ordenar por...', 'hello-elementor-child' ),
				'type'        => 'select',
				'options'     => $ordem_options,
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 5. Botão de Disparo / Aplicar Filtros -->
		<div class="barra-filtros__item barra-filtros__item--submit">
			<button type="submit" class="btn btn--primary barra-filtros__btn">
				<?php _e( 'Filtrar', 'hello-elementor-child' ); ?>
			</button>
		</div>

	</form>
</div>
