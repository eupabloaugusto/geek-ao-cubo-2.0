<?php
/**
 * Hello Elementor Child functions and definitions
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enqueue scripts and styles.
 */
function mm_hello_child_enqueue_styles() {
	// 1. Google Fonts: Hanken Grotesk & Inter
	wp_enqueue_style(
		'mm-google-fonts',
		'https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		'1.0.0'
	);

	// 2. Design Tokens - A fonte da verdade visual
	wp_enqueue_style(
		'mm-design-tokens',
		get_stylesheet_directory_uri() . '/design-tokens.css',
		array(),
		'1.0.0'
	);

	// 3. Parent Theme Style (Hello Elementor)
	wp_enqueue_style(
		'hello-elementor-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		'1.0.0'
	);

	// 4. Child Theme style.css (Identificação)
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_uri(),
		array('hello-elementor-parent-style', 'mm-design-tokens'),
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'mm_hello_child_enqueue_styles', 20 );

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
	$css_file_path  = get_stylesheet_directory() . "/{$component_path}.css";
	$js_file_path   = get_stylesheet_directory() . "/{$component_path}.js";

	// 1. Enfileira o CSS se o arquivo existir
	if ( file_exists( $css_file_path ) ) {
		$css_handle = 'mm-css-' . sanitize_key( "{$type}-{$name}" );
		wp_enqueue_style(
			$css_handle,
			get_stylesheet_directory_uri() . "/{$component_path}.css",
			array('mm-design-tokens'),
			filemtime( $css_file_path )
		);
	}

	// 2. Enfileira o JS no rodapé se o arquivo existir
	if ( file_exists( $js_file_path ) ) {
		$js_handle = 'mm-js-' . sanitize_key( "{$type}-{$name}" );
		wp_enqueue_script(
			$js_handle,
			get_stylesheet_directory_uri() . "/{$component_path}.js",
			array(),
			filemtime( $js_file_path ),
			true // Carrega no rodapé para melhor performance
		);
	}

	// Renderiza o template part do WordPress
	get_template_part( $component_path, null, $args );
}

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
		'search_enabled' => true
	) );
	mm_render_component( 'organisms', 'search-modal' );
}
add_action( 'wp_footer', 'mm_render_global_components' );


