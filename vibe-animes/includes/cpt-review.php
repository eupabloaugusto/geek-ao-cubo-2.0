<?php
/**
 * Custom Post Type: Review
 *
 * Avaliações editoriais de animes (humanas ou IA-revisadas).
 * URL: /reviews/{slug}/
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mm_register_cpt_review() {
	$labels = array(
		'name'                  => _x( 'Reviews', 'Post type general name', 'geek-ao-cubo' ),
		'singular_name'         => _x( 'Review', 'Post type singular name', 'geek-ao-cubo' ),
		'menu_name'             => _x( 'Reviews', 'Admin Menu text', 'geek-ao-cubo' ),
		'name_admin_bar'        => _x( 'Review', 'Add New on Toolbar', 'geek-ao-cubo' ),
		'add_new'               => __( 'Adicionar Novo', 'geek-ao-cubo' ),
		'add_new_item'          => __( 'Adicionar Nova Review', 'geek-ao-cubo' ),
		'new_item'              => __( 'Nova Review', 'geek-ao-cubo' ),
		'edit_item'             => __( 'Editar Review', 'geek-ao-cubo' ),
		'view_item'             => __( 'Ver Review', 'geek-ao-cubo' ),
		'all_items'             => __( 'Todas as Reviews', 'geek-ao-cubo' ),
		'search_items'          => __( 'Buscar Reviews', 'geek-ao-cubo' ),
		'not_found'             => __( 'Nenhuma review encontrada.', 'geek-ao-cubo' ),
		'not_found_in_trash'    => __( 'Nenhuma review encontrada na lixeira.', 'geek-ao-cubo' ),
		'featured_image'        => _x( 'Imagem de Destaque da Review', 'Overrides the "Featured Image" phrase', 'geek-ao-cubo' ),
		'archives'              => _x( 'Reviews de Animes', 'The post type archive label used in nav menus', 'geek-ao-cubo' ),
		'filter_items_list'     => _x( 'Filtrar lista de reviews', 'Screen reader text for the filter links', 'geek-ao-cubo' ),
		'items_list_navigation' => _x( 'Navegação da lista de reviews', 'Screen reader text for the pagination', 'geek-ao-cubo' ),
		'items_list'            => _x( 'Lista de reviews', 'Screen reader text for the items list', 'geek-ao-cubo' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => 'reviews',
		'hierarchical'       => false,
		'menu_position'      => 8,
		'menu_icon'          => 'dashicons-star-half',
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
		'rewrite'            => array(
			'slug'       => 'reviews',
			'with_front' => false,
			'feeds'      => true,
			'pages'      => true,
		),
		'taxonomies'         => array( 'genero' ), // Reviews também podem ser filtradas por gênero
	);

	register_post_type( 'review', $args );
}
add_action( 'init', 'mm_register_cpt_review' );
