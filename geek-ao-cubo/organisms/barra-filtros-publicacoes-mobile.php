<?php
/**
 * Organism: Barra de Filtros Mobile de Publicações (barra-filtros-publicacoes-mobile)
 *
 * Exibe a barra de busca compacta e um botão que aciona o modal de filtros.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

$ordem_options = array(
	'recentes'  => __( 'Mais Recentes', 'geek-ao-cubo' ),
	'antigos'   => __( 'Mais Antigos', 'geek-ao-cubo' ),
	'populares' => __( 'Mais Populares', 'geek-ao-cubo' ),
);

$sel_busca     = isset( $_GET['busca'] ) ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : ( is_search() ? get_search_query() : '' );
$sel_categoria = isset( $_GET['category_name'] ) ? sanitize_text_field( wp_unslash( $_GET['category_name'] ) ) : '';
$sel_ordem     = isset( $_GET['ordem'] ) ? sanitize_text_field( wp_unslash( $_GET['ordem'] ) ) : '';

$ativos_html = '';

if ( ! empty( $sel_categoria ) ) {
	$cat_obj = get_category_by_slug( $sel_categoria );
	if ( $cat_obj ) {
		$ativos_html .= sprintf(
			'<span class="barra-filtros-mobile__ativo-chip">%s <button type="button" class="barra-filtros-mobile__ativo-remove" data-remove-select="category_name" aria-label="%s">&times;</button></span>',
			esc_html( $cat_obj->name ),
			esc_attr( sprintf( __( 'Remover filtro %s', 'geek-ao-cubo' ), $cat_obj->name ) )
		);
	}
}

if ( ! empty( $sel_ordem ) && 'recentes' !== $sel_ordem && isset( $ordem_options[ $sel_ordem ] ) ) {
	$ativos_html .= sprintf(
		'<span class="barra-filtros-mobile__ativo-chip barra-filtros-mobile__ativo-chip--ordem">%s <button type="button" class="barra-filtros-mobile__ativo-remove" data-remove-select="ordem" aria-label="%s">&times;</button></span>',
		esc_html( $ordem_options[ $sel_ordem ] ),
		esc_attr( sprintf( __( 'Remover filtro %s', 'geek-ao-cubo' ), $ordem_options[ $sel_ordem ] ) )
	);
}
?>

<div class="barra-filtros-mobile <?php echo $class; ?>">
	
	<form method="get" action="<?php echo $action; ?>" class="barra-filtros-mobile__form" id="form-filtros-pub-mobile" role="search">
		<!-- Topo da barra mobile: Busca rápida e Botão de Filtro -->
		<div class="barra-filtros-mobile__bar">
			<label class="barra-filtros-mobile__search-wrap" for="filtros-pub-mobile-busca">
				<svg class="barra-filtros-mobile__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
				<input 
					type="search" 
					id="filtros-pub-mobile-busca"
					name="busca" 
					class="barra-filtros-mobile__search-input" 
					placeholder="<?php esc_attr_e( 'Buscar...', 'geek-ao-cubo' ); ?>" 
					value="<?php echo esc_attr( $sel_busca ); ?>"
					autocomplete="off"
				>
			</label>
			
			<!-- Botão Toggle Universal do Catálogo -->
			<?php 
			// Calcula a contagem de filtros ativos
			$count_ativos = ( ! empty( $sel_categoria ) ? 1 : 0 ) + ( ! empty( $sel_ordem ) && 'recentes' !== $sel_ordem ? 1 : 0 );
			
			mm_render_component( 'atoms', 'btn-filtros-toggle', array(
				'target' => 'mobile-filters-panel',
				'count'  => $count_ativos,
				'class'  => 'barra-filtros-mobile__trigger' // Mantém a classe para compatibilidade com o JS local
			) ); 
			?>
		</div>

		<!-- Chips de Filtros Ativos -->
		<?php if ( ! empty( $ativos_html ) ) : ?>
			<div class="barra-filtros-mobile__ativos" id="mobile-filtros-ativos" aria-live="polite">
				<?php echo $ativos_html; ?>
			</div>
		<?php endif; ?>

		<!-- Modal/Panel de Filtros Expandido (Bottom Sheet) -->
		<div id="mobile-filters-panel" class="barra-filtros-mobile__sheet" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Filtros', 'geek-ao-cubo' ); ?>" aria-hidden="true">
			
			<!-- Handle de arrastar -->
			<div class="barra-filtros-mobile__handle" aria-hidden="true"></div>

			<div class="barra-filtros-mobile__sheet-header">
				<h3 class="barra-filtros-mobile__sheet-title"><?php esc_html_e( 'Filtros', 'geek-ao-cubo' ); ?></h3>
				<button type="button" class="barra-filtros-mobile__close barra-filtros-mobile__panel-close" aria-label="<?php esc_attr_e( 'Fechar filtros', 'geek-ao-cubo' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
					</svg>
				</button>
			</div>

			<div class="barra-filtros-mobile__sheet-body">
			<fieldset class="barra-filtros-mobile__fieldset">
				<legend class="barra-filtros-mobile__legend"><?php esc_html_e( 'Categorias', 'geek-ao-cubo' ); ?></legend>
				<?php mm_render_component( 'molecules', 'form-field', array(
					'name'        => 'category_name',
					'type'        => 'select',
					'options'     => $cat_options,
					'value'       => isset( $_GET['category_name'] ) ? sanitize_text_field( $_GET['category_name'] ) : '',
					'class'       => 'form-field--full',
				) ); ?>
			</fieldset>

			<fieldset class="barra-filtros-mobile__fieldset">
				<legend class="barra-filtros-mobile__legend"><?php esc_html_e( 'Ordenar por', 'geek-ao-cubo' ); ?></legend>
				<?php mm_render_component( 'molecules', 'form-field', array(
					'name'        => 'ordem',
					'type'        => 'select',
					'options'     => $ordem_options,
					'value'       => isset( $_GET['ordem'] ) ? sanitize_text_field( $_GET['ordem'] ) : 'recentes',
					'class'       => 'form-field--full',
				) ); ?>
			</fieldset>

			</div><!-- /.barra-filtros-mobile__sheet-body -->

			<div class="barra-filtros-mobile__sheet-actions">
				<button type="reset" class="btn btn--outline btn--full barra-filtros-mobile__btn-limpar barra-filtros-mobile__reset">
					<?php esc_html_e( 'Limpar', 'geek-ao-cubo' ); ?>
				</button>
				<button type="submit" class="btn btn--primary btn--full barra-filtros-mobile__btn-aplicar">
					<?php esc_html_e( 'Aplicar', 'geek-ao-cubo' ); ?>
				</button>
			</div>
		</div><!-- /.barra-filtros-mobile__sheet -->
	</form><!-- /.barra-filtros-mobile__form -->

	<!-- Overlay (fora do form para não enviar) -->
	<div class="barra-filtros-mobile__overlay" id="mobile-filters-panel-overlay" aria-hidden="true"></div>

</div>
