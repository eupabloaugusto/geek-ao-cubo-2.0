<?php
/**
 * Geek ao Cubo — functions and definitions
 *
 * Tema standalone sem dependência de tema pai.
 * Arquitetura 100% Atomic Design com mm_render_component().
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TEMP: Backfill MangaDex via ?update_mangadex=1
if ( isset( $_GET['update_mangadex'] ) && current_user_can('manage_options') ) {
	add_action( 'template_redirect', function() {
		echo "<pre>Iniciando backfill de UUIDs MangaDex...\n\n";
		$mangas = get_posts([ 'post_type' => 'manga', 'posts_per_page' => -1, 'fields' => 'ids' ]);
		$updated = 0;
		foreach ( $mangas as $m_id ) {
			$mal_id = get_post_meta( $m_id, 'manga_id_mal', true );
			if ( $mal_id ) {
				echo "Processando Post {$m_id} (MAL {$mal_id})...\n";
				$uuid = MangaDex_API::get_manga_uuid( $mal_id, get_the_title( $m_id ), $m_id );
				if ( $uuid ) {
					echo "UUID encontrado: {$uuid}. Fazendo pre-warm do aggregate...\n";
					MangaDex_API::get_manga_aggregate( $uuid );
					$updated++;
				} else {
					echo "UUID não encontrado.\n";
				}
			}
		}
		echo "\nConcluído! {$updated} mangás atualizados.</pre>";
		exit;
	});
}

// =========================================================================
// OTIMIZAÇÃO E PREVENÇÃO DE CONFLITOS DE CACHE EM AMBIENTES COMPARTILHADOS
// =========================================================================
remove_action( 'wp_head', 'wp_generator' ); // Esconde a versão do WP (Segurança)

// =========================================================================
// GARBAGE COLLECTOR: SCRIPT DE EXTERMÍNIO DE HENTAIS (Roda apenas 1 vez)
// =========================================================================
function mm_purge_hentai_animes_once() {
	// Se a limpeza já rodou no passado, não faça nada.
	if ( get_option( 'mm_hentai_purged' ) ) {
		return;
	}

	$args = array(
		'post_type'      => 'anime',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
		'tax_query'      => array(
			array(
				'taxonomy' => 'genero',
				'field'    => 'slug',
				'terms'    => array( 'hentai', 'erotica', 'rx' ),
				'operator' => 'IN',
			),
		),
	);

	$hentai_query = new WP_Query( $args );

	if ( $hentai_query->have_posts() ) {
		while ( $hentai_query->have_posts() ) {
			$hentai_query->the_post();
			// Deleta permanentemente (ignora a lixeira) e destrói relacionamentos
			wp_delete_post( get_the_ID(), true );
		}
		wp_reset_postdata();
	}

	// Marca no banco de dados que a limpeza já foi executada para não atrasar o admin futuramente
	update_option( 'mm_hentai_purged', true );
}
add_action( 'admin_init', 'mm_purge_hentai_animes_once' );

// =========================================================================
// THEME SUPPORT — Declara funcionalidades nativas do WordPress
// =========================================================================
function mm_theme_setup() {
	// Permite que o WordPress gerencie o <title> automaticamente
	add_theme_support( 'title-tag' );

	// Suporte a imagens destacadas em posts/CPTs
	add_theme_support( 'post-thumbnails' );

	// HTML5 semântico nos elementos gerados pelo core
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Suporte a logo personalizado no customizer
	add_theme_support( 'custom-logo' );

	// Menus de navegação registrados
	register_nav_menus( array(
		'primary' => __( 'Menu Principal', 'geek-ao-cubo' ),
		'footer'  => __( 'Menu Rodapé', 'geek-ao-cubo' ),
	) );
}
add_action( 'after_setup_theme', 'mm_theme_setup' );

// =========================================================================
// INCLUDES — Carrega módulos de CPT, Taxonomias e ACF separados
// Cada arquivo tem uma responsabilidade única (Single Responsibility Principle)
// =========================================================================
$mm_includes = array(
	// ---- Infraestrutura (ordem importa) ----
	'acf-sync',        // 1. ACF Local JSON — deve vir ANTES dos CPTs
	'acf-dependency',  // 2. Aviso de admin se ACF não estiver instalado

	// ---- CPTs e Taxonomias ----
	'cpt-anime',       // 3. CPT Anime + Taxonomias: Gênero e Status de Exibição
	'cpt-manga',       // 3.5 CPT Manga
	'cpt-dublador',    // 4. CPT Dublador
	'cpt-temporada',   // 5. CPT Temporada
	'cpt-review',      // 6. CPT Review

	// ---- Lógica de negócio ----
	'class-jikan-api',  // 7. Jikan API Cache (Real-Time DB)
	'class-mangadex-api', // 7.5 MangaDex API Cache (capítulos por volume)
	'class-jikan-cron', // 8. Jikan API Cron (Pre-Warming)
	'cpt-helpers',      // 9. Funções de query reutilizáveis (usado por templates e admin)
	'acf-bidirectional', // 10. Relações bidirecionais Episódio↔Anime, Review↔Anime

	// ---- Infraestrutura de suporte ----
	'rewrite-flush',   // 9. Flush de rewrite rules na ativação/desativação
	'admin-columns',   // 10. Colunas personalizadas e metaboxes no wp-admin
	'monetization',    // 11. Sprint 5: Injeção de AdSense e qualificação de afiliados
	'seo-schema',      // 12. Sprint 5: Dados estruturados JSON-LD ricos
	'security-filters',// 13. Segurança e filtragem de conteúdo (+18)
	'sync-jikan-meta', // 14. Sync e backfill do anime_tipo com Jikan API
);

foreach ( $mm_includes as $mm_file ) {
	$mm_file_path = get_template_directory() . "/includes/{$mm_file}.php";
	if ( file_exists( $mm_file_path ) ) {
		require_once $mm_file_path;
	}
}
unset( $mm_includes, $mm_file, $mm_file_path );

/**
 * Enqueue scripts and styles.
 */
function mm_enqueue_styles() {
	// 0. Utils Global JS
	wp_enqueue_script(
		'mm-utils-js',
		get_template_directory_uri() . '/assets/js/mm-utils.js',
		array(),
		filemtime( get_template_directory() . '/assets/js/mm-utils.js' ),
		true
	);

	// 1. Google Fonts: Hanken Grotesk & Inter
	wp_enqueue_style(
		'mm-google-fonts',
		'https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	// 2. Design Tokens — a fonte da verdade visual
	wp_enqueue_style(
		'mm-design-tokens',
		get_template_directory_uri() . '/design-tokens.css',
		array(),
		filemtime( get_template_directory() . '/design-tokens.css' )
	);

	// 3. Tema style.css (base global + reset de botões)
	wp_enqueue_style(
		'geek-ao-cubo-style',
		get_stylesheet_uri(),
		array( 'mm-design-tokens' ),
		filemtime( get_template_directory() . '/style.css' )
	);

	// 4. Estilos específicos do front-page (Home)
	if ( is_page_template( 'template-home.php' ) || is_front_page() ) {
		wp_enqueue_style(
			'mm-style-template-home',
			get_template_directory_uri() . '/template-home.css',
			array( 'geek-ao-cubo-style' ),
			filemtime( get_template_directory() . '/template-home.css' )
		);
	}

	// 5. Estilos específicos de publicações
	if ( is_page_template( 'template-publicacoes.php' ) || is_home() || is_category() || is_tag() || is_author() || is_search() ) {
		wp_enqueue_style(
			'mm-style-template-publicacoes',
			get_template_directory_uri() . '/template-publicacoes.css',
			array( 'geek-ao-cubo-style' ),
			filemtime( get_template_directory() . '/template-publicacoes.css' )
		);
		// Força o carregamento do CSS dos filtros originais do catálogo
		wp_enqueue_style( 'mm-css-organisms-barra-filtros', get_template_directory_uri() . '/organisms/barra-filtros.css', array('geek-ao-cubo-style'), wp_get_theme()->get('Version') );
		wp_enqueue_style( 'mm-css-organisms-barra-filtros-mobile', get_template_directory_uri() . '/organisms/barra-filtros-mobile.css', array('geek-ao-cubo-style'), wp_get_theme()->get('Version') );
	}

	// 6. Estilos específicos do catálogo de animes
	if ( is_page_template( 'template-catalogo.php' ) ) {
		wp_enqueue_style(
			'mm-style-template-catalogo',
			get_template_directory_uri() . '/template-catalogo.css',
			array( 'geek-ao-cubo-style' ),
			filemtime( get_template_directory() . '/template-catalogo.css' )
		);
	}

	// 6. Estilos específicos de templates single por CPT (devem ser enfileirados ANTES do wp_head)
	if ( is_singular( 'anime' ) ) {
		wp_enqueue_style(
			'mm-style-single-anime',
			get_template_directory_uri() . '/single-anime.css',
			array( 'geek-ao-cubo-style' ),
			filemtime( get_template_directory() . '/single-anime.css' )
		);
	}

	if ( is_singular( 'review' ) ) {
		wp_enqueue_style(
			'mm-style-single-review',
			get_template_directory_uri() . '/single-review.css',
			array( 'geek-ao-cubo-style' ),
			filemtime( get_template_directory() . '/single-review.css' )
		);
	}

	if ( is_singular( 'temporada' ) ) {
		wp_enqueue_style(
			'mm-style-single-temporada',
			get_template_directory_uri() . '/single-temporada.css',
			array( 'geek-ao-cubo-style' ),
			filemtime( get_template_directory() . '/single-temporada.css' )
		);
	}

	if ( is_singular( 'dublador' ) ) {
		wp_enqueue_style(
			'mm-style-single-dublador',
			get_template_directory_uri() . '/single-dublador.css',
			array( 'geek-ao-cubo-style' ),
			filemtime( get_template_directory() . '/single-dublador.css' )
		);
	}

	// 5. Pre-enqueue critical / common components to load them in the <head> in parallel (prevents FOUC and layout shift)
	$core_components = array(
		array( 'atoms', 'btn-hamburger' ),
		array( 'atoms', 'logo' ),
		array( 'atoms', 'nav-link' ),
		array( 'atoms', 'input-busca-compact' ),
		array( 'atoms', 'titulo' ),
		array( 'organisms', 'secao-titulo' ),
		array( 'organisms', 'header' ),
		array( 'organisms', 'navigation-drawer' ),
		array( 'organisms', 'search-modal' ),
		array( 'organisms', 'footer' ),
	);

	// Page-specific critical components
	if ( is_front_page() || is_home() ) {
		$core_components[] = array( 'molecules', 'home-placeholder-carousel' );
		$core_components[] = array( 'molecules', 'home-placeholder-episodes' );
		$core_components[] = array( 'organisms', 'secao-anuncios' );
		$core_components[] = array( 'organisms', 'secao-esteira-animes' );
		$core_components[] = array( 'organisms', 'secao-destaque' );
		$core_components[] = array( 'organisms', 'secao-noticias-recentes' );
		$core_components[] = array( 'organisms', 'sidebar' );
		$core_components[] = array( 'molecules', 'card-noticia' );
		$core_components[] = array( 'molecules', 'card-anime' );
	} elseif ( is_singular( 'anime' ) ) {
		$core_components[] = array( 'organisms', 'hero-anime' );
		$core_components[] = array( 'organisms', 'sidebar-anime-info' );
		$core_components[] = array( 'organisms', 'secao-personagens-dubladores-accordion' );
		$core_components[] = array( 'organisms', 'secao-episodios-accordion' );
		$core_components[] = array( 'organisms', 'secao-reviews' );
		$core_components[] = array( 'organisms', 'secao-relacionados' );
		$core_components[] = array( 'molecules', 'card-personagem-dublador' );
		$core_components[] = array( 'molecules', 'card-personagem' );
		$core_components[] = array( 'molecules', 'card-staff' );
		$core_components[] = array( 'molecules', 'review-card' );
	} elseif ( is_singular( 'manga' ) ) {
		$core_components[] = array( 'organisms', 'hero-anime' );
		$core_components[] = array( 'organisms', 'secao-episodios-accordion' ); // CSS reutilizado pelo secao-manga-volumes-accordion
		$core_components[] = array( 'organisms', 'secao-personagens' );
		$core_components[] = array( 'organisms', 'secao-recomendacoes' );
		$core_components[] = array( 'molecules', 'card-personagem' );
	} elseif ( is_singular( 'temporada' ) ) {
		$core_components[] = array( 'organisms', 'secao-esteira-animes' );
	} elseif ( is_singular( 'review' ) ) {
		$core_components[] = array( 'organisms', 'secao-leia-tambem' );
	} elseif ( is_post_type_archive( 'anime' ) || is_tax( 'genero' ) || is_tax( 'status-exibicao' ) ) {
		$core_components[] = array( 'organisms', 'barra-filtros' );
		$core_components[] = array( 'organisms', 'barra-filtros-mobile' );
		$core_components[] = array( 'organisms', 'lista-catalogo' );
		$core_components[] = array( 'molecules', 'card-catalogo' );
	}

	foreach ( $core_components as $comp ) {
		$type = $comp[0];
		$name = $comp[1];
		$component_path = "{$type}/{$name}";
		$css_file_path  = get_template_directory() . "/{$component_path}.css";
		if ( file_exists( $css_file_path ) ) {
			$css_handle = 'mm-css-' . sanitize_key( "{$type}-{$name}" );
			wp_enqueue_style(
				$css_handle,
				get_template_directory_uri() . "/{$component_path}.css",
				array( 'geek-ao-cubo-style' ),
				filemtime( $css_file_path )
			);
		}
	}

	// 6. Remove CSS do Elementor plugin — conflita com nosso Atomic Design
	$elementor_handles = array(
		'elementor-frontend',
		'elementor-common',
		'elementor-icons',
		'elementor-animations',
		'elementor-post',
		'elementor-global',
		'e-theme-ui-light',
		'e-animations',
	);
	foreach ( $elementor_handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'mm_enqueue_styles', 9999 );


/**
 * Remove CSS dinâmico do Elementor (handles com IDs numéricos de posts)
 */
add_filter( 'print_styles_array', function( $handles ) {
	return array_filter( $handles, function( $handle ) {
		return strpos( $handle, 'elementor' ) === false
			&& strpos( $handle, 'e-theme' ) === false;
	} );
} );

/**
 * Helper function to safely render an atomic design component and auto-enqueue its CSS & JS.
 * This guarantees we ONLY load CSS and JS for components actually rendered on the page, ensuring max performance.
 *
 * @param string $type The component type ('atoms', 'molecules', 'organisms', 'templates')
 * @param string $name The component file slug
 * @param array  $args Arguments passed to the template part
 */
function mm_render_component( $type, $name, $args = array() ) {
	$component_path = "{$type}/{$name}";
	$css_file_path  = get_template_directory() . "/{$component_path}.css";
	$js_file_path   = get_template_directory() . "/{$component_path}.js";

	// 1. Enfileira o CSS se o arquivo existir
	if ( file_exists( $css_file_path ) ) {
		$css_handle = 'mm-css-' . sanitize_key( "{$type}-{$name}" );
		
		// Se já foi registrado ou enfileirado no head, deixamos o core processar
		if ( ! wp_style_is( $css_handle, 'enqueued' ) && ! wp_style_is( $css_handle, 'done' ) ) {
			wp_enqueue_style(
				$css_handle,
				get_template_directory_uri() . "/{$component_path}.css",
				array( 'geek-ao-cubo-style' ),
				filemtime( $css_file_path )
			);

			// Se wp_head() já rodou, imprime o estilo inline imediatamente para prevenir FOUC
			if ( did_action( 'wp_head' ) ) {
				$GLOBALS['mm_printed_styles'] = isset( $GLOBALS['mm_printed_styles'] ) && is_array( $GLOBALS['mm_printed_styles'] )
					? $GLOBALS['mm_printed_styles']
					: array();
				
				if ( ! isset( $GLOBALS['mm_printed_styles'][ $css_handle ] ) ) {
					$GLOBALS['mm_printed_styles'][ $css_handle ] = true;
					$version = filemtime( $css_file_path );
					echo '<link rel="stylesheet" id="' . esc_attr( $css_handle ) . '-inline" href="' . esc_url( get_template_directory_uri() . "/{$component_path}.css?ver={$version}" ) . '" media="all" />';
				}
			}
		}
	}

	// 2. Enfileira o JS no rodapé se o arquivo existir
	if ( file_exists( $js_file_path ) ) {
		$js_handle = 'mm-js-' . sanitize_key( "{$type}-{$name}" );
		wp_enqueue_script(
			$js_handle,
			get_template_directory_uri() . "/{$component_path}.js",
			array(),
			filemtime( $js_file_path ),
			true
		);
	}

	// Renderiza o template part do WordPress
	get_template_part( $component_path, null, $args );
}




/**
 * Substitui o oEmbed padrão do WordPress pelo átomo embed-video do Atomic Design.
 * Intercepta URLs do YouTube no conteúdo dos artigos e entrega o componente
 * facade (thumbnail + botão play) em vez do iframe pesado diretamente.
 *
 * @param string $html    HTML gerado pelo oEmbed do WordPress.
 * @param string $url     URL original do embed.
 * @param array  $attr    Atributos do embed.
 * @param int    $post_id ID do post.
 * @return string         HTML substituído ou original se não for YouTube.
 */
function mm_embed_youtube_as_component( $html, $url, $attr, $post_id ) {
	if ( ! function_exists( 'mm_get_youtube_video_id' ) ) {
		return $html;
	}

	$video_id = mm_get_youtube_video_id( $url );
	if ( empty( $video_id ) ) {
		return $html;
	}

	$title = ! empty( $post_id ) ? get_the_title( $post_id ) : __( 'Vídeo do YouTube', 'geek-ao-cubo' );

	return '<div class="secao-artigo-unico__embedded-block">' .
		mm_get_rendered_component( 'atoms', 'embed-video', array(
			'video_id' => $video_id,
			'title'    => $title,
		) ) .
	'</div>';
}
add_filter( 'embed_oembed_html', 'mm_embed_youtube_as_component', 10, 4 );

/**
 * Remove Gutenberg & standard emoji bloat for cleaner performance (Core Web Vitals >= 90)
 */
function mm_optimize_wordpress_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'mm_optimize_wordpress_head' );

/**
 * Garante que o Modal de Busca e o Menu Lateral (Navigation Drawer)
 * estejam sempre presentes em 100% das páginas, independente de builders.
 */
function mm_render_global_components() {
	mm_render_component( 'organisms', 'navigation-drawer', array(
		'search_enabled' => true,
	) );
	mm_render_component( 'organisms', 'search-modal' );
	if ( is_singular() ) {
		mm_render_component( 'atoms', 'aviso-adblock', array(
			'class' => 'aviso-adblock--floating',
		) );
	}
}
add_action( 'wp_footer', 'mm_render_global_components' );

// =========================================================================
// ROTEAMENTO VIRTUAL (REWRITE RULES) PARA PERSONAGENS
// =========================================================================
function mm_character_rewrite_rules() {
	add_rewrite_rule(
		'^personagem/([0-9]+)/?([^/]*)/?$',
		'index.php?personagem_id=$matches[1]',
		'top'
	);
}
add_action( 'init', 'mm_character_rewrite_rules' );

function mm_character_query_vars( $vars ) {
	$vars[] = 'personagem_id';
	return $vars;
}
add_filter( 'query_vars', 'mm_character_query_vars' );

function mm_character_template_include( $template ) {
	if ( get_query_var( 'personagem_id' ) ) {
		$new_template = locate_template( array( 'single-personagem.php' ) );
		if ( ! empty( $new_template ) ) {
			return $new_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'mm_character_template_include', 99 );

// =========================================================================
// SCRIPT DE IMPORTAÇÃO DE MANGÁS (Via URL: ?import_top_mangas=1)
// =========================================================================
function mm_import_top_mangas_endpoint() {
	if ( isset( $_GET['import_top_mangas'] ) && current_user_can('manage_options') ) {
		echo "<h3>Iniciando importação dos 10 mangás mais populares...</h3>";
		
		$url = 'https://api.jikan.moe/v4/top/manga?limit=10';
		$response = wp_remote_get( $url, ['timeout' => 30] );

		if ( is_wp_error( $response ) ) {
			wp_die( "Erro de conexao com a Jikan API: " . $response->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['data'] ) ) {
			wp_die( "Nenhum dado retornado da API." );
		}

		$mangas = $data['data'];
		$count = 0;

		echo "<ul>";
		foreach ( $mangas as $manga ) {
			$mal_id = $manga['mal_id'];
			$title = $manga['title'];

			// Verifica se ja existe
			$exists = get_posts( array(
				'post_type'  => 'manga',
				'meta_key'   => 'manga_id_mal',
				'meta_value' => $mal_id,
				'posts_per_page' => 1,
				'fields' => 'ids',
				'post_status' => 'any'
			) );

			if ( ! empty( $exists ) ) {
				echo "<li style='color: #888;'>[Ignorado] Manga '{$title}' (ID: {$mal_id}) já existe no banco.</li>";
				continue;
			}

			// Cria o post
			$post_data = array(
				'post_title'    => sanitize_text_field( $title ),
				'post_status'   => 'publish',
				'post_type'     => 'manga',
			);

			$post_id = wp_insert_post( $post_data );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, 'manga_id_mal', $mal_id );
				echo "<li style='color: green;'>[Criado] Manga '{$title}' (Post ID: {$post_id}, MAL ID: {$mal_id}).</li>";
				$count++;
			} else {
				echo "<li style='color: red;'>[Erro] Falha ao criar manga '{$title}'.</li>";
			}
			
			// Throttling leve para a API
			usleep(350000); 
		}
		echo "</ul>";
		echo "<p><strong>Finalizado! {$count} mangás importados.</strong></p>";
		wp_die();
	}
}
add_action( 'init', 'mm_import_top_mangas_endpoint' );

// =========================================================================
// SCRIPT DE LIMPEZA DE CACHE DOS MANGÁS (Via URL: ?flush_manga_cache=1)
// =========================================================================
function mm_flush_manga_cache_endpoint() {
	if ( isset( $_GET['flush_manga_cache'] ) && current_user_can('manage_options') ) {
		echo "<h3>Limpando cache de todos os mangás importados...</h3>";
		
		$mangas = get_posts( array(
			'post_type'      => 'manga',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		$count = 0;
		echo "<ul>";
		foreach ( $mangas as $post_id ) {
			$mal_id = get_post_meta( $post_id, 'manga_id_mal', true );
			if ( $mal_id ) {
				// Apaga os transients do Jikan para este ID
				delete_transient( 'jikan_manga_full_' . $mal_id );
				delete_option( '_transient_timeout_jikan_manga_full_' . $mal_id );
				
				delete_transient( 'jikan_manga_chars_' . $mal_id );
				delete_option( '_transient_timeout_jikan_manga_chars_' . $mal_id );

				delete_transient( 'jikan_manga_recs_' . $mal_id );
				delete_option( '_transient_timeout_jikan_manga_recs_' . $mal_id );
				
				echo "<li style='color: green;'>Cache apagado para o Mangá ID Local: {$post_id} (MAL: {$mal_id})</li>";
				$count++;
			}
		}
		echo "</ul>";
		echo "<p><strong>Finalizado! Cache de {$count} mangás foi limpo. As próximas visitas gerarão uma nova requisição limpa para a API.</strong></p>";
		wp_die();
	}
}
add_action( 'init', 'mm_flush_manga_cache_endpoint' );

// =========================================================================
// AUTO-FLUSH PARA MANGÁS (Resolve 404s)
// =========================================================================
function mm_auto_flush_rewrite_rules_for_manga() {
	if ( ! get_transient( 'mm_manga_rewrite_flushed' ) ) {
		flush_rewrite_rules();
		set_transient( 'mm_manga_rewrite_flushed', true, YEAR_IN_SECONDS );
	}
}
add_action( 'init', 'mm_auto_flush_rewrite_rules_for_manga', 999 );
