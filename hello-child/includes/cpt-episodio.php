<?php
/**
 * Custom Post Type: Episódio
 *
 * Representa um episódio individual de um anime.
 * URL: /episodios/{slug}/
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mm_register_cpt_episodio() {
	$labels = array(
		'name'                  => _x( 'Episódios', 'Post type general name', 'hello-elementor-child' ),
		'singular_name'         => _x( 'Episódio', 'Post type singular name', 'hello-elementor-child' ),
		'menu_name'             => _x( 'Episódios', 'Admin Menu text', 'hello-elementor-child' ),
		'name_admin_bar'        => _x( 'Episódio', 'Add New on Toolbar', 'hello-elementor-child' ),
		'add_new'               => __( 'Adicionar Novo', 'hello-elementor-child' ),
		'add_new_item'          => __( 'Adicionar Novo Episódio', 'hello-elementor-child' ),
		'new_item'              => __( 'Novo Episódio', 'hello-elementor-child' ),
		'edit_item'             => __( 'Editar Episódio', 'hello-elementor-child' ),
		'view_item'             => __( 'Ver Episódio', 'hello-elementor-child' ),
		'all_items'             => __( 'Todos os Episódios', 'hello-elementor-child' ),
		'search_items'          => __( 'Buscar Episódios', 'hello-elementor-child' ),
		'not_found'             => __( 'Nenhum episódio encontrado.', 'hello-elementor-child' ),
		'not_found_in_trash'    => __( 'Nenhum episódio encontrado na lixeira.', 'hello-elementor-child' ),
		'featured_image'        => _x( 'Thumbnail do Episódio', 'Overrides the "Featured Image" phrase', 'hello-elementor-child' ),
		'archives'              => _x( 'Arquivo de Episódios', 'The post type archive label used in nav menus', 'hello-elementor-child' ),
		'filter_items_list'     => _x( 'Filtrar lista de episódios', 'Screen reader text for the filter links', 'hello-elementor-child' ),
		'items_list_navigation' => _x( 'Navegação da lista de episódios', 'Screen reader text for the pagination', 'hello-elementor-child' ),
		'items_list'            => _x( 'Lista de episódios', 'Screen reader text for the items list', 'hello-elementor-child' ),
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
		'has_archive'        => 'episodios',
		'hierarchical'       => false,
		'menu_position'      => 6,
		'menu_icon'          => 'dashicons-format-video',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
		'rewrite'            => array(
			'slug'       => 'episodios',
			'with_front' => false,
			'feeds'      => true,
			'pages'      => true,
		),
	);

	register_post_type( 'episodio', $args );
}
add_action( 'init', 'mm_register_cpt_episodio' );
