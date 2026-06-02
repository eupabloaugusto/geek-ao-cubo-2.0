<?php
/**
 * Custom Post Type: Dublador
 *
 * Registra o tipo de post para os dubladores (pessoas).
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function geek_ao_cubo_register_cpt_dublador() {
	$labels = array(
		'name'                  => _x( 'Dubladores', 'Post Type General Name', 'geek-ao-cubo' ),
		'singular_name'         => _x( 'Dublador', 'Post Type Singular Name', 'geek-ao-cubo' ),
		'menu_name'             => __( 'Dubladores', 'geek-ao-cubo' ),
		'name_admin_bar'        => __( 'Dublador', 'geek-ao-cubo' ),
		'archives'              => __( 'Arquivos de Dubladores', 'geek-ao-cubo' ),
		'attributes'            => __( 'Atributos do Dublador', 'geek-ao-cubo' ),
		'parent_item_colon'     => __( 'Dublador Pai:', 'geek-ao-cubo' ),
		'all_items'             => __( 'Todos os Dubladores', 'geek-ao-cubo' ),
		'add_new_item'          => __( 'Adicionar Novo Dublador', 'geek-ao-cubo' ),
		'add_new'               => __( 'Adicionar Novo', 'geek-ao-cubo' ),
		'new_item'              => __( 'Novo Dublador', 'geek-ao-cubo' ),
		'edit_item'             => __( 'Editar Dublador', 'geek-ao-cubo' ),
		'update_item'           => __( 'Atualizar Dublador', 'geek-ao-cubo' ),
		'view_item'             => __( 'Ver Dublador', 'geek-ao-cubo' ),
		'view_items'            => __( 'Ver Dubladores', 'geek-ao-cubo' ),
		'search_items'          => __( 'Buscar Dublador', 'geek-ao-cubo' ),
		'not_found'             => __( 'Não encontrado', 'geek-ao-cubo' ),
		'not_found_in_trash'    => __( 'Não encontrado na lixeira', 'geek-ao-cubo' ),
		'featured_image'        => __( 'Imagem Destacada', 'geek-ao-cubo' ),
		'set_featured_image'    => __( 'Definir imagem destacada', 'geek-ao-cubo' ),
		'remove_featured_image' => __( 'Remover imagem destacada', 'geek-ao-cubo' ),
		'use_featured_image'    => __( 'Usar como imagem destacada', 'geek-ao-cubo' ),
		'insert_into_item'      => __( 'Inserir no dublador', 'geek-ao-cubo' ),
		'uploaded_to_this_item' => __( 'Enviado para este dublador', 'geek-ao-cubo' ),
		'items_list'            => __( 'Lista de dubladores', 'geek-ao-cubo' ),
		'items_list_navigation' => __( 'Navegação da lista de dubladores', 'geek-ao-cubo' ),
		'filter_items_list'     => __( 'Filtrar lista de dubladores', 'geek-ao-cubo' ),
	);

	$args = array(
		'label'                 => __( 'Dublador', 'geek-ao-cubo' ),
		'description'           => __( 'Perfis de dubladores e atores de voz', 'geek-ao-cubo' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'thumbnail', 'custom-fields' ), // ID MAL e Dados no ACF
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 6,
		'menu_icon'             => 'dashicons-microphone',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => 'dubladores',
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true, // Para o Gutenberg, se for usar
		'rewrite'               => array(
			'slug'       => 'dublador',
			'with_front' => false,
		),
	);

	register_post_type( 'dublador', $args );
}
add_action( 'init', 'geek_ao_cubo_register_cpt_dublador', 0 );
