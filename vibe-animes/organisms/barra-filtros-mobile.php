<?php
/**
 * Organism: Barra de Filtros Mobile (barra-filtros-mobile)
 *
 * Painel de filtros otimizado para telas touch.
 * Composto por:
 * - Barra sticky: campo de busca rápida + botão toggle com badge de ativos.
 * - Bottom sheet: overlay + drawer com grupos de chips (gênero, status, ordem) + ações.
 *
 * Opções buscadas dinamicamente das taxonomias 'genero' e 'status_exibicao'.
 * Valores selecionados lidos de $_GET para persistência entre navegações.
 *
 * @package geek-ao-cubo
 *
 * @param string $class      Classes CSS adicionais para a barra sticky.
 * @param string $action_url URL de destino do formulário. Default: archive de 'anime'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class      = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$action_url = isset( $args['action_url'] )
	? esc_url( $args['action_url'] )
	: esc_url( get_post_type_archive_link( 'anime' ) ?: home_url( '/' ) );

$sheet_id   = 'barra-filtros-sheet';
$form_id    = 'form-filtros-mobile';

// ── Valores atualmente selecionados (persistência GET) ────────────────────
$sel_busca      = isset( $_GET['busca'] )            ? sanitize_text_field( wp_unslash( $_GET['busca'] ) )            : '';
$sel_generos    = isset( $_GET['genero'] )       ? array_map( 'sanitize_key', (array) $_GET['genero'] )       : array();
$sel_status     = isset( $_GET['status_anime'] ) ? array( sanitize_key( $_GET['status_anime'] ) )             : array();
$sel_idioma     = isset( $_GET['idioma'] )       ? array( sanitize_key( $_GET['idioma'] ) )                   : array();
$sel_tipo_midia = isset( $_GET['tipo_midia'] )   ? array( sanitize_key( $_GET['tipo_midia'] ) )               : array();
$sel_ordem      = isset( $_GET['ordem'] )        ? array( sanitize_key( $_GET['ordem'] ) )                    : array();

$is_manga = ( isset( $_GET['tipo_midia'] ) && 'manga' === $_GET['tipo_midia'] );

$count_ativos = count( array_filter( $sel_generos ) )
	+ count( array_filter( $sel_status ) )
	+ ( ! $is_manga ? count( array_filter( $sel_idioma ) ) : 0 )
	+ count( array_filter( $sel_tipo_midia ) )
	+ count( array_filter( $sel_ordem ) );

// ── Opções dinâmicas das taxonomias ──────────────────────────────────────
$genero_terms   = function_exists( 'get_terms' ) ? get_terms( array( 'taxonomy' => 'genero', 'hide_empty' => true ) ) : array();
$genero_options = array();
if ( ! is_wp_error( $genero_terms ) && ! empty( $genero_terms ) ) {
	foreach ( $genero_terms as $t ) {
		$genero_options[ $t->slug ] = $t->name;
	}
}
if ( empty( $genero_options ) ) {
	$genero_options = array(
		'acao'         => __( 'Ação', 'geek-ao-cubo' ),
		'romance'      => __( 'Romance', 'geek-ao-cubo' ),
		'shonen'       => __( 'Shonen', 'geek-ao-cubo' ),
		'isekai'       => __( 'Isekai', 'geek-ao-cubo' ),
		'slice-of-life' => __( 'Slice of Life', 'geek-ao-cubo' ),
		'fantasia'     => __( 'Fantasia', 'geek-ao-cubo' ),
	);
}

// Opções para Filtro de Status — lista curada fixa
// Opções para Filtro de Status
$status_options = array(
	'todos'      => __( 'Todos', 'geek-ao-cubo' ),
);
if ( $is_manga ) {
	$status_terms = get_terms( array( 'taxonomy' => 'status_manga', 'hide_empty' => false ) );
	if ( ! is_wp_error( $status_terms ) && ! empty( $status_terms ) ) {
		foreach ( $status_terms as $term ) {
			$status_options[ $term->slug ] = $term->name;
		}
	} else {
		$status_options['em-publicacao'] = __( 'Em Publicação', 'geek-ao-cubo' );
		$status_options['finalizado']    = __( 'Finalizado', 'geek-ao-cubo' );
		$status_options['em-hiato']      = __( 'Em Hiato', 'geek-ao-cubo' );
	}
} else {
	$status_options['lancamento'] = __( 'Em Lançamento', 'geek-ao-cubo' );
	$status_options['finalizado'] = __( 'Finalizado', 'geek-ao-cubo' );
}

$idioma_options = array(
	'todos'     => __( 'Todos', 'geek-ao-cubo' ),
	'legendado' => __( 'Legendados', 'geek-ao-cubo' ),
	'dublado'   => __( 'Dublados', 'geek-ao-cubo' ),
);

$tipo_midia_options = array(
	'todos'   => __( 'Todos', 'geek-ao-cubo' ),
	'serie'   => __( 'Séries (TV/Web)', 'geek-ao-cubo' ),
	'filme'   => __( 'Filmes', 'geek-ao-cubo' ),
	'OVA'     => __( 'OVA', 'geek-ao-cubo' ),
	'Special' => __( 'Especiais', 'geek-ao-cubo' ),
	'manga'   => __( 'Mangá', 'geek-ao-cubo' ),
);

$ordem_options = array(
	'populares'  => __( 'Mais Populares', 'geek-ao-cubo' ),
	'recente'    => __( 'Mais Recente', 'geek-ao-cubo' ),
	'alfabetica' => __( 'Ordem Alfabética', 'geek-ao-cubo' ),
);
?>
<div class="barra-filtros-mobile <?php echo $class; ?>">
	<form
		method="get"
		action="<?php echo $action_url; ?>"
		class="barra-filtros-mobile__form"
		id="<?php echo $form_id; ?>"
		role="search"
		aria-label="<?php esc_attr_e( 'Filtrar animes', 'geek-ao-cubo' ); ?>"
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
			<label class="barra-filtros-mobile__search-wrap" for="filtros-mobile-busca">
				<svg class="barra-filtros-mobile__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
				</svg>
				<input
					type="search"
					id="filtros-mobile-busca"
					name="busca"
					class="barra-filtros-mobile__search-input"
					placeholder="<?php echo esc_attr( $is_manga ? __( 'Buscar mangá...', 'geek-ao-cubo' ) : __( 'Buscar anime...', 'geek-ao-cubo' ) ); ?>"
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
			<div class="barra-filtros-mobile__ativos" aria-label="<?php esc_attr_e( 'Filtros ativos', 'geek-ao-cubo' ); ?>">
				<?php foreach ( $sel_generos as $slug ) :
					$label = isset( $genero_options[ $slug ] ) ? $genero_options[ $slug ] : $slug;
				?>
					<span class="barra-filtros-mobile__ativo-chip">
						<?php echo esc_html( $label ); ?>
						<button type="button" class="barra-filtros-mobile__ativo-remove" data-filtros-remove="genero" data-filtros-value="<?php echo esc_attr( $slug ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Remover filtro %s', 'geek-ao-cubo' ), $label ) ); ?>">×</button>
					</span>
				<?php endforeach; ?>
				<?php foreach ( $sel_status as $slug ) :
					$label = isset( $status_options[ $slug ] ) ? $status_options[ $slug ] : $slug;
				?>
					<span class="barra-filtros-mobile__ativo-chip">
						<?php echo esc_html( $label ); ?>
						<button type="button" class="barra-filtros-mobile__ativo-remove" data-filtros-remove="status_anime" data-filtros-value="<?php echo esc_attr( $slug ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Remover filtro %s', 'geek-ao-cubo' ), $label ) ); ?>">×</button>
					</span>
				<?php endforeach; ?>
				<?php foreach ( $sel_ordem as $slug ) :
					$label = isset( $ordem_options[ $slug ] ) ? $ordem_options[ $slug ] : $slug;
				?>
					<span class="barra-filtros-mobile__ativo-chip barra-filtros-mobile__ativo-chip--ordem">
						<?php echo esc_html( $label ); ?>
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
			aria-label="<?php esc_attr_e( 'Filtros de busca', 'geek-ao-cubo' ); ?>"
			aria-hidden="true"
		>
			<!-- Handle de arrastar -->
			<div class="barra-filtros-mobile__handle" aria-hidden="true"></div>

			<!-- Cabeçalho do Sheet -->
			<div class="barra-filtros-mobile__sheet-header">
				<span class="barra-filtros-mobile__sheet-title">
					<?php _e( 'Filtrar por', 'geek-ao-cubo' ); ?>
				</span>
				<button
					type="button"
					class="barra-filtros-mobile__close"
					data-filtros-close="<?php echo $sheet_id; ?>"
					aria-label="<?php esc_attr_e( 'Fechar filtros', 'geek-ao-cubo' ); ?>"
				>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
					</svg>
				</button>
			</div>

			<!-- Corpo com Grupos de Filtros -->
			<div class="barra-filtros-mobile__sheet-body">
				<?php mm_render_component( 'molecules', 'grupo-filtros-chips', array(
					'titulo'       => __( 'Gênero', 'geek-ao-cubo' ),
					'name'         => 'genero',
					'tipo'         => 'checkbox',
					'opcoes'       => $genero_options,
					'selecionados' => $sel_generos,
				) ); ?>

				<?php mm_render_component( 'molecules', 'grupo-filtros-chips', array(
					'titulo'       => __( 'Status', 'geek-ao-cubo' ),
					'name'         => 'status_anime',
					'tipo'         => 'radio',
					'opcoes'       => $status_options,
					'selecionados' => $sel_status,
				) ); ?>

				<?php if ( ! $is_manga ) : ?>
				<?php mm_render_component( 'molecules', 'grupo-filtros-chips', array(
					'titulo'       => __( 'Idioma', 'geek-ao-cubo' ),
					'name'         => 'idioma',
					'tipo'         => 'radio',
					'opcoes'       => $idioma_options,
					'selecionados' => $sel_idioma,
				) ); ?>
				<?php endif; ?>

				<?php mm_render_component( 'molecules', 'grupo-filtros-chips', array(
					'titulo'       => __( 'Tipo de Mídia', 'geek-ao-cubo' ),
					'name'         => 'tipo_midia',
					'tipo'         => 'radio',
					'opcoes'       => $tipo_midia_options,
					'selecionados' => $sel_tipo_midia,
				) ); ?>

				<?php mm_render_component( 'molecules', 'grupo-filtros-chips', array(
					'titulo'       => __( 'Ordenar por', 'geek-ao-cubo' ),
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
					<?php _e( 'Limpar tudo', 'geek-ao-cubo' ); ?>
				</button>
				<button
					type="submit"
					class="btn btn--primary barra-filtros-mobile__btn-aplicar"
				>
					<?php _e( 'Aplicar', 'geek-ao-cubo' ); ?>
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
