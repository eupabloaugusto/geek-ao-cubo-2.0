<?php
/**
 * Organism: Grid Trabalhos de Voz (grid-trabalhos-voz)
 *
 * Exibe um grid CSS com todos os papéis (vozes) que o dublador realizou.
 *
 * @package geek-ao-cubo
 *
 * @param array $voices Array de vozes retornado do endpoint /people/{id}/full do Jikan.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$voices = isset( $args['voices'] ) ? $args['voices'] : array();

if ( empty( $voices ) ) {
	return;
}

// Filtro Ativo
$ordem_ativa = isset( $_GET['ordem'] ) ? sanitize_text_field( $_GET['ordem'] ) : 'relevancia';

// Lógica de ordenação
if ( 'alfabetica' === $ordem_ativa ) {
	// A-Z pelo título do anime
	usort( $voices, function( $a, $b ) {
		$title_a = $a['anime']['title'] ?? '';
		$title_b = $b['anime']['title'] ?? '';
		return strcasecmp( $title_a, $title_b );
	} );
	$sorted_voices = $voices;
	
} elseif ( 'recentes' === $ordem_ativa ) {
	// Array bruta (Jikan já ordena cronologicamente por ID)
	$sorted_voices = $voices;
	
} else {
	// Padrão: Relevância (Main > Supporting) mantendo cronologia dentro dos grupos
	$main_roles = array();
	$supporting_roles = array();
	
	foreach ( $voices as $voice ) {
		if ( ( $voice['role'] ?? '' ) === 'Main' ) {
			$main_roles[] = $voice;
		} else {
			$supporting_roles[] = $voice;
		}
	}
	
	$sorted_voices = array_merge( $main_roles, $supporting_roles );
}

// Lógica de Paginação (Melhor estratégia para SEO: Renderização via PHP)
// 10 linhas no desktop (10 x 3 colunas = 30 itens)
// 10 itens no mobile
$items_per_page = wp_is_mobile() ? 10 : 30;

$current_page = isset( $_GET['pg'] ) ? max( 1, (int) $_GET['pg'] ) : 1;
$total_items  = count( $sorted_voices );
$total_pages  = ceil( $total_items / $items_per_page );

if ( $current_page > $total_pages && $total_pages > 0 ) {
	$current_page = $total_pages;
}

$offset       = ( $current_page - 1 ) * $items_per_page;
$paged_voices = array_slice( $sorted_voices, $offset, $items_per_page );

// URL base para os links de paginação (remove a query string 'pg' atual)
$base_url = remove_query_arg( 'pg' );

wp_enqueue_style( 'geek-ao-cubo-grid-trabalhos-voz', get_template_directory_uri() . '/organisms/grid-trabalhos-voz.css', array(), '1.0' );
?>
<section class="grid-trabalhos-voz js-ajax-container" id="trabalhos-voz">
	<div class="grid-trabalhos-voz__ad-top" style="margin-bottom: var(--space-600); width: 100%; text-align: center;">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'     => 'dublador-trabalhos-top-banner',
			'variacao' => 'banner',
		) ); ?>
	</div>

	<div class="grid-trabalhos-voz__inner js-ajax-scroll-target">
		
		<?php
		mm_render_component( 'organisms', 'secao-titulo', array(
			'titulo' => __( 'Trabalhos Notáveis', 'geek-ao-cubo' ),
		) );
		
		// Renderiza os filtros (Pills)
		mm_render_component( 'molecules', 'filtro-pills', array(
			'options' => array(
				'relevancia' => __( 'Relevância', 'geek-ao-cubo' ),
				'recentes'   => __( 'Mais Recentes', 'geek-ao-cubo' ),
				'alfabetica' => __( 'A-Z', 'geek-ao-cubo' ),
			),
			'active_key' => $ordem_ativa,
		) );
		?>

		<div class="grid-trabalhos-voz__grid js-ajax-replace">
		<?php
		// Frequência de anúncios:
		// Mobile: 1 a cada 5 cards
		// Desktop: 1 a cada 9 cards (3 linhas × 3 colunas) — a posição no grid rotaciona automaticamente entre as 3 colunas
		$ad_frequency = wp_is_mobile() ? 5 : 9;
		$card_index   = 0;

		foreach ( $paged_voices as $voice ) :
			$character = $voice['character'] ?? array();
			$anime     = $voice['anime'] ?? array();
			
			// Ignora se vier corrompido
			if ( empty( $character ) || empty( $anime ) ) continue;

			$nome_personagem = $character['name'] ?? '';
			$papel           = $voice['role'] ?? '';
			$foto_personagem = $character['images']['webp']['image_url'] ?? ( $character['images']['jpg']['image_url'] ?? '' );
			
			$nome_anime = $anime['title'] ?? '';
			$foto_anime = $anime['images']['webp']['image_url'] ?? ( $anime['images']['jpg']['image_url'] ?? '' );
			
			$anime_mal_id = isset( $anime['mal_id'] ) ? (int) $anime['mal_id'] : 0;
			$local_anime  = mm_get_local_anime_by_mal_id( $anime_mal_id );
			
			if ( $local_anime && ! empty( $local_anime['url'] ) ) {
				$url_anime = $local_anime['url'];
			} else {
				$url_anime = home_url( '/?s=' . urlencode( $nome_anime ) );
			}
			
			mm_render_component( 'molecules', 'card-dublagem', array(
				'nome_personagem' => $nome_personagem,
				'papel'           => $papel,
				'foto_personagem' => $foto_personagem,
				'nome_anime'      => $nome_anime,
				'foto_anime'      => $foto_anime,
				'url_anime'       => $url_anime
			) );

			$card_index++;

			// Injeta anúncio camuflado após atingir a frequência definida
			if ( $card_index % $ad_frequency === 0 ) {
				echo '<div class="grid-trabalhos-voz__ad-cell">';
				mm_render_component( 'atoms', 'anuncio-adsense', array(
					'slot'     => 'dublador-grid-inline-' . $card_index,
					'variacao' => 'in-feed',
					'class'    => 'anuncio-adsense--grid-dubladores',
				) );
				echo '</div>';
			}

		endforeach; ?>
	</div>

	<!-- Paginação -->
	<div class="grid-trabalhos-voz__paginacao">
		<?php
		if ( $total_pages > 1 ) {
			mm_render_component( 'molecules', 'pagination', array(
				'max_num_pages' => $total_pages,
				'current_page'  => $current_page,
				'base'          => add_query_arg( 'pg', '%#%', $base_url ),
				'format'        => '?pg=%#%',
			) );
		}
		?>
	</div>
</section>
