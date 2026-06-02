<?php
/**
 * Organism: Painel de Filtros e Busca de Animes (barra-filtros)
 *
 * Compõe múltiplas moléculas de form-field para busca, filtros por gênero, status e ordenação.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$class  = isset( $args['class'] )      ? esc_attr( $args['class'] )  : '';
$action = isset( $args['action_url'] )
	? esc_url( $args['action_url'] )
	: esc_url( get_post_type_archive_link( 'anime' ) ?: home_url( '/' ) );

// Opções para Filtro de Gêneros — lidos dinamicamente da taxonomia para garantir consistência com o mobile e com os slugs reais do banco
$genero_terms   = get_terms( array( 'taxonomy' => 'genero', 'hide_empty' => true ) );
$genero_options = array();
if ( ! is_wp_error( $genero_terms ) && ! empty( $genero_terms ) ) {
	foreach ( $genero_terms as $t ) {
		$genero_options[ $t->slug ] = $t->name;
	}
}
if ( empty( $genero_options ) ) {
	// Fallback com slugs reais esperados pela taxonomia
	$genero_options = array(
		'acao'          => __( 'Ação', 'geek-ao-cubo' ),
		'romance'       => __( 'Romance', 'geek-ao-cubo' ),
		'shonen'        => __( 'Shonen', 'geek-ao-cubo' ),
		'isekai'        => __( 'Isekai', 'geek-ao-cubo' ),
		'slice-of-life' => __( 'Slice of Life', 'geek-ao-cubo' ),
	);
}

// Opções para Filtro de Status — lista curada fixa
// 'lancamento' é tratado como query computada em cpt-helpers.php (episódios recentes + publicação nos últimos 30 dias)
$status_options = array(
	'todos'      => __( 'Todos', 'geek-ao-cubo' ),
	'lancamento' => __( 'Em Lançamento', 'geek-ao-cubo' ),
	'finalizado' => __( 'Finalizado', 'geek-ao-cubo' ),
);

// Opções para Idioma
$idioma_options = array(
	'todos'     => __( 'Todos', 'geek-ao-cubo' ),
	'legendado' => __( 'Legendados', 'geek-ao-cubo' ),
	'dublado'   => __( 'Dublados', 'geek-ao-cubo' ),
);

// Opções para Tipo de Mídia
$tipo_midia_options = array(
	'todos'   => __( 'Todos', 'geek-ao-cubo' ),
	'serie'   => __( 'Séries (TV/Web)', 'geek-ao-cubo' ),
	'filme'   => __( 'Filmes', 'geek-ao-cubo' ),
	'OVA'     => __( 'OVA', 'geek-ao-cubo' ),
	'Special' => __( 'Especiais', 'geek-ao-cubo' ),
	'manga'   => __( 'Mangá', 'geek-ao-cubo' ),
);

// Opções para Ordenação
$ordem_options = array(
	'populares'  => __( 'Mais Populares', 'geek-ao-cubo' ),
	'recente'    => __( 'Mais Recente', 'geek-ao-cubo' ),
	'alfabetica' => __( 'Ordem Alfabética', 'geek-ao-cubo' ),
);
?>

<div class="barra-filtros <?php echo $class; ?>">
	<form method="get" action="<?php echo $action; ?>" class="barra-filtros__form" role="search">
		
		<!-- 1. Campo de Busca Textual -->
		<div class="barra-filtros__item barra-filtros__item--search">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'busca',
				'placeholder' => __( 'Buscar anime...', 'geek-ao-cubo' ),
				'type'        => 'search',
				'value'       => isset( $_GET['busca'] ) ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 2. Dropdown: Gêneros -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'genero',
				'placeholder' => __( 'Gêneros...', 'geek-ao-cubo' ),
				'type'        => 'select',
				'options'     => $genero_options,
				'value'       => isset( $_GET['genero'] ) ? sanitize_key( $_GET['genero'] ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 3. Dropdown: Status -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'status_anime',
				'placeholder' => __( 'Status...', 'geek-ao-cubo' ),
				'type'        => 'select',
				'options'     => $status_options,
				'value'       => isset( $_GET['status_anime'] ) ? sanitize_key( $_GET['status_anime'] ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 4. Dropdown: Idioma -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'idioma',
				'placeholder' => __( 'Idioma...', 'geek-ao-cubo' ),
				'type'        => 'select',
				'options'     => $idioma_options,
				'value'       => isset( $_GET['idioma'] ) ? sanitize_key( $_GET['idioma'] ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 5. Dropdown: Tipo de Mídia -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'tipo_midia',
				'placeholder' => __( 'Tipo de Mídia...', 'geek-ao-cubo' ),
				'type'        => 'select',
				'options'     => $tipo_midia_options,
				'value'       => isset( $_GET['tipo_midia'] ) ? sanitize_key( $_GET['tipo_midia'] ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 6. Dropdown: Ordenação -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'ordem',
				'placeholder' => __( 'Ordenar por...', 'geek-ao-cubo' ),
				'type'        => 'select',
				'options'     => $ordem_options,
				'value'       => isset( $_GET['ordem'] ) ? sanitize_key( $_GET['ordem'] ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 5. Botão de Disparo / Aplicar Filtros -->
		<div class="barra-filtros__item barra-filtros__item--submit">
			<button type="submit" class="btn btn--primary barra-filtros__btn">
				<?php _e( 'Filtrar', 'geek-ao-cubo' ); ?>
			</button>
		</div>

	</form>
</div>
