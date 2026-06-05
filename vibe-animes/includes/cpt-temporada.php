<?php
/**
 * Custom Post Type: Temporada
 *
 * Agrupa animes por período sazonal (Inverno/Primavera/Verão/Outono).
 * URL: /temporada/{slug}/   ex: /temporada/verao-2025/
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mm_register_cpt_temporada() {
	$labels = array(
		'name'                  => _x( 'Temporadas', 'Post type general name', 'geek-ao-cubo' ),
		'singular_name'         => _x( 'Temporada', 'Post type singular name', 'geek-ao-cubo' ),
		'menu_name'             => _x( 'Temporadas', 'Admin Menu text', 'geek-ao-cubo' ),
		'name_admin_bar'        => _x( 'Temporada', 'Add New on Toolbar', 'geek-ao-cubo' ),
		'add_new'               => __( 'Adicionar Nova', 'geek-ao-cubo' ),
		'add_new_item'          => __( 'Adicionar Nova Temporada', 'geek-ao-cubo' ),
		'new_item'              => __( 'Nova Temporada', 'geek-ao-cubo' ),
		'edit_item'             => __( 'Editar Temporada', 'geek-ao-cubo' ),
		'view_item'             => __( 'Ver Temporada', 'geek-ao-cubo' ),
		'all_items'             => __( 'Todas as Temporadas', 'geek-ao-cubo' ),
		'search_items'          => __( 'Buscar Temporadas', 'geek-ao-cubo' ),
		'not_found'             => __( 'Nenhuma temporada encontrada.', 'geek-ao-cubo' ),
		'not_found_in_trash'    => __( 'Nenhuma temporada encontrada na lixeira.', 'geek-ao-cubo' ),
		'archives'              => _x( 'Calendário de Temporadas', 'The post type archive label used in nav menus', 'geek-ao-cubo' ),
		'filter_items_list'     => _x( 'Filtrar lista de temporadas', 'Screen reader text for the filter links', 'geek-ao-cubo' ),
		'items_list_navigation' => _x( 'Navegação da lista de temporadas', 'Screen reader text for the pagination', 'geek-ao-cubo' ),
		'items_list'            => _x( 'Lista de temporadas', 'Screen reader text for the items list', 'geek-ao-cubo' ),
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
