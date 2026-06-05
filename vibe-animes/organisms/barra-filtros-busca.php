<?php
/**
 * Organism: Painel de Filtros Global para Busca (barra-filtros-busca)
 *
 * Exibido no topo da página de resultados (search.php), adaptando-se aos tipos de conteúdo
 * encontrados para aquela palavra-chave específica.
 *
 * @package vibe-animes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class  = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$action = esc_url( home_url( '/' ) );

// $args['tipos_encontrados'] virá do search.php: array( 'anime' => 'Animes', 'manga' => 'Mangás', 'post' => 'Artigos' )
$tipos_encontrados = isset( $args['tipos_encontrados'] ) && is_array( $args['tipos_encontrados'] ) ? $args['tipos_encontrados'] : array();

// Sempre mostrar a opção 'todos' se houver mais de um tipo, ou se estiver vazio
$tipo_options = array( '' => __( 'Todo o Site', 'vibe-animes' ) );
foreach ( $tipos_encontrados as $key => $label ) {
	$tipo_options[ $key ] = $label;
}

// Opções para Ordenação
$ordem_options = array(
	'recentes'   => __( 'Mais Recentes', 'vibe-animes' ),
	'antigos'    => __( 'Mais Antigos', 'vibe-animes' ),
	'populares'  => __( 'Mais Populares', 'vibe-animes' ),
	'alfabetica' => __( 'Ordem Alfabética (A-Z)', 'vibe-animes' ),
);
?>

<div class="barra-filtros <?php echo $class; ?>" style="margin-bottom: var(--space-400);">
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
				'name'        => 's', // Na busca global o parâmetro é 's'
				'placeholder' => __( 'O que você procura?', 'vibe-animes' ),
				'type'        => 'search',
				'value'       => get_search_query(),
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>

		<!-- 2. Dropdown: Tipo de Conteúdo (Adaptativo) -->
		<?php if ( count( $tipo_options ) > 1 ) : ?>
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'tipo_conteudo',
				'placeholder' => __( 'Tipo de Conteúdo', 'vibe-animes' ),
				'type'        => 'select',
				'options'     => $tipo_options,
				'value'       => isset( $_GET['tipo_conteudo'] ) ? sanitize_text_field( wp_unslash( $_GET['tipo_conteudo'] ) ) : '',
				'class'       => 'form-field--inline',
			) ); 
			?>
		</div>
		<?php endif; ?>

		<!-- 3. Dropdown: Ordenação -->
		<div class="barra-filtros__item">
			<?php 
			mm_render_component( 'molecules', 'form-field', array(
				'name'        => 'ordem',
				'placeholder' => __( 'Ordenar por...', 'vibe-animes' ),
				'type'        => 'select',
				'options'     => $ordem_options,
				'value'       => isset( $_GET['ordem'] ) ? sanitize_text_field( wp_unslash( $_GET['ordem'] ) ) : 'recentes',
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

