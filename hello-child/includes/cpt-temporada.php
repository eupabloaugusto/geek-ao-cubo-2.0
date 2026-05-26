<?php
/**
 * Custom Post Type: Temporada
 *
 * Agrupa animes por período sazonal (Inverno/Primavera/Verão/Outono).
 * URL: /temporada/{slug}/   ex: /temporada/verao-2025/
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mm_register_cpt_temporada() {
	$labels = array(
		'name'                  => _x( 'Temporadas', 'Post type general name', 'hello-elementor-child' ),
		'singular_name'         => _x( 'Temporada', 'Post type singular name', 'hello-elementor-child' ),
		'menu_name'             => _x( 'Temporadas', 'Admin Menu text', 'hello-elementor-child' ),
		'name_admin_bar'        => _x( 'Temporada', 'Add New on Toolbar', 'hello-elementor-child' ),
		'add_new'               => __( 'Adicionar Nova', 'hello-elementor-child' ),
		'add_new_item'          => __( 'Adicionar Nova Temporada', 'hello-elementor-child' ),
		'new_item'              => __( 'Nova Temporada', 'hello-elementor-child' ),
		'edit_item'             => __( 'Editar Temporada', 'hello-elementor-child' ),
		'view_item'             => __( 'Ver Temporada', 'hello-elementor-child' ),
		'all_items'             => __( 'Todas as Temporadas', 'hello-elementor-child' ),
		'search_items'          => __( 'Buscar Temporadas', 'hello-elementor-child' ),
		'not_found'             => __( 'Nenhuma temporada encontrada.', 'hello-elementor-child' ),
		'not_found_in_trash'    => __( 'Nenhuma temporada encontrada na lixeira.', 'hello-elementor-child' ),
		'archives'              => _x( 'Calendário de Temporadas', 'The post type archive label used in nav menus', 'hello-elementor-child' ),
		'filter_items_list'     => _x( 'Filtrar lista de temporadas', 'Screen reader text for the filter links', 'hello-elementor-child' ),
		'items_list_navigation' => _x( 'Navegação da lista de temporadas', 'Screen reader text for the pagination', 'hello-elementor-child' ),
		'items_list'            => _x( 'Lista de temporadas', 'Screen reader text for the items list', 'hello-elementor-child' ),
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
		'has_archive'        => 'temporadas',
		'hierarchical'       => false,
		'menu_position'      => 7,
		'menu_icon'          => 'dashicons-calendar-alt',
		'supports'           => array( 'title', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
		'rewrite'            => array(
			'slug'       => 'temporada',
			'with_front' => false,
			'feeds'      => false,
			'pages'      => true,
		),
	);

	register_post_type( 'temporada', $args );
}
add_action( 'init', 'mm_register_cpt_temporada' );
