<?php
/**
 * Includes: Módulo de SEO Multilíngue (Single Post Architecture)
 *
 * Implementa rotas de tradução sem duplicar posts no banco de dados.
 *
 * @package vibe-animes
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Retorna o idioma ativo no momento (en, es, fr, de ou pt-BR) de forma precoce
 */
function vibe_multilingual_get_current_language() {
	static $lang = null;
	if ( $lang !== null ) {
		return $lang;
	}

	$lang = get_query_var( 'app_lang' );
	if ( ! empty( $lang ) ) {
		return $lang;
	}

	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
		$parts = explode( '/', $path );
		$first_part = isset( $parts[0] ) ? strtolower( $parts[0] ) : '';
		if ( in_array( $first_part, array( 'en', 'es', 'fr', 'de' ), true ) ) {
			$lang = $first_part;
			return $lang;
		}
	}

	$lang = 'pt-BR';
	return $lang;
}

/**
 * ---------------------------------------------------------------------
 * TASK T0.2: Regras de Roteamento (Rewrite Rules)
 * ---------------------------------------------------------------------
 */
function vibe_multilingual_rewrite_rules() {
	// Registra a query var 'app_lang' para podermos acessá-la no WordPress
	add_rewrite_tag( '%app_lang%', '([^&]+)' );

	$languages = 'en|es|fr|de';

	// Descobre IDs das páginas dinamicamente para garantir a portabilidade
	$front_page_id = (int) get_option( 'page_on_front' );
	if ( ! $front_page_id ) {
		$home_page = get_page_by_path( 'home' ) ?: get_page_by_path( 'pagina-inicial' );
		$front_page_id = $home_page ? $home_page->ID : 3946;
	}

	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( ! $posts_page_id ) {
		$pub_page = get_page_by_path( 'publicacoes' ) ?: get_page_by_path( 'noticias' );
		$posts_page_id = $pub_page ? $pub_page->ID : 3950;
	}

	// -----------------------------------------------------------------------
	// PASSO 1: Registra rotas GENÉRICAS primeiro (prioridade mais baixa).
	// Como o WordPress empilha regras 'top' (a última adicionada vai ao topo),
	// as regras específicas adicionadas no PASSO 2 ficarão acima destas.
	// -----------------------------------------------------------------------

	// Rota para a Homepage
	add_rewrite_rule( "^($languages)/?$", 'index.php?page_id=' . $front_page_id . '&app_lang=$matches[1]', 'top' );

	// Rotas genéricas de categoria, tag, gênero, busca, paginação e páginas avulsas
	add_rewrite_rule( "^($languages)/noticias/page/?([0-9]{1,})/?$", 'index.php?category_name=noticias&paged=$matches[2]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/noticias/?$", 'index.php?category_name=noticias&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $front_page_id . '&paged=$matches[2]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/category/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?category_name=$matches[2]&paged=$matches[3]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/category/([^/]+)/?$", 'index.php?category_name=$matches[2]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/tag/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?tag=$matches[2]&paged=$matches[3]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/tag/([^/]+)/?$", 'index.php?tag=$matches[2]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/genero/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?genero=$matches[2]&paged=$matches[3]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/genero/([^/]+)/?$", 'index.php?genero=$matches[2]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/([0-9]{4})/([0-9]{2})/([0-9]{2})/([^/]+)/?$", 'index.php?name=$matches[5]&app_lang=$matches[1]', 'top' );
	// Catch-all genérico de páginas (deve ser o último das genéricas)
	add_rewrite_rule( "^($languages)/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?pagename=$matches[2]&paged=$matches[3]&app_lang=$matches[1]', 'top' );
	add_rewrite_rule( "^($languages)/([^/]+)/?$", 'index.php?pagename=$matches[2]&app_lang=$matches[1]', 'top' );

	// -----------------------------------------------------------------------
	// PASSO 2: Registra rotas ESPECÍFICAS por idioma por último.
	// Por serem adicionadas depois, ficam no TOPO do stack e são testadas
	// ANTES das regras genéricas acima — evitando capturas incorretas.
	// -----------------------------------------------------------------------
	$languages_arr = array( 'en', 'es', 'fr', 'de' );
	foreach ( $languages_arr as $lang ) {
		$char_slug = 'personagem';
		$chars_slug = 'personagens';
		$dub_slug = 'dublador';
		$dubs_slug = 'dubladores';
		$cat_anime_slug = 'catalogo-de-animes';
		$cat_manga_slug = 'catalogo-de-mangas';
		$cat_manga_page = 'catalogo-manga';
		$animes_slug = 'animes';
		$mangas_slug = 'mangas';
		$review_slug = 'review';
		$reviews_slug = 'reviews';
		$analise_slug = 'analise';
		$analises_slug = 'analises';
		$ep_slug = 'episodio';
		$season_slug = 'temporada';
		$pub_slug = 'publicacoes';

		if ( 'en' === $lang ) {
			$char_slug = 'character';
			$chars_slug = 'characters';
			$dub_slug = 'voice-actor';
			$dubs_slug = 'voice-actors';
			$cat_anime_slug = 'anime-catalog';
			$cat_manga_slug = 'manga-catalog';
			$cat_manga_page = 'manga-catalog';
			$animes_slug = 'animes';
			$mangas_slug = 'mangas';
			$review_slug = 'review';
			$reviews_slug = 'reviews';
			$analise_slug = 'review';
			$analises_slug = 'reviews';
			$ep_slug = 'episode';
			$season_slug = 'season';
			$pub_slug = 'posts';
		} elseif ( 'es' === $lang ) {
			$char_slug = 'personaje';
			$chars_slug = 'personajes';
			$dub_slug = 'actor-de-voz';
			$dubs_slug = 'actores-de-voz';
			$cat_anime_slug = 'catalogo-de-animes';
			$cat_manga_slug = 'catalogo-de-mangas';
			$cat_manga_page = 'catalogo-manga';
			$animes_slug = 'animes';
			$mangas_slug = 'mangas';
			$review_slug = 'critica';
			$reviews_slug = 'criticas';
			$analise_slug = 'analisis';
			$analises_slug = 'analisis';
			$ep_slug = 'episodio';
			$season_slug = 'temporada';
			$pub_slug = 'publicaciones';
		} elseif ( 'fr' === $lang ) {
			$char_slug = 'personnage';
			$chars_slug = 'personnages';
			$dub_slug = 'acteur-de-doublage';
			$dubs_slug = 'acteurs-de-doublage';
			$cat_anime_slug = 'catalogue-d-animes';
			$cat_manga_slug = 'catalogue-de-mangas';
			$cat_manga_page = 'catalogue-manga';
			$animes_slug = 'animes';
			$mangas_slug = 'mangas';
			$review_slug = 'critique';
			$reviews_slug = 'critiques';
			$analise_slug = 'analyse';
			$analises_slug = 'analyses';
			$ep_slug = 'episode';
			$season_slug = 'saison';
			$pub_slug = 'publications';
		} elseif ( 'de' === $lang ) {
			$char_slug = 'charakter';
			$chars_slug = 'charaktere';
			$dub_slug = 'synchronsprecher';
			$dubs_slug = 'synchronsprecher';
			$cat_anime_slug = 'anime-katalog';
			$cat_manga_slug = 'manga-katalog';
			$cat_manga_page = 'manga-katalog';
			$animes_slug = 'animes';
			$mangas_slug = 'mangas';
			$review_slug = 'bewertung';
			$reviews_slug = 'bewertungen';
			$analise_slug = 'analyse';
			$analises_slug = 'analysen';
			$ep_slug = 'episode';
			$season_slug = 'staffel';
			$pub_slug = 'beitraege';
		}

		// Singles e hierárquicas
		add_rewrite_rule( "^{$lang}/{$cat_anime_slug}/([^/]+)/([^/]+)/?$", "index.php?anime_or_season=\$matches[2]&parent_anime_slug=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$cat_anime_slug}/([^/]+)/?$", "index.php?anime=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$cat_manga_slug}/([^/]+)/?$", "index.php?manga=\$matches[1]&app_lang={$lang}", 'top' );

		add_rewrite_rule( "^{$lang}/([^/]+)/{$dubs_slug}/([^/]+)/?$", "index.php?anime_slug=\$matches[1]&dublador=\$matches[2]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/([^/]+)/{$char_slug}/([^/]+)/?$", "index.php?anime_slug=\$matches[1]&personagem_slug=\$matches[2]&app_lang={$lang}", 'top' );

		add_rewrite_rule( "^{$lang}/{$reviews_slug}/([^/]+)/?$", "index.php?review=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$review_slug}/([^/]+)/?$", "index.php?review=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$analise_slug}/([^/]+)/?$", "index.php?review=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$dub_slug}/([^/]+)/?$", "index.php?dublador=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$ep_slug}/([^/]+)/?$", "index.php?episodio=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$season_slug}/([^/]+)/?$", "index.php?temporada=\$matches[1]&app_lang={$lang}", 'top' );

		// Catálogos: mapeiam para os slugs das páginas PT-BR no banco de dados
		add_rewrite_rule( "^{$lang}/{$cat_anime_slug}/page/?([0-9]{1,})/?$", "index.php?pagename=catalogo-de-animes&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$cat_anime_slug}/?$", "index.php?pagename=catalogo-de-animes&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$animes_slug}/page/?([0-9]{1,})/?$", "index.php?pagename=catalogo-de-animes&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$animes_slug}/?$", "index.php?pagename=catalogo-de-animes&app_lang={$lang}", 'top' );

		add_rewrite_rule( "^{$lang}/{$cat_manga_slug}/page/?([0-9]{1,})/?$", "index.php?pagename={$cat_manga_page}&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$cat_manga_slug}/?$", "index.php?pagename={$cat_manga_page}&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$cat_manga_page}/page/?([0-9]{1,})/?$", "index.php?pagename={$cat_manga_page}&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$cat_manga_page}/?$", "index.php?pagename={$cat_manga_page}&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$mangas_slug}/page/?([0-9]{1,})/?$", "index.php?pagename={$cat_manga_page}&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$mangas_slug}/?$", "index.php?pagename={$cat_manga_page}&app_lang={$lang}", 'top' );

		add_rewrite_rule( "^{$lang}/{$chars_slug}/page/?([0-9]{1,})/?$", "index.php?pagename=personagens&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$chars_slug}/?$", "index.php?pagename=personagens&app_lang={$lang}", 'top' );

		add_rewrite_rule( "^{$lang}/{$dubs_slug}/page/?([0-9]{1,})/?$", "index.php?pagename=dubladores&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$dubs_slug}/?$", "index.php?pagename=dubladores&app_lang={$lang}", 'top' );

		add_rewrite_rule( "^{$lang}/{$analises_slug}/page/?([0-9]{1,})/?$", "index.php?post_type=review&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$analises_slug}/?$", "index.php?post_type=review&app_lang={$lang}", 'top' );

		add_rewrite_rule( "^{$lang}/{$pub_slug}/page/?([0-9]{1,})/?$", "index.php?page_id=" . $posts_page_id . "&paged=\$matches[1]&app_lang={$lang}", 'top' );
		add_rewrite_rule( "^{$lang}/{$pub_slug}/?$", "index.php?page_id=" . $posts_page_id . "&app_lang={$lang}", 'top' );
	}
}
add_action( 'init', 'vibe_multilingual_rewrite_rules', 10 );

// Intercepta e corrige a query var de busca 's' para não sequestrar o roteamento multilíngue
add_filter( 'request', 'vibe_multilingual_fix_search_request' );
function vibe_multilingual_fix_search_request( $query_vars ) {
	if ( ( isset( $query_vars['app_lang'] ) || vibe_multilingual_get_current_language() !== 'pt-BR' ) && isset( $query_vars['s'] ) ) {
		unset( $query_vars['page_id'] );
		unset( $query_vars['pagename'] );
	}
	return $query_vars;
}

/**
 * ---------------------------------------------------------------------
 * TASK T0.3: Interceptadores SEO (RankMath/Yoast)
 * ---------------------------------------------------------------------
 */

// Filtro para RankMath (e Yoast similar) para consertar a URL Canonical
add_filter( 'rank_math/frontend/canonical', 'vibe_multilingual_canonical' );
add_filter( 'wpseo_canonical', 'vibe_multilingual_canonical' ); // Yoast fallback

function vibe_multilingual_canonical( $canonical ) {
	$lang = vibe_multilingual_get_current_language();
	if ( empty( $lang ) || $lang === 'pt-BR' ) {
		return $canonical;
	}

	$home_root = rtrim( get_option( 'home' ), '/' );
	
	if ( is_singular( array( 'anime', 'manga', 'dublador', 'personagem', 'post', 'review', 'temporada', 'page' ) ) ) {
		// Pega a URL nativa original e injeta o prefixo de idioma
		global $post;
		$base = str_replace( $home_root, '', get_permalink( $post->ID ) );
		$base = preg_replace( '#^/(en|es|fr|de)(/|$)#i', '/', $base );
		return $home_root . '/' . $lang . '/' . ltrim( $base, '/' );
	}

	// Para archives
	if ( is_post_type_archive( array( 'anime', 'manga', 'dublador', 'personagem' ) ) ) {
		$pt = get_query_var( 'post_type' );
		$base = str_replace( $home_root, '', get_post_type_archive_link( $pt ) );
		$base = preg_replace( '#^/(en|es|fr|de)(/|$)#i', '/', $base );
		return $home_root . '/' . $lang . '/' . ltrim( $base, '/' );
	}

	return $canonical;
}

// Filtro de Title (RankMath e Yoast)
add_filter( 'rank_math/frontend/title', 'vibe_multilingual_seo_title', 15 );
add_filter( 'wpseo_title', 'vibe_multilingual_seo_title', 15 );
function vibe_multilingual_seo_title( $title ) {
	$lang = vibe_multilingual_get_current_language();
	if ( empty( $lang ) || $lang === 'pt-BR' || empty( $title ) ) {
		return $title;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return $title;
	}

	// Se for post padrão, lê exclusivamente do metadado e nunca faz JIT/Groq no frontend
	if ( get_post_type( $post_id ) === 'post' ) {
		$translated = get_post_meta( $post_id, '_post_seo_title_' . $lang, true );
		if ( ! empty( $translated ) ) {
			return $translated;
		}
		// Fallback: se tiver o título normal traduzido, substitui no título do SEO
		$translated_title = get_post_meta( $post_id, '_post_title_' . $lang, true );
		if ( ! empty( $translated_title ) ) {
			$orig_title = get_the_title( $post_id );
			if ( ! empty( $orig_title ) && strpos( $title, $orig_title ) !== false ) {
				return str_replace( $orig_title, $translated_title, $title );
			}
		}
		return $title;
	}

	$cache_key = "vibe_tr_seo_title_" . $lang . "_" . $post_id;
	$cached = get_transient( $cache_key );
	if ( $cached !== false ) {
		return $cached;
	}

	$fail_cache_key = "vibe_tr_fail_seo_title_" . $lang . "_" . $post_id;
	if ( get_transient( $fail_cache_key ) !== false ) {
		return $title;
	}

	$translated = vibe_translate_with_ai( $title, $lang );
	if ( $translated !== false && ! empty( $translated ) ) {
		set_transient( $cache_key, $translated, 30 * DAY_IN_SECONDS );
		return $translated;
	}

	set_transient( $fail_cache_key, true, HOUR_IN_SECONDS );
	return $title;
}

// Filtro de Meta Description (RankMath e Yoast)
add_filter( 'rank_math/frontend/description', 'vibe_multilingual_seo_desc', 15 );
add_filter( 'wpseo_metadesc', 'vibe_multilingual_seo_desc', 15 );
function vibe_multilingual_seo_desc( $desc ) {
	$lang = vibe_multilingual_get_current_language();
	if ( empty( $lang ) || $lang === 'pt-BR' || empty( $desc ) ) {
		return $desc;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return $desc;
	}

	// Se for post padrão, lê exclusivamente do metadado e nunca faz JIT/Groq no frontend
	if ( get_post_type( $post_id ) === 'post' ) {
		$translated = get_post_meta( $post_id, '_post_seo_desc_' . $lang, true );
		if ( ! empty( $translated ) ) {
			return $translated;
		}
		return $desc;
	}

	$cache_key = "vibe_tr_seo_desc_" . $lang . "_" . $post_id;
	$cached = get_transient( $cache_key );
	if ( $cached !== false ) {
		return $cached;
	}

	$fail_cache_key = "vibe_tr_fail_seo_desc_" . $lang . "_" . $post_id;
	if ( get_transient( $fail_cache_key ) !== false ) {
		return $desc;
	}

	$translated = vibe_translate_with_ai( $desc, $lang );
	if ( $translated !== false && ! empty( $translated ) ) {
		set_transient( $cache_key, $translated, 30 * DAY_IN_SECONDS );
		return $translated;
	}

	set_transient( $fail_cache_key, true, HOUR_IN_SECONDS );
	return $desc;
}

/**
 * ---------------------------------------------------------------------
 * TASK T0.4: Injeção de tags hreflang Dinâmicas
 * ---------------------------------------------------------------------
 */
function vibe_inject_hreflang_tags() {
	if ( is_singular( array( 'anime', 'manga', 'dublador', 'personagem', 'post', 'review', 'temporada', 'page' ) ) ) {
		global $post;
		$home_root = rtrim( get_option( 'home' ), '/' );
		$base = str_replace( $home_root, '', get_permalink( $post->ID ) );
		$base = preg_replace( '#^/(en|es|fr|de)(/|$)#i', '/', $base );
		$base_path = '/' . ltrim( $base, '/' );
		
		echo '<!-- Vibe Animes Multilingual Hreflang -->' . "\n";
		echo '<link rel="alternate" hreflang="pt-BR" href="'. esc_url( $home_root . $base_path ) .'" />' . "\n";
		echo '<link rel="alternate" hreflang="en" href="'. esc_url( $home_root . "/en" . $base_path ) .'" />' . "\n";
		echo '<link rel="alternate" hreflang="es" href="'. esc_url( $home_root . "/es" . $base_path ) .'" />' . "\n";
		echo '<link rel="alternate" hreflang="fr" href="'. esc_url( $home_root . "/fr" . $base_path ) .'" />' . "\n";
		echo '<link rel="alternate" hreflang="de" href="'. esc_url( $home_root . "/de" . $base_path ) .'" />' . "\n";
		echo '<link rel="alternate" hreflang="x-default" href="'. esc_url( $home_root . "/en" . $base_path ) .'" />' . "\n";
	}
}
add_action( 'wp_head', 'vibe_inject_hreflang_tags', 1 );

/**
 * ---------------------------------------------------------------------
 * TASK T1.2: Interceptador de Campos Multilíngues (ACF) com JIT AI
 * ---------------------------------------------------------------------
 */

function vibe_translate_with_ai($text, $target_lang) {
    if (empty($text)) return $text;
    
    // Suporte para Groq API via constante no wp-config.php: define('GROQ_API_KEY', 'sua-chave');
    $api_key = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
    
    if (empty($api_key)) {
        return false; // Retorna false se não tiver chave de API configurada
    }

    $language_map = array(
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German'
    );
    
    $target_name = isset($language_map[$target_lang]) ? $language_map[$target_lang] : 'English';

    $body = json_encode(array(
        'model' => 'llama-3.3-70b-versatile',
        'messages' => array(
            array(
                'role' => 'system',
                'content' => "You are a professional translator. Translate the following text from Portuguese to $target_name. Preserve all HTML formatting. Output ONLY the translated text, without any conversational filler."
            ),
            array(
                'role' => 'user',
                'content' => $text
            )
        ),
        'temperature' => 0.3
    ));

    $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json'
        ),
        'body'    => $body,
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        return false;
    }

    $body_obj = json_decode(wp_remote_retrieve_body($response));
    if (isset($body_obj->choices[0]->message->content)) {
        return trim($body_obj->choices[0]->message->content);
    }

    return false;
}

add_filter('acf/load_value/name=anime_sinopse', 'vibe_multilingual_acf_interceptor', 10, 3);
add_filter('acf/load_value/name=anime_sinopse_manual', 'vibe_multilingual_acf_interceptor', 10, 3);
add_filter('acf/load_value/name=manga_sinopse_manual', 'vibe_multilingual_acf_interceptor', 10, 3);
add_filter('acf/load_value/name=review_veredicto', 'vibe_multilingual_acf_interceptor', 10, 3);
add_filter('the_content', 'vibe_multilingual_content_interceptor', 20);
add_filter('the_title', 'vibe_multilingual_title_interceptor', 10, 2);
add_filter('the_excerpt', 'vibe_multilingual_content_interceptor', 20);
add_filter('get_the_excerpt', 'vibe_multilingual_excerpt_interceptor', 20, 2);

function vibe_multilingual_acf_interceptor($value, $post_id, $field) {
    $lang = vibe_multilingual_get_current_language();
    if (empty($lang) || $lang === 'pt-BR' || empty($value)) return $value;

    $cache_key = "vibe_tr_" . $field['name'] . "_" . $lang . "_" . $post_id;
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }

    $fail_cache_key = "vibe_tr_fail_" . $field['name'] . "_" . $lang . "_" . $post_id;
    if (get_transient($fail_cache_key) !== false) {
        return $value;
    }

    // Se não tiver cache, tenta o campo nativo do ACF (ex: anime_sinopse_en)
    $translated_acf = get_field($field['name'] . '_' . $lang, $post_id);
    if (!empty($translated_acf)) {
        set_transient($cache_key, $translated_acf, 30 * DAY_IN_SECONDS);
        return $translated_acf;
    }

    // Se não existir, gera via IA em tempo real (JIT)
    $translated_ai = vibe_translate_with_ai($value, $lang);
    
    if ($translated_ai !== false && !empty($translated_ai)) {
        // Salva no banco nativo para a próxima e no cache
        update_field($field['name'] . '_' . $lang, $translated_ai, $post_id);
        set_transient($cache_key, $translated_ai, 30 * DAY_IN_SECONDS);
        return $translated_ai;
    }

    // Registra falha de tradução temporária por 1 hora
    set_transient($fail_cache_key, true, HOUR_IN_SECONDS);
    return $value;
}

function vibe_multilingual_content_interceptor($content) {
    if (!in_the_loop() || !is_main_query()) return $content;
    
    $lang = vibe_multilingual_get_current_language();
    if (empty($lang) || $lang === 'pt-BR' || empty($content)) return $content;

    global $post;
    if (!$post) return $content;

    // Se for post padrão (notícia), lê exclusivamente do banco e nunca faz JIT/Groq no frontend
    if ($post->post_type === 'post') {
        $translated = get_post_meta($post->ID, '_post_content_' . $lang, true);
        if (!empty($translated)) {
            return $translated;
        }
        return $content; // fallback original
    }

    $cache_key = "vibe_tr_content_" . $lang . "_" . $post->ID;
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $fail_cache_key = "vibe_tr_fail_content_" . $lang . "_" . $post->ID;
    if (get_transient($fail_cache_key) !== false) {
        return $content;
    }

    $translated_ai = vibe_translate_with_ai($content, $lang);
    if ($translated_ai !== false && !empty($translated_ai)) {
        set_transient($cache_key, $translated_ai, 30 * DAY_IN_SECONDS);
        return $translated_ai;
    }

    set_transient($fail_cache_key, true, HOUR_IN_SECONDS);
    return $content;
}

function vibe_multilingual_title_interceptor($title, $post_id = null) {
    if (empty($post_id)) return $title;
    if (is_admin()) return $title;
    
    // Não intercepta menus, apenas posts no loop principal, CPTs singulares, arquivos ou buscas
    if (!in_the_loop() && !is_singular() && !is_archive() && !is_search()) return $title;
    
    $lang = vibe_multilingual_get_current_language();
    if (empty($lang) || $lang === 'pt-BR' || empty($title)) return $title;

    // Primeiro, tenta traduzir com o dicionário de UI estático
    $dict_trans = vibe_multilingual_get_dict_translation($title, $lang);
    if ($dict_trans) {
        return $dict_trans;
    }

    // Se for um post padrão, lê exclusivamente do metadado e nunca faz JIT/Groq
    $post_type = get_post_type($post_id);
    if ($post_type === 'post') {
        $translated = get_post_meta($post_id, '_post_title_' . $lang, true);
        if (!empty($translated)) {
            return $translated;
        }
        return $title;
    }

    $cache_key = "vibe_tr_title_" . $lang . "_" . $post_id;
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $fail_cache_key = "vibe_tr_fail_title_" . $lang . "_" . $post_id;
    if (get_transient($fail_cache_key) !== false) {
        return $title;
    }

    $translated_ai = vibe_translate_with_ai($title, $lang);
    if ($translated_ai !== false && !empty($translated_ai)) {
        set_transient($cache_key, $translated_ai, 30 * DAY_IN_SECONDS);
        return $translated_ai;
    }

    set_transient($fail_cache_key, true, HOUR_IN_SECONDS);
    return $title;
}

function vibe_multilingual_excerpt_interceptor($excerpt, $post = null) {
    if (empty($excerpt)) return $excerpt;
    
    $lang = vibe_multilingual_get_current_language();
    if (empty($lang) || $lang === 'pt-BR') return $excerpt;

    $post_id = $post ? $post->ID : get_the_ID();
    if (!$post_id) return $excerpt;

    // Se for um post padrão, lê exclusivamente do metadado e nunca faz JIT/Groq
    $post_type = get_post_type($post_id);
    if ($post_type === 'post') {
        $translated = get_post_meta($post_id, '_post_excerpt_' . $lang, true);
        if (!empty($translated)) {
            return $translated;
        }
        return $excerpt;
    }

    $cache_key = "vibe_tr_excerpt_" . $lang . "_" . $post_id;
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $fail_cache_key = "vibe_tr_fail_excerpt_" . $lang . "_" . $post_id;
    if (get_transient($fail_cache_key) !== false) {
        return $excerpt;
    }

    $translated_ai = vibe_translate_with_ai($excerpt, $lang);
    if ($translated_ai !== false && !empty($translated_ai)) {
        set_transient($cache_key, $translated_ai, 30 * DAY_IN_SECONDS);
        return $translated_ai;
    }

    set_transient($fail_cache_key, true, HOUR_IN_SECONDS);
    return $excerpt;
}

// Filtra e traduz taxonomias globais (como categorias e gêneros) no carregamento de termos
add_filter( 'get_term', 'vibe_multilingual_translate_term_object', 15, 2 );
function vibe_multilingual_translate_term_object( $_term, $taxonomy ) {
	if ( ! $_term || is_wp_error( $_term ) ) {
		return $_term;
	}

	$lang = vibe_multilingual_get_current_language();
	if ( empty( $lang ) || $lang === 'pt-BR' ) {
		return $_term;
	}

	$_term->name = vibe_multilingual_translate_dynamic_string( $_term->name, 'term' );

	return $_term;
}

/**
 * Traduz uma string dinâmica usando o dicionário estático ou JIT AI Translation do Groq com cache.
 */
function vibe_multilingual_translate_dynamic_string( $text, $cache_group = 'term' ) {
    if ( empty( $text ) ) {
        return $text;
    }
    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' ) {
        return $text;
    }

    // 1. Dicionário estático de UI
    $dict_trans = vibe_multilingual_get_dict_translation( $text, $lang );
    if ( $dict_trans ) {
        return $dict_trans;
    }

    // 2. Cache temporário (Transient)
    $cache_key = "vibe_tr_" . $cache_group . "_" . $lang . "_" . md5( $text );
    $cached = get_transient( $cache_key );
    if ( $cached !== false ) {
        return $cached;
    }

    $fail_cache_key = "vibe_tr_fail_" . $cache_group . "_" . $lang . "_" . md5( $text );
    if ( get_transient( $fail_cache_key ) !== false ) {
        return $text;
    }

    // 3. Tradução via Groq AI
    $translated_ai = vibe_translate_with_ai( $text, $lang );
    if ( $translated_ai !== false && ! empty( $translated_ai ) ) {
        set_transient( $cache_key, $translated_ai, 30 * DAY_IN_SECONDS );
        return $translated_ai;
    } else {
        set_transient( $fail_cache_key, true, HOUR_IN_SECONDS );
        return $text;
    }
}

// Interceptadores de Breadcrumbs (Yoast SEO e RankMath)
add_filter( 'wpseo_breadcrumb_links', 'vibe_multilingual_translate_yoast_breadcrumbs', 15 );
function vibe_multilingual_translate_yoast_breadcrumbs( $links ) {
    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' || ! is_array( $links ) ) {
        return $links;
    }
    
    foreach ( $links as &$link ) {
        if ( isset( $link['text'] ) && ! empty( $link['text'] ) ) {
            // Se for link para a Home, traduz para 'Home' estático
            if ( isset( $link['url'] ) && ( $link['url'] === home_url( '/' ) || $link['url'] === home_url() ) ) {
                $link['text'] = vibe_multilingual_get_dict_translation( 'Home', $lang ) ?: $link['text'];
                continue;
            }
            $link['text'] = vibe_multilingual_translate_dynamic_string( $link['text'], 'crumb' );
        }
        
        // Corrige URLs internas do breadcrumb para manter o idioma prefixado
        if ( isset( $link['url'] ) && ! empty( $link['url'] ) ) {
            $home_root = rtrim( get_option( 'home' ), '/' );
            if ( strpos( $link['url'], $home_root ) === 0 ) {
                $base_path = str_replace( $home_root, '', $link['url'] );
                if ( ! preg_match( '#^/(en|es|fr|de)(/|\?|$)#i', $base_path ) ) {
                    $link['url'] = $home_root . '/' . $lang . $base_path;
                }
            }
        }
    }
    return $links;
}

add_filter( 'rank_math/frontend/breadcrumb/items', 'vibe_multilingual_translate_rankmath_breadcrumbs', 15, 2 );
function vibe_multilingual_translate_rankmath_breadcrumbs( $crumbs, $class ) {
    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' || ! is_array( $crumbs ) ) {
        return $crumbs;
    }

    foreach ( $crumbs as &$crumb ) {
        if ( isset( $crumb[0] ) && ! empty( $crumb[0] ) ) {
            $label = $crumb[0];
            $url   = isset( $crumb[1] ) ? $crumb[1] : '';

            // Se for link para a Home, traduz para 'Home' estático
            if ( ! empty( $url ) ) {
                $home_root = rtrim( get_option( 'home' ), '/' );
                if ( $url === home_url( '/' ) || $url === home_url() || $url === $home_root ) {
                    $crumb[0] = vibe_multilingual_get_dict_translation( 'Home', $lang ) ?: $label;
                    $crumb[1] = $home_root . '/' . $lang . '/';
                    continue;
                }
            }

            $crumb[0] = vibe_multilingual_translate_dynamic_string( $label, 'crumb' );

            // Corrige URLs internas do breadcrumb para manter o idioma prefixado
            if ( ! empty( $url ) ) {
                $home_root = rtrim( get_option( 'home' ), '/' );
                if ( strpos( $url, $home_root ) === 0 ) {
                    $base_path = str_replace( $home_root, '', $url );
                    if ( ! preg_match( '#^/(en|es|fr|de)(/|\?|$)#i', $base_path ) ) {
                        $crumb[1] = $home_root . '/' . $lang . $base_path;
                    }
                }
            }
        }
    }
    return $crumbs;
}

/**
 * ---------------------------------------------------------------------
 * TASK T1.3: Preservar idioma durante a navegação
 * ---------------------------------------------------------------------
 * Intercepta home_url e outros links do tema para injetar o prefixo de idioma atual
 */
add_filter( 'post_type_archive_link', 'vibe_multilingual_archive_link', 10, 2 );
function vibe_multilingual_archive_link( $link, $post_type ) {
    $lang = vibe_multilingual_get_current_language();
    if ( $lang && $lang !== 'pt-BR' && in_array( $post_type, get_post_types( array( 'public' => true ) ), true ) ) {
        $home_root = rtrim( get_option( 'home' ), '/' );
        
        $base = str_replace( $home_root, '', $link );
        if ( preg_match( '#^/(en|es|fr|de)(/|\?|$)#i', $base ) ) {
            return $link;
        }

        return $home_root . "/{$lang}{$base}";
    }
    return $link;
}

add_filter( 'post_link', 'vibe_multilingual_post_link', 10, 3 );
add_filter( 'post_type_link', 'vibe_multilingual_post_link', 10, 3 );
add_filter( 'page_link', 'vibe_multilingual_post_link', 10, 3 );
function vibe_multilingual_post_link( $permalink, $post, $leavename = false ) {
    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' ) return $permalink;

    if ( is_numeric( $post ) ) {
        $post = get_post( $post );
    }
    
    if ( $post instanceof WP_Post && in_array( $post->post_type, get_post_types( array( 'public' => true ) ), true ) ) {
        $home_root = rtrim( get_option( 'home' ), '/' );
        
        $base = str_replace( $home_root, '', $permalink );
        $base_clean = preg_replace( '#^/(en|es|fr|de)(/|\?|$)#i', '/', $base );
        $translated_path = vibe_multilingual_translate_path_segments( $base_clean, $lang );
        $permalink = $home_root . "/{$lang}/" . ltrim( $translated_path, '/' );

        // Localize standard CPT URL slugs based on language
        if ( 'temporada' === $post->post_type ) {
            $season_slug = 'temporada';
            if ( 'en' === $lang ) $season_slug = 'season';
            elseif ( 'fr' === $lang ) $season_slug = 'saison';
            elseif ( 'de' === $lang ) $season_slug = 'staffel';
            
            $permalink = str_replace( "/temporada/{$post->post_name}/", "/{$season_slug}/{$post->post_name}/", $permalink );
        } elseif ( 'episodio' === $post->post_type ) {
            $ep_slug = 'episodio';
            if ( 'en' === $lang || 'fr' === $lang || 'de' === $lang ) $ep_slug = 'episode';
            
            $permalink = str_replace( "/episodio/{$post->post_name}/", "/{$ep_slug}/{$post->post_name}/", $permalink );
        } elseif ( 'review' === $post->post_type ) {
            $rev_slug = 'review';
            if ( 'es' === $lang ) $rev_slug = 'critica';
            elseif ( 'fr' === $lang ) $rev_slug = 'critique';
            elseif ( 'de' === $lang ) $rev_slug = 'bewertung';
            
            $permalink = str_replace( "/review/{$post->post_name}/", "/{$rev_slug}/{$post->post_name}/", $permalink );
        }

        return $permalink;
    }
    return $permalink;
}

add_filter( 'term_link', 'vibe_multilingual_term_link', 10, 3 );
add_filter( 'category_link', 'vibe_multilingual_term_link', 10, 3 );
add_filter( 'tag_link', 'vibe_multilingual_term_link', 10, 3 );
function vibe_multilingual_term_link( $termlink, $term, $taxonomy = '' ) {
    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' ) return $termlink;
    
    $home_root = rtrim( get_option( 'home' ), '/' );
    
    $base = str_replace( $home_root, '', $termlink );
    if ( preg_match( '#^/(en|es|fr|de)(/|\?|$)#i', $base ) ) {
        return $termlink;
    }

    return $home_root . "/{$lang}{$base}";
}

// Filtro especial para buscas para manter a variável de linguagem
add_filter( 'get_search_form', 'vibe_multilingual_search_form' );
function vibe_multilingual_search_form( $form ) {
    $lang = vibe_multilingual_get_current_language();
    if ( $lang && $lang !== 'pt-BR' ) {
        $form = str_replace( '</form>', '<input type="hidden" name="app_lang" value="' . esc_attr($lang) . '" /></form>', $form );
    }
    return $form;
}

/**
 * ---------------------------------------------------------------------
 * TASK T3.2: Sitemaps Dinâmicos para Idiomas (/sitemap-{lang}.xml)
 * ---------------------------------------------------------------------
 * Gera um XML enxuto contendo os links traduzidos para acelerar a indexação.
 */
function vibe_multilingual_sitemap_rewrite() {
    add_rewrite_rule('^sitemap-lang-([a-z]{2})\.xml$', 'index.php?vibe_sitemap_lang=$matches[1]', 'top');
}
add_action('init', 'vibe_multilingual_sitemap_rewrite');

function vibe_multilingual_sitemap_query_vars($vars) {
    $vars[] = 'vibe_sitemap_lang';
    return $vars;
}
add_filter('query_vars', 'vibe_multilingual_sitemap_query_vars');

function vibe_multilingual_sitemap_render() {
    $lang = get_query_var('vibe_sitemap_lang');
    if (!$lang) return;

    // Apenas idiomas permitidos
    if (!in_array($lang, array('en', 'es', 'fr', 'de'))) {
        status_header(404);
        exit;
    }

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Busca animes (limite de 10.000 por sitemap por questões de performance)
    $query = new WP_Query(array(
        'post_type' => 'anime',
        'post_status' => 'publish',
        'posts_per_page' => 10000,
        'fields' => 'ids',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));

    $home_root = rtrim( get_option( 'home' ), '/' );

    foreach ($query->posts as $post_id) {
        $base_url = str_replace($home_root, '', get_permalink($post_id));
        $base_url = preg_replace( '#^/(en|es|fr|de)(/|$)#i', '/', $base_url );
        $translated_path = vibe_multilingual_translate_path_segments( $base_url, $lang );
        $lang_url = $home_root . '/' . $lang . '/' . ltrim($translated_path, '/');
        
        echo "  <url>\n";
        echo "    <loc>" . esc_url($lang_url) . "</loc>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
    }

    echo '</urlset>';
    exit;
}
add_action('template_redirect', 'vibe_multilingual_sitemap_render', 0);

/**
 * Altera o Locale global do WordPress para traduzir strings estáticas do tema (via .po/.mo)
 */
add_filter('locale', 'vibe_multilingual_switch_locale');
function vibe_multilingual_switch_locale($locale) {
    $lang = vibe_multilingual_get_current_language();
    if ($lang === 'en') return 'en_US';
    if ($lang === 'es') return 'es_ES';
    if ($lang === 'fr') return 'fr_FR';
    if ($lang === 'de') return 'de_DE';
    return $locale;
}

function vibe_multilingual_get_dict_translation( $text, $lang ) {
    static $ui_dictionary = null;
    if ( $ui_dictionary === null ) {
        $dict_path = __DIR__ . '/ui-dictionary.php';
        if ( file_exists( $dict_path ) ) {
            $ui_dictionary = include $dict_path;
        } else {
            $ui_dictionary = array();
        }
    }
    return isset( $ui_dictionary[$text] ) && isset( $ui_dictionary[$text][$lang] ) ? $ui_dictionary[$text][$lang] : null;
}

/**
 * ---------------------------------------------------------------------
 * TASK UI: Dicionário Global de Interface (Gettext Interceptor)
 * ---------------------------------------------------------------------
 * Intercepta as funções __() e _e() do WordPress em tempo real para os 4 idiomas
 */
add_filter('gettext', 'vibe_multilingual_translate_ui_complete', 15, 3);
function vibe_multilingual_translate_ui_complete($translated_text, $text, $domain) {
    if ($domain !== 'vibe-animes' && $domain !== 'geek-ao-cubo') return $translated_text;
    
    $lang = vibe_multilingual_get_current_language();
    if (empty($lang) || $lang === 'pt-BR') return $translated_text;

    $translated = vibe_multilingual_get_dict_translation($text, $lang);
    return $translated ? $translated : $translated_text;
}

add_filter('gettext_with_context', 'vibe_multilingual_translate_ui_context', 15, 4);
function vibe_multilingual_translate_ui_context($translated_text, $text, $context, $domain) {
    if ($domain !== 'vibe-animes' && $domain !== 'geek-ao-cubo') return $translated_text;
    
    $lang = vibe_multilingual_get_current_language();
    if (empty($lang) || $lang === 'pt-BR') return $translated_text;

    $translated = vibe_multilingual_get_dict_translation($text, $lang);
    return $translated ? $translated : $translated_text;
}

add_filter('nav_menu_item_title', 'vibe_multilingual_translate_menus', 15, 4);
function vibe_multilingual_translate_menus($title, $item, $args, $depth) {
    $lang = vibe_multilingual_get_current_language();
    if (empty($lang) || $lang === 'pt-BR') return $title;

    $translated = vibe_multilingual_get_dict_translation($title, $lang);
    return $translated ? $translated : $title;
}

add_filter('term_name', 'vibe_multilingual_translate_terms', 15, 2);
function vibe_multilingual_translate_terms($name, $term) {
    $lang = vibe_multilingual_get_current_language();
    if (empty($lang) || $lang === 'pt-BR') return $name;

    $translated = vibe_multilingual_get_dict_translation($name, $lang);
    return $translated ? $translated : $name;
}

add_filter( 'ngettext', 'vibe_multilingual_translate_ui_ngettext', 15, 5 );
function vibe_multilingual_translate_ui_ngettext( $translation, $single, $plural, $number, $domain ) {
    if ( $domain !== 'vibe-animes' && $domain !== 'geek-ao-cubo' ) {
        return $translation;
    }
    
    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' ) {
        return $translation;
    }

    $key = ( $number === 1 ) ? $single : $plural;
    $translated = vibe_multilingual_get_dict_translation( $key, $lang );
    return $translated ? $translated : $translation;
}

add_filter( 'get_the_archive_title', 'vibe_multilingual_translate_archive_title', 15 );
function vibe_multilingual_translate_archive_title( $title ) {
    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' ) {
        return $title;
    }

    $replacements = array(
        'en' => array(
            'Categoria: ' => 'Category: ',
            'Tag: '       => 'Tag: ',
            'Arquivos: '  => 'Archives: ',
            'Gênero: '    => 'Genre: ',
            'Animes'      => 'Animes',
            'Mangás'      => 'Mangas',
            'Personagens' => 'Characters',
            'Dubladores'  => 'Voice Actors',
            'Reviews'     => 'Reviews',
            'Temporadas'  => 'Seasons',
            'Episódios'   => 'Episodes',
        ),
        'es' => array(
            'Categoria: ' => 'Categoría: ',
            'Tag: '       => 'Etiqueta: ',
            'Arquivos: '  => 'Archivos: ',
            'Gênero: '    => 'Género: ',
            'Animes'      => 'Animes',
            'Mangás'      => 'Mangas',
            'Personagens' => 'Personajes',
            'Dubladores'  => 'Actores de Voz',
            'Reviews'     => 'Reseñas',
            'Temporadas'  => 'Temporadas',
            'Episódios'   => 'Episodios',
        ),
        'fr' => array(
            'Categoria: ' => 'Catégorie : ',
            'Tag: '       => 'Étiquette : ',
            'Arquivos: '  => 'Archives : ',
            'Gênero: '    => 'Genre : ',
            'Animes'      => 'Animes',
            'Mangás'      => 'Mangas',
            'Personagens' => 'Personnages',
            'Dubladores'  => 'Acteurs de Doublage',
            'Reviews'     => 'Critiques',
            'Temporadas'  => 'Saisons',
            'Episódios'   => 'Épisodes',
        ),
        'de' => array(
            'Categoria: ' => 'Kategorie: ',
            'Tag: '       => 'Schlagwort: ',
            'Arquivos: '  => 'Archive: ',
            'Gênero: '    => 'Genre: ',
            'Animes'      => 'Animes',
            'Mangás'      => 'Mangas',
            'Personagens' => 'Charaktere',
            'Dubladores'  => 'Synchronsprecher',
            'Reviews'     => 'Rezensionen',
            'Temporadas'  => 'Staffeln',
            'Episódios'   => 'Episoden',
        )
    );

    $lang_rep = isset( $replacements[$lang] ) ? $replacements[$lang] : array();
    foreach ( $lang_rep as $pt => $trans ) {
        if ( strpos( $title, $pt ) !== false ) {
            $title = str_replace( $pt, $trans, $title );
        }
    }
    
    $dict_trans = vibe_multilingual_get_dict_translation( $title, $lang );
    if ( $dict_trans ) {
        return $dict_trans;
    }

    return $title;
}

/**
 * ---------------------------------------------------------------------
 * PAGINACAO MULTILINGUE: Injeta prefixo de idioma nos links de paginate_links()
 * ---------------------------------------------------------------------
 */
/**
 * Helper to prefix a single URL with the active language if not already prefixed.
 */
function vibe_multilingual_prefix_single_url( $url, $lang, $home ) {
    if ( strpos( $url, $home ) !== 0 ) {
        return $url;
    }
    $base = str_replace( $home, '', $url );
    // Check if it already has any language prefix
    if ( preg_match( '#^/(en|es|fr|de)(/|\?|$)#i', $base ) ) {
        return $url;
    }
    // Check if it's an asset or system path
    $clean_path = ltrim( $base, '/' );
    if ( ! empty( $clean_path ) && preg_match( '#^(wp-content|wp-admin|wp-includes|wp-json|\.php)#i', $clean_path ) ) {
        return $url;
    }
    
    // Inject prefix
    if ( empty( $clean_path ) ) {
        return $home . '/' . $lang . '/';
    }
    
    return $home . '/' . $lang . '/' . $clean_path;
}

add_filter( 'paginate_links', 'vibe_multilingual_fix_paginate_links' );
function vibe_multilingual_fix_paginate_links( $links ) {
    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' ) {
        return $links;
    }

    $home = rtrim( get_option( 'home' ), '/' );

    if ( is_array( $links ) ) {
        foreach ( $links as $key => $link ) {
            $links[$key] = preg_replace_callback(
                '#href=(["\'])([^"\']+)\1#i',
                function( $matches ) use ( $lang, $home ) {
                    return 'href=' . $matches[1] . vibe_multilingual_prefix_single_url( $matches[2], $lang, $home ) . $matches[1];
                },
                $link
            );
        }
    } else {
        $links = preg_replace_callback(
            '#href=(["\'])([^"\']+)\1#i',
            function( $matches ) use ( $lang, $home ) {
                return 'href=' . $matches[1] . vibe_multilingual_prefix_single_url( $matches[2], $lang, $home ) . $matches[1];
            },
            $links
        );
    }

    return $links;
}

/**
 * ---------------------------------------------------------------------
 * HELPER: Retorna o home_url com prefixo de idioma se ativo
 * ---------------------------------------------------------------------
 */
function vibe_lang_url( $path = '/' ) {
    $lang = vibe_multilingual_get_current_language();
    $home = rtrim( get_option( 'home' ), '/' );
    if ( ! empty( $lang ) && $lang !== 'pt-BR' ) {
        return $home . '/' . $lang . '/' . ltrim( $path, '/' );
    }
    return $home . '/' . ltrim( $path, '/' );
}

function vibe_multilingual_translate_path_segments( $path, $lang ) {
    $clean_path = ltrim( (string)$path, '/' );
    if ( empty( $clean_path ) ) {
        return '';
    }

    if ( strpos( $clean_path, 'catalogo-de-animes' ) === 0 ) {
        $cat_anime = 'catalogo-de-animes';
        if ( 'en' === $lang ) $cat_anime = 'anime-catalog';
        elseif ( 'fr' === $lang ) $cat_anime = 'catalogue-d-animes';
        elseif ( 'de' === $lang ) $cat_anime = 'anime-katalog';
        $clean_path = preg_replace( '#^catalogo-de-animes#', $cat_anime, $clean_path );
    } elseif ( strpos( $clean_path, 'catalogo-de-mangas' ) === 0 ) {
        $cat_manga = 'catalogo-de-mangas';
        if ( 'en' === $lang ) $cat_manga = 'manga-catalog';
        elseif ( 'de' === $lang ) $cat_manga = 'manga-katalog';
        $clean_path = preg_replace( '#^catalogo-de-mangas#', $cat_manga, $clean_path );
    } elseif ( strpos( $clean_path, 'catalogo-manga' ) === 0 ) {
        $cat_manga_page = 'catalogo-manga';
        if ( 'en' === $lang ) $cat_manga_page = 'manga-catalog';
        elseif ( 'de' === $lang ) $cat_manga_page = 'manga-katalog';
        $clean_path = preg_replace( '#^catalogo-manga#', $cat_manga_page, $clean_path );
    } elseif ( strpos( $clean_path, 'dubladores' ) === 0 ) {
        $dubs = 'dubladores';
        if ( 'en' === $lang ) $dubs = 'voice-actors';
        elseif ( 'es' === $lang ) $dubs = 'actores-de-voz';
        elseif ( 'fr' === $lang ) $dubs = 'acteurs-de-doublage';
        elseif ( 'de' === $lang ) $dubs = 'synchronsprecher';
        $clean_path = preg_replace( '#^dubladores#', $dubs, $clean_path );
    } elseif ( strpos( $clean_path, 'personagens' ) === 0 ) {
        $chars = 'personagens';
        if ( 'en' === $lang ) $chars = 'characters';
        elseif ( 'es' === $lang ) $chars = 'personajes';
        elseif ( 'fr' === $lang ) $chars = 'personnages';
        elseif ( 'de' === $lang ) $chars = 'charaktere';
        $clean_path = preg_replace( '#^personagens#', $chars, $clean_path );
    } elseif ( strpos( $clean_path, 'publicacoes' ) === 0 ) {
        $pubs = 'publicacoes';
        if ( 'en' === $lang ) $pubs = 'posts';
        elseif ( 'es' === $lang ) $pubs = 'publicaciones';
        elseif ( 'fr' === $lang ) $pubs = 'publications';
        elseif ( 'de' === $lang ) $pubs = 'beitraege';
        $clean_path = preg_replace( '#^publicacoes#', $pubs, $clean_path );
    } elseif ( strpos( $clean_path, 'analises' ) === 0 ) {
        $anals = 'analises';
        if ( 'en' === $lang ) $anals = 'reviews';
        elseif ( 'es' === $lang ) $anals = 'analisis';
        elseif ( 'fr' === $lang ) $anals = 'analyses';
        elseif ( 'de' === $lang ) $anals = 'analysen';
        $clean_path = preg_replace( '#^analises#', $anals, $clean_path );
    }

    return $clean_path;
}

/**
 * Corrige os links internos do header e menus quando em idioma alternativo.
 * Intercepta home_url para que links do tipo home_url('/catalogo-de-animes/')
 * se tornem /en/catalogo-de-animes/ automaticamente.
 */
add_filter( 'home_url', 'vibe_multilingual_fix_home_url', 10, 4 );
function vibe_multilingual_fix_home_url( $url, $path, $orig_scheme, $blog_id ) {
    if ( is_admin() ) {
        return $url;
    }

    $clean_path = ltrim( (string)$path, '/' );

    // Ignore asset folders, API endpoints and system files
    if ( ! empty( $clean_path ) && preg_match( '#^(wp-content|wp-admin|wp-includes|wp-json|\.php)#i', $clean_path ) ) {
        return $url;
    }

    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' ) {
        return $url;
    }

    $home = rtrim( get_option( 'home' ), '/' );
    $prefix = $home . '/' . $lang;

    // Safeguard: Check if the URL already has any language prefix
    $base = str_replace( $home, '', $url );
    if ( preg_match( '#^/(en|es|fr|de)(/|\?|$)#i', $base ) ) {
        return $url;
    }

    // If path is empty, we return prefixed homepage URL directly without calling home_url to avoid recursion
    if ( empty( $clean_path ) ) {
        return $prefix . '/';
    }

    $translated_path = vibe_multilingual_translate_path_segments( $clean_path, $lang );
    $url = $prefix . '/' . ltrim( $translated_path, '/' );

    return $url;
}

/**
 * ---------------------------------------------------------------------
 * MENUS MULTILINGUES: Injeta prefixo de idioma nos links do Menu de Navegação (wp_nav_menu)
 * ---------------------------------------------------------------------
 */
add_filter( 'wp_get_nav_menu_items', 'vibe_multilingual_filter_menu_items_urls', 10, 3 );
function vibe_multilingual_filter_menu_items_urls( $items, $menu, $args ) {
    if ( is_admin() ) {
        return $items;
    }

    $lang = vibe_multilingual_get_current_language();
    if ( empty( $lang ) || $lang === 'pt-BR' ) {
        return $items;
    }

    $home = rtrim( get_option( 'home' ), '/' );

    if ( ! empty( $items ) ) {
        foreach ( $items as $item ) {
            if ( ! empty( $item->url ) && strpos( $item->url, $home ) === 0 ) {
                $base = str_replace( $home, '', $item->url );
                
                // Do not prefix assets or system files
                if ( ! preg_match( '#^/(wp-content|wp-admin|wp-includes|wp-json|\.php)#i', $base ) ) {
                    // Check if it already has any language prefix
                    if ( ! preg_match( '#^/(en|es|fr|de)(/|\?|$)#i', $base ) ) {
                        $translated_path = vibe_multilingual_translate_path_segments( $base, $lang );
                        $item->url = $home . '/' . $lang . '/' . ltrim( $translated_path, '/' );
                    }
                }
            }
        }
    }
    return $items;
}

// Força o flush das novas regras multilíngues apenas uma vez
add_action( 'init', 'vibe_multilingual_auto_flush_rules', 999 );
function vibe_multilingual_auto_flush_rules() {
    if ( ! get_transient( 'vibe_multilingual_rules_flushed_v4' ) ) {
        flush_rewrite_rules();
        set_transient( 'vibe_multilingual_rules_flushed_v4', true, YEAR_IN_SECONDS );
    }
}

// Fallback específico: mapeia /es/personajes/ para a página base /personagens/
add_filter( 'request', 'vibe_multilingual_fix_personajes_pagename', 6 );
function vibe_multilingual_fix_personajes_pagename( $query_vars ) {
    if ( isset( $query_vars['pagename'] ) && $query_vars['pagename'] === 'personajes' ) {
        $lang = isset( $query_vars['app_lang'] ) ? $query_vars['app_lang'] : vibe_multilingual_get_current_language();
        if ( $lang === 'es' ) {
            $page = get_page_by_path( 'personagens' );
            if ( $page ) {
                unset( $query_vars['pagename'] );
                $query_vars['page_id']  = $page->ID;
                $query_vars['app_lang'] = 'es';
            }
        }
    }
    return $query_vars;
}

/**
 * Estende a busca padrão do WordPress para pesquisar nos metadados traduzidos de posts
 */
add_filter( 'posts_search', 'vibe_multilingual_posts_search_meta', 10, 2 );
function vibe_multilingual_posts_search_meta( $search, $wp_query ) {
    global $wpdb;

    if ( is_admin() || ! $wp_query->is_search() ) {
        return $search;
    }

    // Busca o idioma da query ou do ambiente
    $lang = $wp_query->get( 'app_lang' );
    if ( empty( $lang ) ) {
        $lang = vibe_multilingual_get_current_language();
    }

    if ( empty( $lang ) || $lang === 'pt-BR' ) {
        return $search;
    }

    $search_term = $wp_query->get( 's' );
    if ( empty( $search_term ) ) {
        return $search;
    }

    // Escapa o termo de busca para SQL
    $like = '%' . $wpdb->esc_like( $search_term ) . '%';

    // Cria as meta_keys correspondentes ao idioma atual
    $meta_title_key   = '_post_title_' . $lang;
    $meta_content_key = '_post_content_' . $lang;

    // Subquery para buscar posts cujas traduções correspondem ao termo
    $subquery = $wpdb->prepare(
        " OR ($wpdb->posts.ID IN (
            SELECT post_id FROM $wpdb->postmeta 
            WHERE (meta_key = %s OR meta_key = %s) 
            AND meta_value LIKE %s
        ))",
        $meta_title_key,
        $meta_content_key,
        $like
    );

    // Injeta a subquery dentro da busca do WordPress
    // O formato padrão do $search é: AND (((wp_posts.post_title LIKE '%term%') OR ...))
    if ( ! empty( $search ) ) {
        // Encontra o primeiro fechamento do bloco de busca )))
        $search = preg_replace( '/\)\)\)/', ')' . $subquery . '))', $search, 1 );
    }

    return $search;
}

