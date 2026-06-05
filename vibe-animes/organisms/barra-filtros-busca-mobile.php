<?php
/**
 * Organism: Barra de Filtros Mobile para Busca Global
 *
 * @package vibe-animes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$action = esc_url( home_url( '/' ) );

// $args['tipos_encontrados'] virá do search.php
$tipos_encontrados = isset( $args['tipos_encontrados'] ) && is_array( $args['tipos_encontrados'] ) ? $args['tipos_encontrados'] : array();

$tipo_conteudo_atual = isset( $_GET['tipo_conteudo'] ) ? sanitize_text_field( wp_unslash( $_GET['tipo_conteudo'] ) ) : '';
$ordem_atual         = isset( $_GET['ordem'] ) ? sanitize_text_field( wp_unslash( $_GET['ordem'] ) ) : 'recentes';

$filtros_ativos = array();
if ( $tipo_conteudo_atual && isset( $tipos_encontrados[ $tipo_conteudo_atual ] ) ) {
	$filtros_ativos[] = array(
		'label' => 'Tipo: ' . $tipos_encontrados[ $tipo_conteudo_atual ],
		'value' => 'tipo_conteudo',
	);
}
if ( $ordem_atual && 'recentes' !== $ordem_atual ) {
	$ordem_label = '';
	if ( 'antigos' === $ordem_atual ) $ordem_label = 'Mais Antigos';
	if ( 'populares' === $ordem_atual ) $ordem_label = 'Mais Populares';
	if ( 'alfabetica' === $ordem_atual ) $ordem_label = 'Alfabética (A-Z)';
	if ( $ordem_label ) {
		$filtros_ativos[] = array(
			'label' => 'Ordem: ' . $ordem_label,
			'value' => 'ordem',
		);
	}
}

$ordem_options = array(
	'recentes'   => __( 'Mais Recentes', 'vibe-animes' ),
	'antigos'    => __( 'Mais Antigos', 'vibe-animes' ),
	'populares'  => __( 'Mais Populares', 'vibe-animes' ),
	'alfabetica' => __( 'Ordem Alfabética (A-Z)', 'vibe-animes' ),
);
?>

<div class="barra-filtros-mobile">
	<form method="get" action="<?php echo $action; ?>" class="barra-filtros-mobile__form" id="form-filtros-busca-mobile" role="search">
		<?php
		$lang = vibe_multilingual_get_current_language();
		if ( $lang && $lang !== 'pt-BR' ) {
			echo '<input type="hidden" name="app_lang" value="' . esc_attr( $lang ) . '" />';
		}
		?>
		<!-- ── Barra Sticky ─────────────────────────────────────── -->
		<div class="barra-filtros-mobile__bar">
			<!-- Campo de Busca Rápida -->
			<label class="barra-filtros-mobile__search-wrap" for="filtros-busca-mobile-busca">
				<svg class="barra-filtros-mobile__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
				</svg>
				<input
					type="search"
					id="filtros-busca-mobile-busca"
					name="s"
					class="barra-filtros-mobile__search-input"
					placeholder="<?php esc_attr_e( 'O que você procura?', 'vibe-animes' ); ?>"
					value="<?php echo esc_attr( get_search_query() ); ?>"
					autocomplete="off"
				>
			</label>

			<!-- Botão Toggle do Bottom Sheet -->
			<?php mm_render_component( 'atoms', 'btn-filtros-toggle', array(
				'target' => 'filtros-busca-sheet',
				'count'  => count( $filtros_ativos ),
			) ); ?>
		</div>

		<!-- ── Chips de Filtros Ativos (resumo visual) ──────────── -->
		<?php if ( ! empty( $filtros_ativos ) ) : ?>
		<div class="barra-filtros-mobile__ativos">
			<?php foreach ( $filtros_ativos as $filtro ) : ?>
				<span class="barra-filtros-mobile__ativo-chip">
					<?php echo esc_html( $filtro['label'] ); ?>
					<button type="button" class="barra-filtros-mobile__ativo-remove" data-filtros-remove="<?php echo esc_attr( $filtro['value'] ); ?>" data-filtros-value="<?php echo 'tipo_conteudo' === $filtro['value'] ? esc_attr( $tipo_conteudo_atual ) : esc_attr( $ordem_atual ); ?>">×</button>
				</span>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<!-- Bottom Sheet (Modal de Filtros) -->
		<div id="filtros-busca-sheet" class="barra-filtros-mobile__sheet" role="dialog" aria-modal="true" aria-labelledby="filtros-busca-title" hidden>
			<div class="barra-filtros-mobile__content">
				<!-- Handle de arrastar -->
				<div class="barra-filtros-mobile__drag-handle"></div>

				<div class="barra-filtros-mobile__header">
					<h2 id="filtros-busca-title"><?php esc_html_e( 'Filtros de Busca', 'vibe-animes' ); ?></h2>
					<button type="button" class="barra-filtros-mobile__close" data-filtros-close="filtros-busca-sheet" aria-label="<?php esc_attr_e( 'Fechar filtros', 'vibe-animes' ); ?>">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>
				
				<div class="barra-filtros-mobile__body">
					<!-- Tipo de Conteúdo (Só exibe se tiver mais de 1 tipo disponível) -->
					<?php if ( count( $tipos_encontrados ) > 1 ) : ?>
					<div class="barra-filtros-mobile__section">
						<h3><?php esc_html_e( 'Tipo de Conteúdo', 'vibe-animes' ); ?></h3>
						<?php mm_render_component( 'molecules', 'grupo-filtros-chips', array(
							'titulo'       => '',
							'name'         => 'tipo_conteudo',
							'tipo'         => 'radio',
							'opcoes'       => $tipos_encontrados,
							'selecionados' => array( $tipo_conteudo_atual ),
						) ); ?>
					</div>
					<?php endif; ?>

					<!-- Ordenação -->
					<div class="barra-filtros-mobile__section">
						<h3><?php esc_html_e( 'Ordenação', 'vibe-animes' ); ?></h3>
						<?php mm_render_component( 'molecules', 'grupo-filtros-chips', array(
							'titulo'       => '',
							'name'         => 'ordem',
							'tipo'         => 'radio',
							'opcoes'       => $ordem_options,
							'selecionados' => array( $ordem_atual ),
						) ); ?>
					</div>

					<div class="barra-filtros-mobile__footer">
						<button type="button" class="btn btn--outline barra-filtros-mobile__clear" data-filtros-clear="form-filtros-busca-mobile">
							<?php esc_html_e( 'Limpar tudo', 'vibe-animes' ); ?>
						</button>
						<button type="submit" class="btn btn--primary barra-filtros-mobile__apply">
							<?php esc_html_e( 'Aplicar Filtros', 'vibe-animes' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<!-- Overlay (fora do form para não enviar) -->
<div
	class="barra-filtros-mobile__overlay"
	id="filtros-busca-sheet-overlay"
	data-filtros-close="filtros-busca-sheet"
	aria-hidden="true"
></div>

