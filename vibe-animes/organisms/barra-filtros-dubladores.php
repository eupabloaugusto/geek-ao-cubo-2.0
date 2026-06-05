<?php
/**
 * Organism: Painel de Filtros e Busca de Dubladores (barra-filtros-dubladores)
 *
 * Compõe múltiplas moléculas de form-field para busca e ordenação do catálogo de dubladores.
 * Clone estrutural de barra-filtros-personagens.php — apenas filtros específicos de dublador.
 *
 * @package vibe-animes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$class  = isset( $args['class'] )      ? esc_attr( $args['class'] )  : '';
$action = isset( $args['action_url'] )
	? esc_url( $args['action_url'] )
	: esc_url( get_permalink() ?: home_url( '/' ) );

// Opções para Ordenação
$ordem_options = array(
	'populares'  => __( 'Mais Populares', 'vibe-animes' ),
	'alfabetica' => __( 'Ordem Alfabética', 'vibe-animes' ),
);

// Opções para Idioma
$idioma_options = array(
	''         => __( 'Todos os Idiomas', 'vibe-animes' ),
	'PT-BR'    => __( 'PT-BR', 'vibe-animes' ),
	'Original' => __( 'Original (Japonês)', 'vibe-animes' ),
	'Inglês'   => __( 'Inglês', 'vibe-animes' ),
	'Espanhol' => __( 'Espanhol', 'vibe-animes' ),
	'Francês'  => __( 'Francês', 'vibe-animes' ),
	'Alemão'   => __( 'Alemão', 'vibe-animes' ),
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
				'placeholder' => __( 'Buscar dublador...', 'vibe-animes' ),
				'type'        => 'search',
				'value'       => isset( $_GET['busca'] ) ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : '',
				'class'       => 'form-field--inline',
			) );
			?>
		</div>

		<!-- 2. Dropdown: Idioma -->
		<div class="barra-filtros__item">
			<?php
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'idioma',
				'placeholder' => __( 'Idioma...', 'vibe-animes' ),
				'type'        => 'select',
				'options'     => $idioma_options,
				'value'       => isset( $_GET['idioma'] ) ? sanitize_text_field( wp_unslash( $_GET['idioma'] ) ) : '',
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
				'value'       => isset( $_GET['ordem'] ) ? sanitize_key( $_GET['ordem'] ) : '',
				'class'       => 'form-field--inline',
			) );
			?>
		</div>

		<!-- 3. Botão de Disparo / Aplicar Filtros -->
		<div class="barra-filtros__item barra-filtros__item--submit">
			<button type="submit" class="btn btn--primary barra-filtros__btn">
				<?php _e( 'Filtrar', 'vibe-animes' ); ?>
			</button>
		</div>

	</form>
</div>

