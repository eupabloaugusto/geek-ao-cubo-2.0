<?php
/**
 * Custom Post Type: Dublador
 *
 * Registra o tipo de post para os dubladores (pessoas).
 *
 * @package vibe-animes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function geek_ao_cubo_register_cpt_dublador() {
	$labels = array(
		'name'                  => _x( 'Dubladores', 'Post Type General Name', 'vibe-animes' ),
		'singular_name'         => _x( 'Dublador', 'Post Type Singular Name', 'vibe-animes' ),
		'menu_name'             => __( 'Dubladores', 'vibe-animes' ),
		'name_admin_bar'        => __( 'Dublador', 'vibe-animes' ),
		'archives'              => __( 'Arquivos de Dubladores', 'vibe-animes' ),
		'attributes'            => __( 'Atributos do Dublador', 'vibe-animes' ),
		'parent_item_colon'     => __( 'Dublador Pai:', 'vibe-animes' ),
		'all_items'             => __( 'Todos os Dubladores', 'vibe-animes' ),
		'add_new_item'          => __( 'Adicionar Novo Dublador', 'vibe-animes' ),
		'add_new'               => __( 'Adicionar Novo', 'vibe-animes' ),
		'new_item'              => __( 'Novo Dublador', 'vibe-animes' ),
		'edit_item'             => __( 'Editar Dublador', 'vibe-animes' ),
		'update_item'           => __( 'Atualizar Dublador', 'vibe-animes' ),
		'view_item'             => __( 'Ver Dublador', 'vibe-animes' ),
		'view_items'            => __( 'Ver Dubladores', 'vibe-animes' ),
		'search_items'          => __( 'Buscar Dublador', 'vibe-animes' ),
		'not_found'             => __( 'Não encontrado', 'vibe-animes' ),
		'not_found_in_trash'    => __( 'Não encontrado na lixeira', 'vibe-animes' ),
		'featured_image'        => __( 'Imagem Destacada', 'vibe-animes' ),
		'set_featured_image'    => __( 'Definir imagem destacada', 'vibe-animes' ),
		'remove_featured_image' => __( 'Remover imagem destacada', 'vibe-animes' ),
		'use_featured_image'    => __( 'Usar como imagem destacada', 'vibe-animes' ),
		'insert_into_item'      => __( 'Inserir no dublador', 'vibe-animes' ),
		'uploaded_to_this_item' => __( 'Enviado para este dublador', 'vibe-animes' ),
		'items_list'            => __( 'Lista de dubladores', 'vibe-animes' ),
		'items_list_navigation' => __( 'Navegação da lista de dubladores', 'vibe-animes' ),
		'filter_items_list'     => __( 'Filtrar lista de dubladores', 'vibe-animes' ),
	);

	$args = array(
		'label'                 => __( 'Dublador', 'vibe-animes' ),
		'description'           => __( 'Perfis de dubladores e atores de voz', 'vibe-animes' ),
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
		'has_archive'           => false,
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

