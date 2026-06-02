<?php
/**
 * Admin Columns — Colunas Personalizadas nos CPTs
 *
 * Substitui as colunas padrão do wp-admin nas listagens de cada CPT
 * por colunas com informações úteis e acionáveis.
 *
 * Estrutura das colunas por CPT:
 *  - Anime:    Capa | Título | ID MAL | Nota | Gêneros | Status | Data
 *  - Episódio: Número | Título | Anime Pai | Data de Lançamento | Filler | Data
 *  - Temporada: Período | Ano | Qtd de Animes | Destaque | Data
 *  - Review:   Anime | Nota | Recomenda | Spoilers | Autor | Data
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// =========================================================================
// ANIME — Colunas
// =========================================================================

function mm_anime_admin_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'],
		'anime_capa'   => 'Capa',
		'title'        => 'Título',
		'anime_id_mal' => 'ID MAL',
		'anime_nota'   => 'Nota ⭐',
		'taxonomy-genero'          => 'Gêneros',
		'taxonomy-status_exibicao' => 'Status',
		'date'         => 'Publicado',
	);
}
add_filter( 'manage_anime_posts_columns', 'mm_anime_admin_columns' );


function mm_anime_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'anime_capa':
			$thumb = get_the_post_thumbnail( $post_id, array( 48, 68 ) );
			if ( $thumb ) {
				echo '<div style="line-height:0">' . $thumb . '</div>';
			} else {
				$mal_id = (int) get_field( 'anime_id_mal', $post_id );
				$jikan_data = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();
				$capa_url = $jikan_data['images']['webp']['image_url'] ?? ( $jikan_data['images']['jpg']['image_url'] ?? '' );
				
				if ( $capa_url ) {
					echo '<img src="' . esc_url( $capa_url ) . '" width="48" height="68" alt="" loading="lazy" style="object-fit:cover;border-radius:4px">';
				} else {
					echo '<span style="color:#999;font-size:11px">—</span>';
				}
			}
			break;

		case 'anime_id_mal':
			$id = get_field( 'anime_id_mal', $post_id );
			if ( $id ) {
				echo '<a href="https://myanimelist.net/anime/' . intval( $id ) . '" target="_blank" rel="noopener noreferrer" title="Ver no MAL">#' . intval( $id ) . ' ↗</a>';
			} else {
				echo '<span style="color:#c00">⚠ Sem ID</span>';
			}
			break;

		case 'anime_nota':
			$mal_id = (int) get_field( 'anime_id_mal', $post_id );
			$jikan_data = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();
			$nota = $jikan_data['score'] ?? '';
			
			if ( $nota ) {
				$color = $nota >= 8 ? '#2d9e6b' : ( $nota >= 6 ? '#e0941a' : '#c0392b' );
				echo '<strong style="color:' . $color . '">' . number_format( (float) $nota, 2 ) . '</strong>';
			} else {
				echo '<span style="color:#999">N/A</span>';
			}
			break;
	}
}
add_action( 'manage_anime_posts_custom_column', 'mm_anime_admin_column_content', 10, 2 );


function mm_anime_admin_sortable_columns( $columns ) {
	$columns['anime_id_mal'] = 'anime_id_mal';
	$columns['anime_nota']   = 'anime_nota_mal';
	return $columns;
}
add_filter( 'manage_edit-anime_sortable_columns', 'mm_anime_admin_sortable_columns' );


// =========================================================================
// MANGÁ — Colunas
// =========================================================================

function mm_manga_admin_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'],
		'manga_capa'   => 'Capa',
		'title'        => 'Título',
		'manga_id_mal' => 'ID MAL',
		'manga_nota'   => 'Nota ⭐',
		'taxonomy-genero'       => 'Gêneros',
		'taxonomy-status_manga' => 'Status',
		'date'         => 'Publicado',
	);
}
add_filter( 'manage_manga_posts_columns', 'mm_manga_admin_columns' );


function mm_manga_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'manga_capa':
			$thumb = get_the_post_thumbnail( $post_id, array( 48, 68 ) );
			if ( $thumb ) {
				echo '<div style="line-height:0">' . $thumb . '</div>';
			} else {
				$mal_id = (int) get_field( 'manga_id_mal', $post_id );
				$jikan_data = $mal_id > 0 ? Jikan_API::get_manga_full( $mal_id ) : array();
				$capa_url = $jikan_data['images']['webp']['image_url'] ?? ( $jikan_data['images']['jpg']['image_url'] ?? '' );
				
				if ( $capa_url ) {
					echo '<img src="' . esc_url( $capa_url ) . '" width="48" height="68" alt="" loading="lazy" style="object-fit:cover;border-radius:4px">';
				} else {
					echo '<span style="color:#999;font-size:11px">—</span>';
				}
			}
			break;

		case 'manga_id_mal':
			$id = get_field( 'manga_id_mal', $post_id );
			if ( $id ) {
				echo '<a href="https://myanimelist.net/manga/' . intval( $id ) . '" target="_blank" rel="noopener noreferrer" title="Ver no MAL">#' . intval( $id ) . ' ↗</a>';
			} else {
				echo '<span style="color:#c00">⚠ Sem ID</span>';
			}
			break;

		case 'manga_nota':
			$mal_id = (int) get_field( 'manga_id_mal', $post_id );
			$jikan_data = $mal_id > 0 ? Jikan_API::get_manga_full( $mal_id ) : array();
			$nota = $jikan_data['score'] ?? '';
			
			if ( $nota ) {
				$color = $nota >= 8 ? '#2d9e6b' : ( $nota >= 6 ? '#e0941a' : '#c0392b' );
				echo '<strong style="color:' . $color . '">' . number_format( (float) $nota, 2 ) . '</strong>';
			} else {
				echo '<span style="color:#999">N/A</span>';
			}
			break;
	}
}
add_action( 'manage_manga_posts_custom_column', 'mm_manga_admin_column_content', 10, 2 );

function mm_manga_admin_sortable_columns( $columns ) {
	$columns['manga_id_mal'] = 'manga_id_mal';
	$columns['manga_nota']   = 'manga_nota_mal';
	return $columns;
}
add_filter( 'manage_edit-manga_sortable_columns', 'mm_manga_admin_sortable_columns' );


// =========================================================================
// EPISÓDIO — Colunas
// =========================================================================

function mm_episodio_admin_columns( $columns ) {
	return array(
		'cb'               => $columns['cb'],
		'ep_numero'        => 'Nº',
		'title'            => 'Título',
		'ep_anime_pai'     => 'Anime',
		'ep_lancamento'    => 'Lançamento',
		'ep_filler_badge'  => 'Filler?',
		'date'             => 'Publicado',
	);
}
add_filter( 'manage_episodio_posts_columns', 'mm_episodio_admin_columns' );


function mm_episodio_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'ep_numero':
			$num = get_field( 'ep_numero', $post_id );
			echo $num ? '<strong>#' . intval( $num ) . '</strong>' : '—';
			break;

		case 'ep_anime_pai':
			$anime = get_field( 'ep_anime_relacionado', $post_id );
			if ( ! empty( $anime ) ) {
				$anime_obj = is_array( $anime ) ? $anime[0] : $anime;
				$anime_id  = is_object( $anime_obj ) ? $anime_obj->ID : (int) $anime_obj;
				$anime_title = get_the_title( $anime_id );
				$edit_link   = get_edit_post_link( $anime_id );
				echo '<a href="' . esc_url( $edit_link ) . '">' . esc_html( $anime_title ) . '</a>';
			} else {
				echo '<span style="color:#c00">⚠ Sem anime</span>';
			}
			break;

		case 'ep_lancamento':
			$data = get_field( 'ep_data_lancamento', $post_id );
			echo $data ? esc_html( date_i18n( 'd/m/Y H:i', strtotime( $data ) ) ) : '—';
			break;

		case 'ep_filler_badge':
			$filler = get_field( 'ep_filler', $post_id );
			echo $filler
				? '<span style="background:#e0941a;color:#fff;padding:2px 6px;border-radius:3px;font-size:11px">FILLER</span>'
				: '<span style="color:#999">—</span>';
			break;
	}
}
add_action( 'manage_episodio_posts_custom_column', 'mm_episodio_admin_column_content', 10, 2 );


function mm_episodio_admin_sortable_columns( $columns ) {
	$columns['ep_numero']     = 'ep_numero';
	$columns['ep_lancamento'] = 'ep_data_lancamento';
	return $columns;
}
add_filter( 'manage_edit-episodio_sortable_columns', 'mm_episodio_admin_sortable_columns' );


// =========================================================================
// TEMPORADA — Colunas
// =========================================================================

function mm_temporada_admin_columns( $columns ) {
	return array(
		'cb'              => $columns['cb'],
		'title'           => 'Temporada',
		'temp_periodo_col'=> 'Período',
		'temp_ano_col'    => 'Ano',
		'temp_qtd_animes' => 'Animes',
		'date'            => 'Publicado',
	);
}
add_filter( 'manage_temporada_posts_columns', 'mm_temporada_admin_columns' );


function mm_temporada_admin_column_content( $column, $post_id ) {
	$periodos = array(
		'inverno'   => '❄️ Inverno',
		'primavera' => '🌸 Primavera',
		'verao'     => '☀️ Verão',
		'outono'    => '🍂 Outono',
	);

	switch ( $column ) {
		case 'temp_periodo_col':
			$periodo = get_field( 'temp_periodo', $post_id );
			echo isset( $periodos[ $periodo ] ) ? esc_html( $periodos[ $periodo ] ) : '—';
			break;

		case 'temp_ano_col':
			$ano = get_field( 'temp_ano', $post_id );
			echo $ano ? '<strong>' . intval( $ano ) . '</strong>' : '—';
			break;

		case 'temp_qtd_animes':
			$animes = get_field( 'temp_animes', $post_id );
			$count  = ! empty( $animes ) ? count( (array) $animes ) : 0;
			echo '<strong>' . intval( $count ) . '</strong> anime' . ( 1 !== $count ? 's' : '' );
			break;
	}
}
add_action( 'manage_temporada_posts_custom_column', 'mm_temporada_admin_column_content', 10, 2 );


// =========================================================================
// REVIEW — Colunas
// =========================================================================

function mm_review_admin_columns( $columns ) {
	return array(
		'cb'               => $columns['cb'],
		'title'            => 'Título da Review',
		'review_anime'     => 'Anime',
		'review_nota_col'  => 'Nota',
		'review_rec'       => 'Recomenda?',
		'review_spoiler'   => 'Spoiler',
		'author'           => 'Autor',
		'date'             => 'Publicado',
	);
}
add_filter( 'manage_review_posts_columns', 'mm_review_admin_columns' );


function mm_review_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'review_anime':
			$anime = get_field( 'review_anime_relacionado', $post_id );
			if ( ! empty( $anime ) ) {
				$anime_obj = is_array( $anime ) ? $anime[0] : $anime;
				$anime_id  = is_object( $anime_obj ) ? $anime_obj->ID : (int) $anime_obj;
				echo '<a href="' . esc_url( get_edit_post_link( $anime_id ) ) . '">' . esc_html( get_the_title( $anime_id ) ) . '</a>';
			} else {
				echo '—';
			}
			break;

		case 'review_nota_col':
			$nota  = get_field( 'review_nota', $post_id );
			if ( '' !== $nota && null !== $nota ) {
				$color = (float) $nota >= 8 ? '#2d9e6b' : ( (float) $nota >= 6 ? '#e0941a' : '#c0392b' );
				echo '<strong style="color:' . $color . '">' . number_format( (float) $nota, 1 ) . '/10</strong>';
			} else {
				echo '—';
			}
			break;

		case 'review_rec':
			$rec = get_field( 'review_recomenda', $post_id );
			$map = array( 'sim' => '✅ Sim', 'depende' => '⚠️ Depende', 'nao' => '❌ Não' );
			echo isset( $map[ $rec ] ) ? $map[ $rec ] : '—';
			break;

		case 'review_spoiler':
			$spoiler = get_field( 'review_spoilers', $post_id );
			echo $spoiler
				? '<span style="background:#c0392b;color:#fff;padding:2px 6px;border-radius:3px;font-size:11px">SPOILER</span>'
				: '<span style="color:#999">Não</span>';
			break;
	}
}
add_action( 'manage_review_posts_custom_column', 'mm_review_admin_column_content', 10, 2 );


function mm_review_admin_sortable_columns( $columns ) {
	$columns['review_nota_col'] = 'review_nota';
	return $columns;
}
add_filter( 'manage_edit-review_sortable_columns', 'mm_review_admin_sortable_columns' );


// =========================================================================
// METABOX RÁPIDA — Nota colorida no topo das páginas de edição de Anime
// Aparece na tela de edição do post, abaixo do título
// =========================================================================

function mm_anime_edit_overview_metabox() {
	add_meta_box(
		'mm-anime-overview',
		'📊 Dados do MAL',
		'mm_anime_overview_metabox_content',
		'anime',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'mm_anime_edit_overview_metabox' );


function mm_anime_overview_metabox_content( $post ) {
	$id_mal     = get_field( 'anime_id_mal', $post->ID );
	$jikan_data = $id_mal > 0 ? Jikan_API::get_anime_full( $id_mal ) : array();

	$nota       = $jikan_data['score'] ?? '';
	$ranking    = $jikan_data['rank'] ?? '';
	$membros    = $jikan_data['members'] ?? '';
	$studio     = ! empty( $jikan_data['studios'] ) ? $jikan_data['studios'][0]['name'] : '';
	$ep_count   = mm_query_episodios_do_anime( $post->ID );
	$review_count = mm_query_reviews_do_anime( $post->ID, -1 );

	echo '<ul style="margin:0;padding:0;list-style:none;font-size:13px;line-height:2">';

	if ( $id_mal ) {
		echo '<li>🔗 <a href="https://myanimelist.net/anime/' . intval( $id_mal ) . '" target="_blank" rel="noopener">Ver no MAL ↗</a></li>';
	}
	if ( $nota ) {
		echo '<li>⭐ Nota: <strong>' . number_format( (float) $nota, 2 ) . '</strong></li>';
	}
	if ( $ranking ) {
		echo '<li>🏆 Rank: <strong>#' . intval( $ranking ) . '</strong></li>';
	}
	if ( $membros ) {
		echo '<li>👥 Membros: <strong>' . number_format( (int) $membros ) . '</strong></li>';
	}
	if ( $studio ) {
		echo '<li>🎬 Estúdio: ' . esc_html( $studio ) . '</li>';
	}
	echo '<li>📺 Episódios publicados: <strong>' . intval( $ep_count->found_posts ) . '</strong></li>';
	echo '<li>✍️ Reviews publicadas: <strong>' . intval( $review_count->found_posts ) . '</strong></li>';
	echo '</ul>';

	wp_reset_postdata();
}
