<?php
/**
 * Security Filters
 *
 * Módulo de blindagem de conteúdo do catálogo público.
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove animes da categoria "Hentai" e "Erotica" das consultas públicas do WordPress.
 * Age de forma invisível nas vitrines para não quebrar referências no banco de dados.
 *
 * @param WP_Query $query Instância atual da consulta.
 */
function mm_exclude_hentai_from_catalog( $query ) {
	// Apenas afeta o front-end principal (não altera queries no painel wp-admin)
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Filtra as listagens gerais: Home, Buscas, Arquivos Genéricos e Catálogo de Animes
	if ( $query->is_home() || $query->is_search() || $query->is_archive() || $query->is_post_type_archive( 'anime' ) ) {
		
		// Obtém a tax_query atual (se existir) para não sobrescrever outros filtros
		$tax_query = $query->get( 'tax_query' ) ?: array();

		// Adiciona a cláusula de exclusão restrita
		$tax_query[] = array(
			'taxonomy' => 'genero',
			'field'    => 'slug',
			'terms'    => array( 'hentai', 'erotica', 'rx' ),
			'operator' => 'NOT IN',
		);

		// Se já havia outras tax_queries, garantimos a relação 'AND' obrigatória
		if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
			$tax_query['relation'] = 'AND';
		}

		$query->set( 'tax_query', $tax_query );
	}
}
// Acopla ao core do WordPress antes de fazer a busca no banco
add_action( 'pre_get_posts', 'mm_exclude_hentai_from_catalog' );
