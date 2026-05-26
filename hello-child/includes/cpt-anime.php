<?php
/**
 * Custom Post Type: Anime
 * Taxonomias: Gênero, Status de Exibição
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// =========================================================================
// TAXONOMIA: Gênero
// Exemplos: Ação, Romance, Isekai, Shonen, Seinen, Slice of Life…
// =========================================================================

function mm_register_taxonomy_genero() {
	$labels = array(
		'name'              => _x( 'Gêneros', 'taxonomy general name', 'hello-elementor-child' ),
		'singular_name'     => _x( 'Gênero', 'taxonomy singular name', 'hello-elementor-child' ),
		'search_items'      => __( 'Buscar Gêneros', 'hello-elementor-child' ),
		'all_items'         => __( 'Todos os Gêneros', 'hello-elementor-child' ),
		'edit_item'         => __( 'Editar Gênero', 'hello-elementor-child' ),
		'update_item'       => __( 'Atualizar Gênero', 'hello-elementor-child' ),
		'add_new_item'      => __( 'Adicionar Gênero', 'hello-elementor-child' ),
		'new_item_name'     => __( 'Nome do Novo Gênero', 'hello-elementor-child' ),
		'menu_name'         => __( 'Gêneros', 'hello-elementor-child' ),
		'not_found'         => __( 'Nenhum gênero encontrado.', 'hello-elementor-child' ),
	);

	$args = array(
		'hierarchical'      => false, // Comportamento de tag (não de categoria)
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,  // Necessário para o editor Gutenberg e WP REST API
		'rewrite'           => array(
			'slug'         => 'genero',
			'with_front'   => false,
		),
	);

	register_taxonomy( 'genero', array( 'anime' ), $args );
}
add_action( 'init', 'mm_register_taxonomy_genero', 0 );


// =========================================================================
// TAXONOMIA: Status de Exibição
// Exemplos: Em Exibição, Finalizado, Brevemente, Pausado
// =========================================================================

function mm_register_taxonomy_status_exibicao() {
	$labels = array(
		'name'              => _x( 'Status', 'taxonomy general name', 'hello-elementor-child' ),
		'singular_name'     => _x( 'Status', 'taxonomy singular name', 'hello-elementor-child' ),
		'search_items'      => __( 'Buscar Status', 'hello-elementor-child' ),
		'all_items'         => __( 'Todos os Status', 'hello-elementor-child' ),
		'edit_item'         => __( 'Editar Status', 'hello-elementor-child' ),
		'update_item'       => __( 'Atualizar Status', 'hello-elementor-child' ),
		'add_new_item'      => __( 'Adicionar Status', 'hello-elementor-child' ),
		'new_item_name'     => __( 'Nome do Novo Status', 'hello-elementor-child' ),
		'menu_name'         => __( 'Status', 'hello-elementor-child' ),
		'not_found'         => __( 'Nenhum status encontrado.', 'hello-elementor-child' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => array(
			'slug'         => 'status',
			'with_front'   => false,
		),
	);

	register_taxonomy( 'status_exibicao', array( 'anime' ), $args );
}
add_action( 'init', 'mm_register_taxonomy_status_exibicao', 0 );


// =========================================================================
// CPT: Anime
// URL: /animes/{slug}/
// =========================================================================

function mm_register_cpt_anime() {
	$labels = array(
		'name'                  => _x( 'Animes', 'Post type general name', 'hello-elementor-child' ),
		'singular_name'         => _x( 'Anime', 'Post type singular name', 'hello-elementor-child' ),
		'menu_name'             => _x( 'Animes', 'Admin Menu text', 'hello-elementor-child' ),
		'name_admin_bar'        => _x( 'Anime', 'Add New on Toolbar', 'hello-elementor-child' ),
		'add_new'               => __( 'Adicionar Novo', 'hello-elementor-child' ),
		'add_new_item'          => __( 'Adicionar Novo Anime', 'hello-elementor-child' ),
		'new_item'              => __( 'Novo Anime', 'hello-elementor-child' ),
		'edit_item'             => __( 'Editar Anime', 'hello-elementor-child' ),
		'view_item'             => __( 'Ver Anime', 'hello-elementor-child' ),
		'all_items'             => __( 'Todos os Animes', 'hello-elementor-child' ),
		'search_items'          => __( 'Buscar Animes', 'hello-elementor-child' ),
		'parent_item_colon'     => __( 'Animes Relacionados:', 'hello-elementor-child' ),
		'not_found'             => __( 'Nenhum anime encontrado.', 'hello-elementor-child' ),
		'not_found_in_trash'    => __( 'Nenhum anime encontrado na lixeira.', 'hello-elementor-child' ),
		'featured_image'        => _x( 'Capa do Anime', 'Overrides the "Featured Image" phrase', 'hello-elementor-child' ),
		'set_featured_image'    => _x( 'Definir Capa', 'Overrides the "Set featured image" phrase', 'hello-elementor-child' ),
		'remove_featured_image' => _x( 'Remover Capa', 'Overrides the "Remove featured image" phrase', 'hello-elementor-child' ),
		'use_featured_image'    => _x( 'Usar como Capa', 'Overrides the "Use as featured image" phrase', 'hello-elementor-child' ),
		'archives'              => _x( 'Catálogo de Animes', 'The post type archive label used in nav menus', 'hello-elementor-child' ),
		'insert_into_item'      => _x( 'Inserir no anime', 'Overrides the "Insert into post" phrase', 'hello-elementor-child' ),
		'uploaded_to_this_item' => _x( 'Enviado para este anime', 'Overrides the "Uploaded to this post" phrase', 'hello-elementor-child' ),
		'filter_items_list'     => _x( 'Filtrar lista de animes', 'Screen reader text for the filter links', 'hello-elementor-child' ),
		'items_list_navigation' => _x( 'Navegação da lista de animes', 'Screen reader text for the pagination', 'hello-elementor-child' ),
		'items_list'            => _x( 'Lista de animes', 'Screen reader text for the items list', 'hello-elementor-child' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => true,  // Ativa suporte ao REST API e editor Gutenberg
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => 'animes',   // URL do arquivo: /animes/
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-video-alt2',
		'supports'           => array( 'title', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
		'rewrite'            => array(
			'slug'       => 'animes',
			'with_front' => false,
			'feeds'      => true,
			'pages'      => true,
		),
		'taxonomies'         => array( 'genero', 'status_exibicao' ),
	);

	register_post_type( 'anime', $args );
}
add_action( 'init', 'mm_register_cpt_anime' );
