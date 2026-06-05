<?php
/**
 * Custom Post Type: Anime
 * Taxonomias: Gênero, Status de Exibição
 *
 * @package geek-ao-cubo
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
		'name'              => _x( 'Gêneros', 'taxonomy general name', 'geek-ao-cubo' ),
		'singular_name'     => _x( 'Gênero', 'taxonomy singular name', 'geek-ao-cubo' ),
		'search_items'      => __( 'Buscar Gêneros', 'geek-ao-cubo' ),
		'all_items'         => __( 'Todos os Gêneros', 'geek-ao-cubo' ),
		'edit_item'         => __( 'Editar Gênero', 'geek-ao-cubo' ),
		'update_item'       => __( 'Atualizar Gênero', 'geek-ao-cubo' ),
		'add_new_item'      => __( 'Adicionar Gênero', 'geek-ao-cubo' ),
		'new_item_name'     => __( 'Nome do Novo Gênero', 'geek-ao-cubo' ),
		'menu_name'         => __( 'Gêneros', 'geek-ao-cubo' ),
		'not_found'         => __( 'Nenhum gênero encontrado.', 'geek-ao-cubo' ),
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
		'name'              => _x( 'Status', 'taxonomy general name', 'geek-ao-cubo' ),
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
		'name'                  => _x( 'Animes', 'Post type general name', 'geek-ao-cubo' ),
		'singular_name'         => _x( 'Anime', 'Post type singular name', 'geek-ao-cubo' ),
		'menu_name'             => _x( 'Animes', 'Admin Menu text', 'geek-ao-cubo' ),
		'name_admin_bar'        => _x( 'Anime', 'Add New on Toolbar', 'geek-ao-cubo' ),
		'add_new'               => __( 'Adicionar Novo', 'geek-ao-cubo' ),
		'add_new_item'          => __( 'Adicionar Novo Anime', 'geek-ao-cubo' ),
		'new_item'              => __( 'Novo Anime', 'geek-ao-cubo' ),
		'edit_item'             => __( 'Editar Anime', 'geek-ao-cubo' ),
		'view_item'             => __( 'Ver Anime', 'geek-ao-cubo' ),
		'all_items'             => __( 'Todos os Animes', 'geek-ao-cubo' ),
		'search_items'          => __( 'Buscar Animes', 'geek-ao-cubo' ),
		'parent_item_colon'     => __( 'Animes Relacionados:', 'geek-ao-cubo' ),
		'not_found'             => __( 'Nenhum anime encontrado.', 'geek-ao-cubo' ),
		'not_found_in_trash'    => __( 'Nenhum anime encontrado na lixeira.', 'geek-ao-cubo' ),
		'featured_image'        => _x( 'Capa do Anime', 'Overrides the "Featured Image" phrase', 'geek-ao-cubo' ),
		'set_featured_image'    => _x( 'Definir Capa', 'Overrides the "Set featured image" phrase', 'geek-ao-cubo' ),
		'remove_featured_image' => _x( 'Remover Capa', 'Overrides the "Remove featured image" phrase', 'geek-ao-cubo' ),
		'use_featured_image'    => _x( 'Usar como Capa', 'Overrides the "Use as featured image" phrase', 'geek-ao-cubo' ),
		'archives'              => _x( 'Catálogo de Animes', 'The post type archive label used in nav menus', 'geek-ao-cubo' ),
		'insert_into_item'      => _x( 'Inserir no anime', 'Overrides the "Insert into post" phrase', 'geek-ao-cubo' ),
		'uploaded_to_this_item' => _x( 'Enviado para este anime', 'Overrides the "Uploaded to this post" phrase', 'geek-ao-cubo' ),
		'filter_items_list'     => _x( 'Filtrar lista de animes', 'Screen reader text for the filter links', 'geek-ao-cubo' ),
		'items_list_navigation' => _x( 'Navegação da lista de animes', 'Screen reader text for the pagination', 'geek-ao-cubo' ),
		'items_list'            => _x( 'Lista de animes', 'Screen reader text for the items list', 'geek-ao-cubo' ),
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
		'has_archive'        => false,   // URL do arquivo: /catalogo-de-animes/
		'hierarchical'       => true,
		'menu_position'      => 4,
		'menu_icon'          => 'dashicons-video-alt3',
		'supports'           => array( 'title', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'page-attributes' ),
		'rewrite'            => array(
			'slug'       => 'catalogo-de-animes',
			'with_front' => false,
			'feeds'      => true,
			'pages'      => true,
		),
		'taxonomies'         => array( 'genero', 'status_exibicao' ),
	);

	register_post_type( 'anime', $args );
}
add_action( 'init', 'mm_register_cpt_anime' );
