<?php
/**
 * Includes: Class Vibe_Admin_Post_Translation_Meta_Box
 *
 * Registra e renderiza o painel de tradução manual e controle de status de IA
 * na tela de edição de posts.
 *
 * @package vibe-animes
 * @since   2.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Vibe_Admin_Post_Translation_Meta_Box {

	/**
	 * Inicializa os ganchos da Meta Box
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box_data' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Enfileira scripts e estilos apenas na tela de posts
	 */
	public static function enqueue_assets( $hook ) {
		global $post_type;
		if ( ( 'post-new.php' === $hook || 'post.php' === $hook ) && 'post' === $post_type ) {
			// CSS e JS internos injetados inline ou via arquivos se preferencial. Como é um painel simples e isolado,
			// colocaremos inline ou enfileirado para manter a portabilidade máxima.
		}
	}

	/**
	 * Adiciona a Meta Box de Traduções
	 */
	public static function add_meta_box() {
		add_meta_box(
			'vibe_post_translations',
			__( 'Traduções do Post (Multilingue IA)', 'vibe-animes' ),
			array( __CLASS__, 'render_meta_box' ),
			'post',
			'normal',
			'high'
		);
	}

	/**
	 * Salva as alterações feitas de forma manual pelo administrador
	 */
	public static function save_meta_box_data( $post_id ) {
		// Verificações de segurança
		if ( ! isset( $_POST['vibe_post_translation_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['vibe_post_translation_nonce'], 'vibe_save_post_translations' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$languages = array( 'en', 'es', 'fr', 'de' );
		foreach ( $languages as $code ) {
			// Título
			if ( isset( $_POST["_post_title_{$code}"] ) ) {
				update_post_meta( $post_id, "_post_title_{$code}", sanitize_text_field( wp_unslash( $_POST["_post_title_{$code}"] ) ) );
			}
			// Conteúdo (mantém HTML bruto e quebras de linha)
			if ( isset( $_POST["_post_content_{$code}"] ) ) {
				update_post_meta( $post_id, "_post_content_{$code}", wp_kses_post( wp_unslash( $_POST["_post_content_{$code}"] ) ) );
			}
			// Resumo
			if ( isset( $_POST["_post_excerpt_{$code}"] ) ) {
				update_post_meta( $post_id, "_post_excerpt_{$code}", sanitize_textarea_field( wp_unslash( $_POST["_post_excerpt_{$code}"] ) ) );
			}
			// SEO Title
			if ( isset( $_POST["_post_seo_title_{$code}"] ) ) {
				update_post_meta( $post_id, "_post_seo_title_{$code}", sanitize_text_field( wp_unslash( $_POST["_post_seo_title_{$code}"] ) ) );
			}
			// SEO Description
			if ( isset( $_POST["_post_seo_desc_{$code}"] ) ) {
				update_post_meta( $post_id, "_post_seo_desc_{$code}", sanitize_textarea_field( wp_unslash( $_POST["_post_seo_desc_{$code}"] ) ) );
			}

			// Marca que o idioma foi revisado/atualizado com sucesso
			update_post_meta( $post_id, "_post_tr_status_{$code}", 'success' );
		}
	}

	/**
	 * Renderiza a interface da Meta Box no painel de edição
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'vibe_save_post_translations', 'vibe_post_translation_nonce' );

		$languages = array(
			'en' => array( 'name' => 'Inglês', 'flag' => '🇬🇧' ),
			'es' => array( 'name' => 'Espanhol', 'flag' => '🇪🇸' ),
			'fr' => array( 'name' => 'Francês', 'flag' => '🇫🇷' ),
			'de' => array( 'name' => 'Alemão', 'flag' => '🇩🇪' )
		);
		?>
		<style>
			.vibe-tr-metabox {
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				background: #fcfcfc;
				margin: -6px -12px -12px -12px;
				border-radius: 0 0 4px 4px;
			}
			.vibe-tr-header {
				display: flex;
				background: #23282d;
				padding: 10px 15px;
				align-items: center;
				justify-content: space-between;
				color: #fff;
			}
			.vibe-tr-header h3 {
				margin: 0;
				color: #fff;
				font-size: 14px;
				font-weight: 600;
			}
			.vibe-tr-translate-all {
				background: #007cba;
				color: #fff;
				border: none;
				padding: 6px 12px;
				border-radius: 4px;
				cursor: pointer;
				font-weight: 500;
				font-size: 12px;
				transition: background 0.2s ease;
			}
			.vibe-tr-translate-all:hover {
				background: #006ba1;
			}
			.vibe-tr-tabs-nav {
				display: flex;
				background: #f0f0f1;
				border-bottom: 1px solid #ccd0d4;
				margin: 0;
				padding: 0;
				list-style: none;
			}
			.vibe-tr-tab-link {
				padding: 12px 20px;
				cursor: pointer;
				font-weight: 600;
				font-size: 13px;
				color: #50575e;
				border-right: 1px solid #ccd0d4;
				border-bottom: 1px solid transparent;
				background: #f0f0f1;
				display: flex;
				align-items: center;
				gap: 8px;
			}
			.vibe-tr-tab-link:hover {
				background: #e0e0e2;
			}
			.vibe-tr-tab-link.active {
				background: #fff;
				border-bottom-color: #fff;
				color: #1d2327;
			}
			.vibe-tr-status-dot {
				width: 8px;
				height: 8px;
				border-radius: 50%;
				display: inline-block;
			}
			.vibe-tr-status-dot.success { background: #46b450; }
			.vibe-tr-status-dot.pending { background: #ffb900; }
			.vibe-tr-status-dot.error { background: #dc3232; }
			.vibe-tr-status-dot.none { background: #999; }

			.vibe-tr-tab-content {
				display: none;
				padding: 20px;
				background: #fff;
			}
			.vibe-tr-tab-content.active {
				display: block;
			}
			.vibe-tr-field-group {
				margin-bottom: 20px;
			}
			.vibe-tr-field-group label {
				display: block;
				font-weight: 600;
				margin-bottom: 6px;
				color: #1d2327;
			}
			.vibe-tr-field-group input[type="text"],
			.vibe-tr-field-group textarea {
				width: 100%;
				padding: 10px;
				border: 1px solid #8c8f94;
				border-radius: 4px;
				font-size: 13px;
				background: #fff;
			}
			.vibe-tr-field-group textarea.content-editor {
				font-family: Consolas, Monaco, monospace;
				font-size: 12px;
				line-height: 1.5;
				height: 250px;
				background: #fafafa;
			}
			.vibe-tr-field-group textarea.excerpt-editor,
			.vibe-tr-field-group textarea.seo-editor {
				height: 80px;
			}
			.vibe-tr-action-bar {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-top: 20px;
				padding-top: 15px;
				border-top: 1px solid #f0f0f1;
			}
			.vibe-tr-lang-status-text {
				font-size: 12px;
				font-style: italic;
				color: #646970;
			}
			.vibe-tr-lang-status-text strong {
				color: #1d2327;
			}
			.vibe-tr-btn-translate-single {
				background: #f6f7f7;
				color: #2271b1;
				border: 1px solid #2271b1;
				padding: 6px 12px;
				border-radius: 4px;
				cursor: pointer;
				font-weight: 500;
				font-size: 12px;
				transition: all 0.2s ease;
			}
			.vibe-tr-btn-translate-single:hover {
				background: #f0f6fc;
				color: #135e96;
				border-color: #135e96;
			}
			.vibe-tr-spinner {
				display: none;
				width: 16px;
				height: 16px;
				border: 2px solid #f3f3f3;
				border-top: 2px solid #2271b1;
				border-radius: 50%;
				animation: vibe-tr-spin 1s linear infinite;
			}
			@keyframes vibe-tr-spin {
				0% { transform: rotate(0deg); }
				100% { transform: rotate(360deg); }
			}
		</style>

		<div class="vibe-tr-metabox" id="vibe-post-translator-panel">
			<!-- Header -->
			<div class="vibe-tr-header">
				<h3><?php _e( 'Tradução Automatizada por IA (Groq/Llama)', 'vibe-animes' ); ?></h3>
				<button type="button" class="vibe-tr-translate-all" id="vibe-tr-btn-all" data-post-id="<?php echo $post->ID; ?>">
					<?php _e( 'Traduzir Todos via IA', 'vibe-animes' ); ?>
				</button>
			</div>

			<!-- Tabs Nav -->
			<ul class="vibe-tr-tabs-nav">
				<?php
				$first = true;
				foreach ( $languages as $code => $info ) :
					$status = get_post_meta( $post->ID, "_post_tr_status_{$code}", true ) ?: 'none';
					?>
					<li>
						<div class="vibe-tr-tab-link <?php echo $first ? 'active' : ''; ?>" data-target="vibe-tr-tab-<?php echo $code; ?>">
							<span><?php echo $info['flag']; ?> <?php echo $info['name']; ?></span>
							<span class="vibe-tr-status-dot <?php echo esc_attr( $status ); ?>" title="Status: <?php echo esc_attr( $status ); ?>" id="status-dot-<?php echo $code; ?>"></span>
						</div>
					</li>
					<?php
					$first = false;
				endforeach;
				?>
			</ul>

			<!-- Tabs Contents -->
			<?php
			$first = true;
			foreach ( $languages as $code => $info ) :
				$title      = get_post_meta( $post->ID, "_post_title_{$code}", true );
				$content    = get_post_meta( $post->ID, "_post_content_{$code}", true );
				$excerpt    = get_post_meta( $post->ID, "_post_excerpt_{$code}", true );
				$seo_title  = get_post_meta( $post->ID, "_post_seo_title_{$code}", true );
				$seo_desc   = get_post_meta( $post->ID, "_post_seo_desc_{$code}", true );
				$status     = get_post_meta( $post->ID, "_post_tr_status_{$code}", true ) ?: 'Pendente';
				$error      = get_post_meta( $post->ID, "_post_tr_error_{$code}", true );
				?>
				<div class="vibe-tr-tab-content <?php echo $first ? 'active' : ''; ?>" id="vibe-tr-tab-<?php echo $code; ?>">
					
					<!-- Título -->
					<div class="vibe-tr-field-group">
						<label><?php printf( __( 'Título Traduzido (%s)', 'vibe-animes' ), $info['name'] ); ?></label>
						<input type="text" name="_post_title_<?php echo $code; ?>" value="<?php echo esc_attr( $title ); ?>" placeholder="Insira o título traduzido" />
					</div>

					<!-- Resumo -->
					<div class="vibe-tr-field-group">
						<label><?php printf( __( 'Resumo/Excerpt Traduzido (%s)', 'vibe-animes' ), $info['name'] ); ?></label>
						<textarea name="_post_excerpt_<?php echo $code; ?>" class="excerpt-editor" placeholder="Insira a tradução do resumo..."><?php echo esc_textarea( $excerpt ); ?></textarea>
					</div>

					<!-- Conteúdo -->
					<div class="vibe-tr-field-group">
						<label><?php printf( __( 'Conteúdo Traduzido (%s) — Inclui Marcações e Tags', 'vibe-animes' ), $info['name'] ); ?></label>
						<textarea name="_post_content_<?php echo $code; ?>" class="content-editor" placeholder="Aguardando geração da tradução por IA..."><?php echo esc_textarea( $content ); ?></textarea>
					</div>

					<!-- SEO Title -->
					<div class="vibe-tr-field-group">
						<label><?php printf( __( 'Meta Title SEO Traduzido (%s)', 'vibe-animes' ), $info['name'] ); ?></label>
						<input type="text" name="_post_seo_title_<?php echo $code; ?>" value="<?php echo esc_attr( $seo_title ); ?>" placeholder="Título personalizado SEO em outro idioma" />
					</div>

					<!-- SEO Description -->
					<div class="vibe-tr-field-group">
						<label><?php printf( __( 'Meta Description SEO Traduzida (%s)', 'vibe-animes' ), $info['name'] ); ?></label>
						<textarea name="_post_seo_desc_<?php echo $code; ?>" class="seo-editor" placeholder="Descrição personalizada SEO..."><?php echo esc_textarea( $seo_desc ); ?></textarea>
					</div>

					<!-- Action/Status Bar -->
					<div class="vibe-tr-action-bar">
						<div class="vibe-tr-lang-status-text" id="status-text-<?php echo $code; ?>">
							<?php printf( __( 'Status atual: <strong>%s</strong>', 'vibe-animes' ), strtoupper( $status ) ); ?>
							<?php if ( $error ) : ?>
								<br><span style="color: #dc3232;"><?php echo esc_html( $error ); ?></span>
							<?php endif; ?>
						</div>
						<div style="display: flex; align-items: center; gap: 10px;">
							<div class="vibe-tr-spinner" id="spinner-<?php echo $code; ?>"></div>
							<button type="button" class="vibe-tr-btn-translate-single" data-post-id="<?php echo $post->ID; ?>" data-lang="<?php echo $code; ?>">
								<?php printf( __( 'Traduzir apenas %s via IA', 'vibe-animes' ), $info['name'] ); ?>
							</button>
						</div>
					</div>

				</div>
				<?php
				$first = false;
			endforeach;
			?>
		</div>

		<!-- Script JS para as Abas e Chamadas AJAX -->
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const panel = document.getElementById('vibe-post-translator-panel');
				if (!panel) return;

				// Lógica das abas
				const tabs = panel.querySelectorAll('.vibe-tr-tab-link');
				const contents = panel.querySelectorAll('.vibe-tr-tab-content');

				tabs.forEach(tab => {
					tab.addEventListener('click', function() {
						tabs.forEach(t => t.classList.remove('active'));
						contents.forEach(c => c.classList.remove('active'));

						this.classList.add('active');
						const targetId = this.getAttribute('data-target');
						document.getElementById(targetId).classList.add('active');
					});
				});

				// Disparar AJAX para tradução manual (Único Idioma)
				const buttons = panel.querySelectorAll('.vibe-tr-btn-translate-single');
				buttons.forEach(button => {
					button.addEventListener('click', function() {
						const postId = this.getAttribute('data-post-id');
						const lang = this.getAttribute('data-lang');
						
						runTranslation(postId, lang);
					});
				});

				// Disparar AJAX para todos os idiomas
				const btnAll = document.getElementById('vibe-tr-btn-all');
				if (btnAll) {
					btnAll.addEventListener('click', function() {
						const postId = this.getAttribute('data-post-id');
						
						const langs = ['en', 'es', 'fr', 'de'];
						let promiseChain = Promise.resolve();

						langs.forEach(lang => {
							promiseChain = promiseChain.then(() => runTranslation(postId, lang));
						});
					});
				}

				function runTranslation(postId, lang) {
					const spinner = document.getElementById('spinner-' + lang);
					const statusDot = document.getElementById('status-dot-' + lang);
					const statusText = document.getElementById('status-text-' + lang);
					const tabContent = document.getElementById('vibe-tr-tab-' + lang);

					if (spinner) spinner.style.display = 'block';
					if (statusDot) {
						statusDot.className = 'vibe-tr-status-dot pending';
					}
					if (statusText) statusText.innerHTML = 'Status atual: <strong>TRADUZINDO...</strong>';

					return new Promise((resolve) => {
						const formData = new FormData();
						formData.append('action', 'vibe_translate_post_manual');
						formData.append('post_id', postId);
						formData.append('lang', lang);

						fetch(ajaxurl, {
							method: 'POST',
							body: formData
						})
						.then(response => response.json())
						.then(data => {
							if (data.success) {
								if (statusDot) statusDot.className = 'vibe-tr-status-dot success';
								if (statusText) statusText.innerHTML = 'Status atual: <strong>SUCCESS</strong>';
								alert(data.data.message);
								
								// Faz reload leve dos dados recém traduzidos buscando via REST/admin se possível
								// ou avisa para recarregar o post para visualizar.
								location.reload();
							} else {
								if (statusDot) statusDot.className = 'vibe-tr-status-dot error';
								if (statusText) statusText.innerHTML = 'Status atual: <strong>ERROR</strong><br><span style="color: #dc3232;">' + (data.data.message || 'Erro deconhecido') + '</span>';
								alert('Erro: ' + (data.data.message || 'Falha na tradução.'));
							}
						})
						.catch(error => {
							console.error(error);
							if (statusDot) statusDot.className = 'vibe-tr-status-dot error';
							if (statusText) statusText.innerHTML = 'Status atual: <strong>ERROR</strong><br><span style="color: #dc3232;">Erro de rede.</span>';
						})
						.finally(() => {
							if (spinner) spinner.style.display = 'none';
							resolve();
						});
					});
				}
			});
		</script>
		<?php
	}
}
Vibe_Admin_Post_Translation_Meta_Box::init();

