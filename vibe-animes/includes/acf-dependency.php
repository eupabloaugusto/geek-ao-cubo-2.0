<?php
/**
 * ACF — Aviso de Dependência
 *
 * Exibe um aviso de erro no painel do WordPress se o plugin
 * Advanced Custom Fields (ACF) não estiver instalado e ativo.
 *
 * Sem o ACF, os campos de metadados dos CPTs não funcionam,
 * o que quebraria silenciosamente a exibição dos templates.
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verifica se o ACF está ativo. Se não estiver, exibe um aviso de admin.
 * Suporta tanto ACF Free quanto ACF Pro.
 */
function mm_check_acf_dependency() {
	// Verifica ACF Free e ACF Pro (ambos definem a função acf_get_setting)
	if ( function_exists( 'acf_get_setting' ) ) {
		return; // ACF está ativo, nada a fazer
	}

	// ACF não encontrado — exibir aviso
	add_action( 'admin_notices', 'mm_acf_missing_notice' );
}
add_action( 'admin_init', 'mm_check_acf_dependency' );


/**
 * Renderiza o aviso de admin quando o ACF não está instalado.
 */
function mm_acf_missing_notice() {
	?>
	<div class="notice notice-error is-dismissible" role="alert">
		<p>
			<strong>⚠️ Modo Maratona — Ação necessária:</strong>
			O plugin <strong>Advanced Custom Fields (ACF)</strong> não está instalado ou ativo.
			Os campos de metadados dos animes, episódios, temporadas e reviews não funcionarão sem ele.
		</p>
		<p>
			<a
				href="<?php echo esc_url( admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=term' ) ); ?>"
				class="button button-primary"
			>
				Instalar ACF agora
			</a>
			&nbsp;
			<a
				href="https://www.advancedcustomfields.com/pro/"
				target="_blank"
				rel="noopener noreferrer"
				class="button"
			>
				Sobre o ACF Pro ↗
			</a>
		</p>
	</div>
	<?php
}


/**
 * Oculta os campos de relação bidirecional (mm-hidden-field) no editor wp-admin.
 * Esses campos são gerenciados programaticamente — o editor não deve editá-los.
 *
 * Injeta CSS mínimo no admin somente quando necessário.
 */
function mm_hide_acf_readonly_fields() {
	$screen = get_current_screen();

	// Aplica apenas nas telas de edição dos CPTs relevantes
	if ( ! $screen || ! in_array( $screen->post_type, array( 'anime', 'episodio', 'temporada', 'review' ), true ) ) {
		return;
	}

	echo '<style>
		.acf-field .mm-hidden-field,
		[data-field_type="relationship"].mm-hidden-field {
			display: none !important;
		}
	</style>';
}
add_action( 'admin_head', 'mm_hide_acf_readonly_fields' );
