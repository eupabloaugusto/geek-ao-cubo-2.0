<?php
/**
 * Custom Post Type: Manga
 * Taxonomias: Gênero (compartilhado), Status de Publicação
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// =========================================================================
// TAXONOMIA: Status de Publicação (Mangás)
// Exemplos: Em Publicação, Finalizado, Em Hiato, Descontinuado
// =========================================================================

function mm_register_taxonomy_status_manga() {
	$labels = array(
		'name'              => _x( 'Status (Mangá)', 'taxonomy general name', 'geek-ao-cubo' ),
		'singular_name'     => _x( 'Status', 'taxonomy singular name', 'geek-ao-cubo' ),
		'search_items'      => __( 'Buscar Status', 'geek-ao-cubo' ),
		'all_items'         => __( 'Todos os Status', 'geek-ao-cubo' ),
		'edit_item'         => __( 'Editar Status', 'geek-ao-cubo' ),
		'update_item'       => __( 'Atualizar Status', 'geek-ao-cubo' ),
		'add_new_item'      => __( 'Adicionar Status', 'geek-ao-cubo' ),
		'new_item_name'     => __( 'Nome do Novo Status', 'geek-ao-cubo' ),
		'menu_name'         => __( 'Status', 'geek-ao-cubo' ),
		'not_found'         => __( 'Nenhum status encontrado.', 'geek-ao-cubo' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => array(
			'slug'         => 'status-manga',
			'with_front'   => false,
		),
	);

	register_taxonomy( 'status_manga', array( 'manga' ), $args );
}
add_action( 'init', 'mm_register_taxonomy_status_manga', 0 );


// =========================================================================
// VINCULAR TAXONOMIA: Gênero ao CPT Manga
// =========================================================================

function mm_link_genero_to_manga() {
	register_taxonomy_for_object_type( 'genero', 'manga' );
}
add_action( 'init', 'mm_link_genero_to_manga', 10 );


// =========================================================================
// CPT: Manga
// URL: /mangas/{slug}/
// =========================================================================

function mm_register_cpt_manga() {
	$labels = array(
		'name'                  => _x( 'Mangás', 'Post type general name', 'geek-ao-cubo' ),
		'singular_name'         => _x( 'Mangá', 'Post type singular name', 'geek-ao-cubo' ),
		'menu_name'             => _x( 'Mangás', 'Admin Menu text', 'geek-ao-cubo' ),
		'name_admin_bar'        => _x( 'Mangá', 'Add New on Toolbar', 'geek-ao-cubo' ),
		'add_new'               => __( 'Adicionar Novo', 'geek-ao-cubo' ),
		'add_new_item'          => __( 'Adicionar Novo Mangá', 'geek-ao-cubo' ),
		'new_item'              => __( 'Novo Mangá', 'geek-ao-cubo' ),
		'edit_item'             => __( 'Editar Mangá', 'geek-ao-cubo' ),
		'view_item'             => __( 'Ver Mangá', 'geek-ao-cubo' ),
		'all_items'             => __( 'Todos os Mangás', 'geek-ao-cubo' ),
		'search_items'          => __( 'Buscar Mangás', 'geek-ao-cubo' ),
		'parent_item_colon'     => __( 'Mangás Relacionados:', 'geek-ao-cubo' ),
		'not_found'             => __( 'Nenhum mangá encontrado.', 'geek-ao-cubo' ),
		'not_found_in_trash'    => __( 'Nenhum mangá encontrado na lixeira.', 'geek-ao-cubo' ),
		'featured_image'        => _x( 'Capa do Mangá', 'Overrides the "Featured Image" phrase', 'geek-ao-cubo' ),
		'set_featured_image'    => _x( 'Definir Capa', 'Overrides the "Set featured image" phrase', 'geek-ao-cubo' ),
		'remove_featured_image' => _x( 'Remover Capa', 'Overrides the "Remove featured image" phrase', 'geek-ao-cubo' ),
		'use_featured_image'    => _x( 'Usar como Capa', 'Overrides the "Use as featured image" phrase', 'geek-ao-cubo' ),
		'archives'              => _x( 'Catálogo de Mangás', 'The post type archive label used in nav menus', 'geek-ao-cubo' ),
		'insert_into_item'      => _x( 'Inserir no mangá', 'Overrides the "Insert into post" phrase', 'geek-ao-cubo' ),
		'uploaded_to_this_item' => _x( 'Enviado para este mangá', 'Overrides the "Uploaded to this post" phrase', 'geek-ao-cubo' ),
		'filter_items_list'     => _x( 'Filtrar lista de mangás', 'Screen reader text for the filter links', 'geek-ao-cubo' ),
		'items_list_navigation' => _x( 'Navegação da lista de mangás', 'Screen reader text for the pagination', 'geek-ao-cubo' ),
		'items_list'            => _x( 'Lista de mangás', 'Screen reader text for the items list', 'geek-ao-cubo' ),
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
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-book',
		'supports'           => array( 'title', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
		'rewrite'            => array(
			'slug'       => 'catalogo-de-mangas',
			'with_front' => false,
			'feeds'      => true,
			'pages'      => true,
		),
		'taxonomies'         => array( 'status_manga' ), // 'genero' vinculado separadamente
	);

	register_post_type( 'manga', $args );
}
add_action( 'init', 'mm_register_cpt_manga' );
