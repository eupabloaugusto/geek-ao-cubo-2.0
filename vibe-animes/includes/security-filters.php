<?php
/**
 * Security Filters
 *
 * Módulo de blindagem de conteúdo do catálogo público.
 *
 * @package vibe-animes
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// #region agent log (debug-5342ce)
if ( ! function_exists( 'mm_dbg5342ce' ) ) {
	function mm_dbg5342ce( array $p ) {
		$p['sessionId']  = '5342ce';
		$p['timestamp']  = isset( $p['timestamp'] ) ? (int) $p['timestamp'] : (int) round( microtime( true ) * 1000 );
		$p['id']         = isset( $p['id'] ) ? (string) $p['id'] : ( 'mm_dbg_' . $p['timestamp'] . '_' . wp_generate_uuid4() );
		$p['runId']      = isset( $p['runId'] ) ? (string) $p['runId'] : 'pre-fix';
		$p['location']   = isset( $p['location'] ) ? (string) $p['location'] : 'security-filters.php';
		$p['message']    = isset( $p['message'] ) ? (string) $p['message'] : 'debug';
		$p['data']       = isset( $p['data'] ) && is_array( $p['data'] ) ? $p['data'] : array();
		$p['hypothesisId'] = isset( $p['hypothesisId'] ) ? (string) $p['hypothesisId'] : 'H?';

		// 1) Best-effort local file (server filesystem; may not be this workspace)
		$log_path = dirname( __DIR__, 2 ) . DIRECTORY_SEPARATOR . 'debug-5342ce.log';
		@file_put_contents( $log_path, wp_json_encode( $p ) . "\n", FILE_APPEND );

		// 2) Preferred: send to local debug collector (writes into this workspace log)
		if ( function_exists( 'wp_remote_post' ) ) {
			@wp_remote_post(
				'http://127.0.0.1:7750/ingest/ab41193b-f4d9-4315-8dc0-7f981894347d',
				array(
					'timeout' => 0.5,
					'headers' => array(
						'Content-Type'       => 'application/json',
						'X-Debug-Session-Id' => '5342ce',
					),
					'body'    => wp_json_encode( $p ),
				)
			);
		}
	}
}
// #endregion agent log (debug-5342ce)

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
		// #region agent log (debug-5342ce)
		mm_dbg5342ce( array(
			'hypothesisId' => 'H1',
			'location'     => 'security-filters.php:mm_exclude_hentai_from_catalog:entry',
			'message'      => 'pre_get_posts main query before tax_query injection',
			'data'         => array(
				'is_home'    => (bool) $query->is_home(),
				'is_search'  => (bool) $query->is_search(),
				'is_archive' => (bool) $query->is_archive(),
				's'          => (string) $query->get( 's' ),
				'post_type'  => $query->get( 'post_type' ),
				'tax_query'  => $query->get( 'tax_query' ),
			),
		) );
		// #endregion agent log (debug-5342ce)
		
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

		// #region agent log (debug-5342ce)
		mm_dbg5342ce( array(
			'hypothesisId' => 'H1',
			'location'     => 'security-filters.php:mm_exclude_hentai_from_catalog:after',
			'message'      => 'pre_get_posts main query after tax_query injection',
			'data'         => array(
				'post_type' => $query->get( 'post_type' ),
				'tax_query' => $query->get( 'tax_query' ),
			),
		) );
		// #endregion agent log (debug-5342ce)
	}
}
// Acopla ao core do WordPress antes de fazer a busca no banco
add_action( 'pre_get_posts', 'mm_exclude_hentai_from_catalog' );

