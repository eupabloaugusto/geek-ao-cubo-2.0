<?php
/**
 * Organism: Barra de Filtros Mobile Personagens (barra-filtros-personagens-mobile)
 *
 * Painel de filtros otimizado para telas touch.
 * Clone estrutural de barra-filtros-mobile.php — apenas filtros específicos de personagem.
 *
 * @package vibe-animes
 *
 * @param string $class      Classes CSS adicionais para a barra sticky.
 * @param string $action_url URL de destino do formulário. Default: archive de 'personagem'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class      = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$action_url = isset( $args['action_url'] )
	? esc_url( $args['action_url'] )
	: esc_url( get_permalink() ?: home_url( '/' ) );

$sheet_id   = 'barra-filtros-personagens-sheet';
$form_id    = 'form-filtros-personagens-mobile';

// ── Valores atualmente selecionados (persistência GET) ────────────────────
$sel_busca      = isset( $_GET['busca'] )     ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : '';
$sel_ordem      = isset( $_GET['ordem'] )     ? array( sanitize_key( $_GET['ordem'] ) )             : array();

$count_ativos = count( array_filter( $sel_ordem ) );

$ordem_options = array(
	'populares'  => __( 'Mais Populares', 'vibe-animes' ),
	'alfabetica' => __( 'Ordem Alfabética', 'vibe-animes' ),
);
?>
<div class="barra-filtros-mobile <?php echo $class; ?>">
	<form
		method="get"
		action="<?php echo $action_url; ?>"
		class="barra-filtros-mobile__form"
		id="<?php echo $form_id; ?>"
		role="search"
		aria-label="<?php esc_attr_e( 'Filtrar personagens', 'vibe-animes' ); ?>"
	>
		<?php
		$lang = vibe_multilingual_get_current_language();
		if ( $lang && $lang !== 'pt-BR' ) {
			echo '<input type="hidden" name="app_lang" value="' . esc_attr( $lang ) . '" />';
		}
		?>
		<!-- ── Barra Sticky ─────────────────────────────────────── -->
		<div class="barra-filtros-mobile__bar">
			<!-- Campo de Busca Rápida -->
			<label class="barra-filtros-mobile__search-wrap" for="filtros-personagens-mobile-busca">
				<svg class="barra-filtros-mobile__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
				</svg>
				<input
					type="search"
					id="filtros-personagens-mobile-busca"
					name="busca"
					class="barra-filtros-mobile__search-input"
					placeholder="<?php esc_attr_e( 'Buscar personagem...', 'vibe-animes' ); ?>"
					value="<?php echo esc_attr( $sel_busca ); ?>"
					autocomplete="off"
				>
			</label>

			<!-- Botão Toggle do Bottom Sheet -->
			<?php mm_render_component( 'atoms', 'btn-filtros-toggle', array(
				'target' => $sheet_id,
				'count'  => $count_ativos,
			) ); ?>
		</div>

		<!-- ── Chips de Filtros Ativos (resumo visual) ──────────── -->
		<?php if ( $count_ativos > 0 ) : ?>
			<div class="barra-filtros-mobile__ativos" aria-label="<?php esc_attr_e( 'Filtros ativos', 'vibe-animes' ); ?>">
				<?php foreach ( $sel_ordem as $slug ) :
				if ( empty( $slug ) ) continue;
				$label = isset( $ordem_options[ $slug ] ) ? $ordem_options[ $slug ] : $slug;
			?>
				<span class="barra-filtros-mobile__ativo-chip barra-filtros-mobile__ativo-chip--ordem">
					<?php echo esc_html( $label ); ?>
					<button type="button" class="barra-filtros-mobile__ativo-remove" data-filtros-remove="ordem" data-filtros-value="<?php echo esc_attr( $slug ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Remover filtro %s', 'vibe-animes' ), $label ) ); ?>">×</button>
				</span>
			<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- ── Bottom Sheet ─────────────────────────────────────── -->
		<div
			class="barra-filtros-mobile__sheet"
			id="<?php echo $sheet_id; ?>"
			role="dialog"
			aria-modal="true"
			aria-label="<?php esc_attr_e( 'Filtros de busca', 'vibe-animes' ); ?>"
			aria-hidden="true"
		>
			<!-- Handle de arrastar -->
			<div class="barra-filtros-mobile__handle" aria-hidden="true"></div>

			<!-- Cabeçalho do Sheet -->
			<div class="barra-filtros-mobile__sheet-header">
				<span class="barra-filtros-mobile__sheet-title">
					<?php _e( 'Filtrar por', 'vibe-animes' ); ?>
				</span>
				<button
					type="button"
					class="barra-filtros-mobile__close"
					data-filtros-close="<?php echo $sheet_id; ?>"
					aria-label="<?php esc_attr_e( 'Fechar filtros', 'vibe-animes' ); ?>"
				>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
					</svg>
				</button>
			</div>

			<!-- Corpo com Grupos de Filtros -->
			<div class="barra-filtros-mobile__sheet-body">
				<?php mm_render_component( 'molecules', 'grupo-filtros-chips', array(
					'titulo'       => __( 'Ordenar por', 'vibe-animes' ),
					'name'         => 'ordem',
					'tipo'         => 'radio',
					'opcoes'       => $ordem_options,
					'selecionados' => $sel_ordem,
				) ); ?>
			</div>

			<!-- Ações do Sheet -->
			<div class="barra-filtros-mobile__sheet-actions">
				<button
					type="button"
					class="btn btn--secondary barra-filtros-mobile__btn-limpar"
					data-filtros-clear="<?php echo $form_id; ?>"
				>
					<?php _e( 'Limpar tudo', 'vibe-animes' ); ?>
				</button>
				<button
					type="submit"
					class="btn btn--primary barra-filtros-mobile__btn-aplicar"
				>
					<?php _e( 'Aplicar', 'vibe-animes' ); ?>
				</button>
			</div>
		</div><!-- /.barra-filtros-mobile__sheet -->

	</form><!-- /.barra-filtros-mobile__form -->
</div><!-- /.barra-filtros-mobile -->

<!-- Overlay (fora do form para não enviar) -->
<div
	class="barra-filtros-mobile__overlay"
	id="<?php echo $sheet_id; ?>-overlay"
	data-filtros-close="<?php echo $sheet_id; ?>"
	aria-hidden="true"
></div>

