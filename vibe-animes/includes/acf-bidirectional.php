<?php
/**
 * ACF — Relações Bidirecionais
 *
 * Mantém a sincronização automática entre os CPTs relacionados:
 *
 *   Episódio  ep_anime_relacionado  →  Anime
 *   Anime     anime_episodios       ←  (campo oculto, gerenciado por este hook)
 *
 *   Review    review_anime_rel      →  Anime
 *   Anime     anime_reviews         ←  (campo oculto, gerenciado por este hook)
 *
 * Como funciona:
 *   1. Usuário salva um Episódio selecionando o Anime Pai.
 *   2. Este hook lê o campo `ep_anime_relacionado` recém-salvo.
 *   3. Atualiza o campo `anime_episodios` do Anime correspondente
 *      adicionando o ID do episódio (se ainda não estiver lá).
 *   4. Remove o episódio de qualquer outro anime que o tinha como
 *      referência (caso o usuário tenha trocado o anime pai).
 *
 * ⚠️  Requer ACF instalado e ativo. A função `get_field()` só é chamada
 *     dentro de hooks do ACF, então é seguro assumir que ACF está disponível.
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utilitário interno: Sincroniza uma relação bidirecional entre dois posts.
 *
 * @param int    $post_a_id         ID do post que acabou de ser salvo (ex: Episódio).
 * @param string $field_a           Nome do campo no post A que aponta para B (ex: 'ep_anime_relacionado').
 * @param string $field_b           Nome do campo no post B que lista os As (ex: 'anime_episodios').
 * @param string $post_type_b       Post type do lado B (ex: 'anime').
 */
function mm_sync_bidirectional_relationship( $post_a_id, $field_a, $field_b, $post_type_b ) {
	// Evita recursão infinita (o save do post B também dispara o hook)
	if ( defined( 'MM_SYNCING_RELATIONSHIP' ) && MM_SYNCING_RELATIONSHIP ) {
		return;
	}

	// Recupera os IDs selecionados no campo do post A (pode ser array ou objeto ACF)
	$related_posts = get_field( $field_a, $post_a_id );

	// Normaliza para array de IDs inteiros (o ACF pode retornar objetos WP_Post)
	$new_related_ids = array();
	if ( ! empty( $related_posts ) ) {
		foreach ( (array) $related_posts as $item ) {
			if ( is_object( $item ) ) {
				$new_related_ids[] = (int) $item->ID;
			} elseif ( is_array( $item ) && isset( $item['ID'] ) ) {
				$new_related_ids[] = (int) $item['ID'];
			} elseif ( is_numeric( $item ) ) {
				$new_related_ids[] = (int) $item;
			}
		}
	}

	// -----------------------------------------------------------------------
	// Passo 1: Remove o post A de TODOS os posts B que o tinham referenciado
	// (cobre o caso de troca de anime pai de um episódio)
	// -----------------------------------------------------------------------
	$all_b_posts = get_posts( array(
		'post_type'      => $post_type_b,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => $field_b,
				'value'   => '"' . $post_a_id . '"',
				'compare' => 'LIKE',
			),
		),
	) );

	define( 'MM_SYNCING_RELATIONSHIP', true );

	foreach ( $all_b_posts as $b_id ) {
		$current_b_ids = get_field( $field_b, $b_id );
		if ( empty( $current_b_ids ) ) {
			continue;
		}

		$current_b_ids = array_map( function( $item ) {
			return is_object( $item ) ? (int) $item->ID : (int) $item;
		}, (array) $current_b_ids );

		// Remove o post A desta lista
		$updated_b_ids = array_values( array_diff( $current_b_ids, array( $post_a_id ) ) );
		update_field( $field_b, $updated_b_ids, $b_id );
	}

	// -----------------------------------------------------------------------
	// Passo 2: Adiciona o post A nos novos posts B selecionados
	// -----------------------------------------------------------------------
	foreach ( $new_related_ids as $b_id ) {
		$current_b_ids = get_field( $field_b, $b_id );
		if ( empty( $current_b_ids ) ) {
			$current_b_ids = array();
		}

		$current_b_ids = array_map( function( $item ) {
			return is_object( $item ) ? (int) $item->ID : (int) $item;
		}, (array) $current_b_ids );

		// Adiciona somente se ainda não estiver na lista
		if ( ! in_array( $post_a_id, $current_b_ids, true ) ) {
			$current_b_ids[] = $post_a_id;
			update_field( $field_b, $current_b_ids, $b_id );
		}
	}
}



// =========================================================================
// Hook: Sincroniza Review ↔ Anime
// =========================================================================

/**
 * Sincroniza review_anime_relacionado → anime_reviews
 *
 * @param int $post_id ID do post recém-salvo.
 */
function mm_sync_review_anime( $post_id ) {
	if ( get_post_type( $post_id ) !== 'review' ) {
		return;
	}

	mm_sync_bidirectional_relationship(
		$post_id,
		'review_anime_relacionado', // Campo da Review que aponta para o Anime
		'anime_reviews',            // Campo do Anime que lista as Reviews (oculto/hidden)
		'anime'
	);
}
add_action( 'acf/save_post', 'mm_sync_review_anime', 20 );
