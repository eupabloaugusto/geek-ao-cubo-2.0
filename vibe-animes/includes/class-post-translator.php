<?php
/**
 * Includes: Class Vibe_Post_Translator
 *
 * Gerencia a tradução automática e manual de posts (artigos) do blog no backend
 * utilizando a API do Groq (Llama-3).
 *
 * @package vibe-animes
 * @since   2.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Vibe_Post_Translator {

	/**
	 * Inicializa os ganchos do tradutor
	 */
	public static function init() {
		// Gatilho automático ao publicar ou atualizar post publicado
		add_action( 'transition_post_status', array( __CLASS__, 'handle_post_status_transition' ), 10, 3 );

		// Ajax para tradução manual via painel do post
		add_action( 'wp_ajax_vibe_translate_post_manual', array( __CLASS__, 'handle_manual_translate_ajax' ) );
	}

	/**
	 * Recupera a chave da API do Groq a partir do wp-config.php ou do .env da automação
	 */
	public static function get_groq_api_key() {
		if ( defined( 'GROQ_API_KEY' ) && ! empty( GROQ_API_KEY ) ) {
			return GROQ_API_KEY;
		}

		$env_file = get_template_directory() . '/../../../../Geek ao Cubo v2.2.2/Pipeline Traducao/.env';
		if ( file_exists( $env_file ) ) {
			$lines = file( $env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
			foreach ( $lines as $line ) {
				if ( strpos( $line, 'GROQ_KEY_1=' ) === 0 ) {
					return trim( str_replace( 'GROQ_KEY_1=', '', $line ) );
				}
			}
		}

		return '';
	}

	/**
	 * Escuta as transições de status de posts do WordPress
	 */
	public static function handle_post_status_transition( $new_status, $old_status, $post ) {
		// Apenas para posts padrão (artigos/notícias)
		if ( 'post' !== $post->post_type ) {
			return;
		}

		// Dispara apenas quando o post é publicado (ou atualizado já publicado)
		if ( 'publish' === $new_status ) {
			// Dispara a tradução de todos os idiomas
			self::translate_all_languages( $post->ID );
		}
	}

	/**
	 * Traduz o post para todos os 4 idiomas suportados
	 */
	public static function translate_all_languages( $post_id ) {
		$languages = array(
			'en' => 'English',
			'es' => 'Spanish',
			'fr' => 'French',
			'de' => 'German'
		);

		foreach ( $languages as $code => $lang_name ) {
			self::translate_post_for_lang( $post_id, $code, $lang_name );
			// Throttling mínimo para evitar limites de taxa da API (300ms)
			usleep( 300000 );
		}
	}

	/**
	 * Traduz o post para um idioma específico
	 */
	public static function translate_post_for_lang( $post_id, $code, $lang_name ) {
		$api_key = self::get_groq_api_key();
		if ( empty( $api_key ) ) {
			update_post_meta( $post_id, "_post_tr_status_{$code}", 'error' );
			update_post_meta( $post_id, "_post_tr_error_{$code}", 'Groq API Key não configurada.' );
			return false;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		// Marca status como em andamento
		update_post_meta( $post_id, "_post_tr_status_{$code}", 'pending' );

		// 1. Extração de Conteúdos
		$pt_title   = $post->post_title;
		$pt_content = $post->post_content;
		$pt_excerpt = $post->post_excerpt;

		// 2. Extração de Tags de SEO (RankMath e Yoast)
		$pt_seo_title = get_post_meta( $post_id, 'rank_math_title', true );
		if ( empty( $pt_seo_title ) ) {
			$pt_seo_title = get_post_meta( $post_id, '_yoast_wpseo_title', true );
		}
		
		$pt_seo_desc = get_post_meta( $post_id, 'rank_math_description', true );
		if ( empty( $pt_seo_desc ) ) {
			$pt_seo_desc = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
		}

		// 3. Tradução do Título
		$tr_title = self::call_groq_translate( $pt_title, $lang_name, false, $api_key );
		if ( ! $tr_title ) {
			self::mark_error( $post_id, $code, 'Falha ao traduzir o título.' );
			return false;
		}

		// 4. Tradução do Resumo (se vazio, gerará depois ou copia original se falhar)
		$tr_excerpt = '';
		if ( ! empty( trim( $pt_excerpt ) ) ) {
			$tr_excerpt = self::call_groq_translate( $pt_excerpt, $lang_name, false, $api_key );
		}

		// 5. Tradução de SEO Title e Desc (se configurados)
		$tr_seo_title = '';
		if ( ! empty( trim( $pt_seo_title ) ) ) {
			$tr_seo_title = self::call_groq_translate( $pt_seo_title, $lang_name, false, $api_key );
		}

		$tr_seo_desc = '';
		if ( ! empty( trim( $pt_seo_desc ) ) ) {
			$tr_seo_desc = self::call_groq_translate( $pt_seo_desc, $lang_name, false, $api_key );
		}

		// 6. Tradução do Conteúdo (Preservando blocos HTML/Gutenberg)
		$tr_content = self::call_groq_translate( $pt_content, $lang_name, true, $api_key );
		if ( ! $tr_content ) {
			self::mark_error( $post_id, $code, 'Falha ao traduzir o conteúdo principal.' );
			return false;
		}

		// 7. Salvamento bem-sucedido
		update_post_meta( $post_id, "_post_title_{$code}", trim( $tr_title ) );
		update_post_meta( $post_id, "_post_content_{$code}", trim( $tr_content ) );
		update_post_meta( $post_id, "_post_excerpt_{$code}", trim( $tr_excerpt ) );
		
		if ( ! empty( $tr_seo_title ) ) {
			update_post_meta( $post_id, "_post_seo_title_{$code}", trim( $tr_seo_title ) );
		}
		if ( ! empty( $tr_seo_desc ) ) {
			update_post_meta( $post_id, "_post_seo_desc_{$code}", trim( $tr_seo_desc ) );
		}

		update_post_meta( $post_id, "_post_tr_status_{$code}", 'success' );
		delete_post_meta( $post_id, "_post_tr_error_{$code}" );

		// Força limpeza do cache local do transiente
		delete_transient( "vibe_tr_title_{$code}_{$post_id}" );
		delete_transient( "vibe_tr_content_{$code}_{$post_id}" );
		delete_transient( "vibe_tr_excerpt_{$code}_{$post_id}" );
		delete_transient( "vibe_tr_seo_title_{$code}_{$post_id}" );
		delete_transient( "vibe_tr_seo_desc_{$code}_{$post_id}" );

		return true;
	}

	/**
	 * Marca erro na tradução de um idioma específico
	 */
	private static function mark_error( $post_id, $code, $message ) {
		update_post_meta( $post_id, "_post_tr_status_{$code}", 'error' );
		update_post_meta( $post_id, "_post_tr_error_{$code}", $message );
	}

	/**
	 * Faz a requisição HTTP para a API do Groq
	 */
	private static function call_groq_translate( $text, $lang_name, $is_html_content, $api_key ) {
		if ( empty( trim( $text ) ) ) {
			return $text;
		}

		$system_prompt = "You are a professional translator for a pop culture, anime, and games blog. ";
		$system_prompt .= "Translate the provided text from Portuguese to {$lang_name}. ";
		
		if ( $is_html_content ) {
			$system_prompt .= "IMPORTANT: The text contains HTML tags and/or WordPress Gutenberg block comments (e.g. <!-- wp:paragraph -->). ";
			$system_prompt .= "You MUST preserve all tags, structures, class names, image URLs, and Gutenberg block configuration annotations exactly as they are. ";
			$system_prompt .= "Only translate the actual visible text and paragraphs. ";
			$system_prompt .= "Translate the value inside 'alt' or 'title' attributes of images and links (e.g. alt='Text'), but do NOT translate the attributes names themselves, classes, or 'src'/'href' target URLs. ";
		} else {
			$system_prompt .= "Preserve any formatting tags if present. ";
		}
		
		$system_prompt .= "Return ONLY the translated text. Do not add any greeting, introductions, conversational fillers, notes, or explanations.";

		$response = wp_remote_post( 'https://api.groq.com/openai/v1/chat/completions', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( array(
				'model'       => 'llama-3.3-70b-versatile',
				'messages'    => array(
					array( 'role' => 'system', 'content' => $system_prompt ),
					array( 'role' => 'user', 'content' => $text )
				),
				'temperature' => 0.3
			) ),
			'timeout' => 45 // Posts longos podem exigir tempo limite maior
		) );

		if ( is_wp_error( $response ) ) {
			error_log( 'Vibe_Post_Translator Error: ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			$body = wp_remote_retrieve_body( $response );
			error_log( "Vibe_Post_Translator API Error (HTTP {$status_code}): " . $body );
			return false;
		}

		$body_obj = json_decode( wp_remote_retrieve_body( $response ) );
		if ( isset( $body_obj->choices[0]->message->content ) ) {
			return trim( $body_obj->choices[0]->message->content );
		}

		return false;
	}

	/**
	 * Handler AJAX para processar solicitações manuais de tradução do editor
	 */
	public static function handle_manual_translate_ajax() {
		// Validação de segurança básica e permissões
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Permissões insuficientes.' ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$lang    = isset( $_POST['lang'] ) ? sanitize_text_field( $_POST['lang'] ) : '';

		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => 'Post ID inválido.' ) );
		}

		$languages = array(
			'en' => 'English',
			'es' => 'Spanish',
			'fr' => 'French',
			'de' => 'German'
		);

		if ( ! empty( $lang ) ) {
			if ( ! isset( $languages[$lang] ) ) {
				wp_send_json_error( array( 'message' => 'Idioma não suportado.' ) );
			}
			
			$success = self::translate_post_for_lang( $post_id, $lang, $languages[$lang] );
			if ( $success ) {
				wp_send_json_success( array( 
					'message' => "Tradução para " . $languages[$lang] . " concluída com sucesso!",
					'status'  => 'success'
				) );
			} else {
				$err_msg = get_post_meta( $post_id, "_post_tr_error_{$lang}", true ) ?: 'Erro na requisição Groq.';
				wp_send_json_error( array( 'message' => $err_msg ) );
			}
		} else {
			// Se nenhum idioma foi passado, traduz todos
			self::translate_all_languages( $post_id );
			wp_send_json_success( array( 'message' => 'Tradução para todos os idiomas iniciada/concluída.' ) );
		}
	}
}
Vibe_Post_Translator::init();

