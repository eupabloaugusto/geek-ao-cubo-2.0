<?php
/**
 * Custom Post Type: Review
 *
 * Avaliações editoriais de animes (humanas ou IA-revisadas).
 * URL: /reviews/{slug}/
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mm_register_cpt_review() {
	$labels = array(
		'name'                  => _x( 'Reviews', 'Post type general name', 'hello-elementor-child' ),
		'singular_name'         => _x( 'Review', 'Post type singular name', 'hello-elementor-child' ),
		'menu_name'             => _x( 'Reviews', 'Admin Menu text', 'hello-elementor-child' ),
		'name_admin_bar'        => _x( 'Review', 'Add New on Toolbar', 'hello-elementor-child' ),
		'add_new'               => __( 'Adicionar Novo', 'hello-elementor-child' ),
		'add_new_item'          => __( 'Adicionar Nova Review', 'hello-elementor-child' ),
		'new_item'              => __( 'Nova Review', 'hello-elementor-child' ),
		'edit_item'             => __( 'Editar Review', 'hello-elementor-child' ),
		'view_item'             => __( 'Ver Review', 'hello-elementor-child' ),
		'all_items'             => __( 'Todas as Reviews', 'hello-elementor-child' ),
		'search_items'          => __( 'Buscar Reviews', 'hello-elementor-child' ),
		'not_found'             => __( 'Nenhuma review encontrada.', 'hello-elementor-child' ),
		'not_found_in_trash'    => __( 'Nenhuma review encontrada na lixeira.', 'hello-elementor-child' ),
		'featured_image'        => _x( 'Imagem de Destaque da Review', 'Overrides the "Featured Image" phrase', 'hello-elementor-child' ),
		'archives'              => _x( 'Reviews de Animes', 'The post type archive label used in nav menus', 'hello-elementor-child' ),
		'filter_items_list'     => _x( 'Filtrar lista de reviews', 'Screen reader text for the filter links', 'hello-elementor-child' ),
		'items_list_navigation' => _x( 'Navegação da lista de reviews', 'Screen reader text for the pagination', 'hello-elementor-child' ),
		'items_list'            => _x( 'Lista de reviews', 'Screen reader text for the items list', 'hello-elementor-child' ),
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
