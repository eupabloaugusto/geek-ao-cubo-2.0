<?php
/**
 * Template: Resultados de Busca (search.php) — v2-fix-blog-count
 *
 * Exibe resultados de busca em duas seções:
 * 1. Anime e Mangá (CPTs próprios, via mm_query_animes_por_letra)
 * 2. Artigos e Publicações (WP main query)
 *
 * @package vibe-animes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$search_query = get_search_query();
$archive_url  = home_url( '/' );

// DEBUG: verificar se busca está funcionando
error_log( 'search.php: search_query=[' . $search_query . '] is_search=[' . ( is_search() ? 'yes' : 'no' ) . ']' );

// #region agent log (debug-5342ce)
if ( function_exists( 'mm_dbg5342ce' ) ) {
	global $wp_query;
	mm_dbg5342ce( array(
		'hypothesisId' => 'H2',
		'location'     => 'search.php:before-custom-queries',
		'message'      => 'search template received query and main query vars',
		'data'         => array(
			'get_search_query' => (string) $search_query,
			'wp_query_s'       => isset( $wp_query ) ? (string) $wp_query->get( 's' ) : '',
			'post_type'        => isset( $wp_query ) ? $wp_query->get( 'post_type' ) : null,
			'found_posts'      => isset( $wp_query ) ? (int) $wp_query->found_posts : null,
			'query_vars'       => isset( $wp_query ) ? $wp_query->query_vars : null,
		),
	) );
}
// #endregion agent log (debug-5342ce)

$ordem_atual         = isset( $_GET['ordem'] ) ? sanitize_text_field( wp_unslash( $_GET['ordem'] ) ) : '';
$tipo_conteudo_atual = isset( $_GET['tipo_conteudo'] ) ? sanitize_text_field( wp_unslash( $_GET['tipo_conteudo'] ) ) : '';

$anime_ordem = $ordem_atual;
if ( 'recentes' === $ordem_atual ) {
	$anime_ordem = 'recente';
} elseif ( 'antigos' === $ordem_atual ) {
	$anime_ordem = 'antigo'; // Opcional se mm_query_animes suportar, senão é ignorado
}

// ── Busca em Anime ──────────────────────────────────────────────────────────
$anime_args = array(
	'busca'          => $search_query,
	'posts_per_page' => 6,
	'paged'          => 1,
);
if ( $anime_ordem ) $anime_args['filtro_ordem'] = $anime_ordem;
$anime_results = mm_query_animes_por_letra( '', $anime_args );

// ── Busca em Mangá ──────────────────────────────────────────────────────────
$manga_args = array(
	'busca'             => $search_query,
	'filtro_tipo_midia' => 'manga',
	'posts_per_page'    => 6,
	'paged'             => 1,
);
if ( $anime_ordem ) $manga_args['filtro_ordem'] = $anime_ordem;
$manga_results = mm_query_animes_por_letra( '', $manga_args );

// ── Contagem real de Artigos (apenas post_type = 'post') ────────────────────
$blog_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	's'              => $search_query,
	'posts_per_page' => 1,
	'no_found_rows'  => false,
	'fields'         => 'ids',
);
if ( 'antigos' === $ordem_atual ) {
	$blog_args['orderby'] = 'date';
	$blog_args['order']   = 'ASC';
} elseif ( 'alfabetica' === $ordem_atual ) {
	$blog_args['orderby'] = 'title';
	$blog_args['order']   = 'ASC';
} elseif ( 'populares' === $ordem_atual ) {
	$blog_args['orderby'] = 'comment_count';
	$blog_args['order']   = 'DESC';
}
$blog_query = new WP_Query( $blog_args );
$total_blog = $blog_query->found_posts;

$tipos_encontrados = array();
if ( $anime_results && $anime_results->have_posts() ) {
	$tipos_encontrados['anime'] = 'Animes';
}
if ( $manga_results && $manga_results->have_posts() ) {
	$tipos_encontrados['manga'] = 'Mangás';
}
if ( $total_blog > 0 ) {
	$tipos_encontrados['post'] = 'Artigos';
}

// #region agent log (debug-5342ce)
if ( function_exists( 'mm_dbg5342ce' ) ) {
	mm_dbg5342ce( array(
		'hypothesisId' => 'H3',
		'location'     => 'search.php:after-custom-queries',
		'message'      => 'search template totals after custom queries',
		'data'         => array(
			'search_query' => (string) $search_query,
			'anime_found'  => ( $anime_results instanceof WP_Query ) ? (int) $anime_results->found_posts : null,
			'manga_found'  => ( $manga_results instanceof WP_Query ) ? (int) $manga_results->found_posts : null,
			'blog_found'   => (int) $total_blog,
		),
	) );
}
// #endregion agent log (debug-5342ce)
?>

<!-- search.php v2-fix-blog-count loaded -->
<div class="archive-publicacoes" data-search-query="<?php echo esc_attr($search_query); ?>" data-is-search="<?php echo is_search()?'yes':'no'; ?>">

	<!-- ── Hero Header ────────────────────────────────────────────── -->
	<header class="archive-publicacoes__hero" aria-labelledby="search-titulo">
		<div class="archive-publicacoes__hero-inner">

			<?php mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'vibe-animes' ),  'url' => home_url( '/' ) ),
					array( 'label' => __( 'Busca', 'vibe-animes' ), 'url' => '' ),
				),
			) ); ?>

			<h1 id="search-titulo" class="archive-publicacoes__titulo">
				<?php printf( esc_html__( 'Resultados para: %s', 'vibe-animes' ), '<span>' . esc_html( $search_query ) . '</span>' ); ?>
			</h1>
			<p class="archive-publicacoes__subtitulo">
				<?php
				$total_anime = $anime_results ? $anime_results->found_posts : 0;
				$total_manga = $manga_results ? $manga_results->found_posts : 0;
				$total_geral = $total_anime + $total_manga + $total_blog;
				echo esc_html( sprintf(
					_n( 'Encontramos %s resultado.', 'Encontramos %s resultados.', $total_geral, 'vibe-animes' ),
					number_format_i18n( $total_geral )
				) );
				?>
			</p>

		</div>
	</header>

	<!-- ── Anúncio ───────────────────────────────────────────────── -->
	<div class="archive-publicacoes__anuncio">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'     => 'search-leaderboard',
			'variacao' => 'leaderboard',
		) ); ?>
	</div>

	<!-- ── Filtros Globais da Busca ──────────────────────────────── -->
	<?php if ( ! empty( $tipos_encontrados ) ) : ?>
	<div class="archive-publicacoes__filtros-desktop" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-400) var(--space-400) 0;">
		<?php mm_render_component( 'organisms', 'barra-filtros-busca', array( 'action_url' => $archive_url, 'tipos_encontrados' => $tipos_encontrados ) ); ?>
	</div>
	<div class="archive-publicacoes__filtros-mobile">
		<?php mm_render_component( 'organisms', 'barra-filtros-busca-mobile', array( 'action_url' => $archive_url, 'tipos_encontrados' => $tipos_encontrados ) ); ?>
	</div>
	<?php endif; ?>

	<?php if ( ( ! $tipo_conteudo_atual || 'anime' === $tipo_conteudo_atual ) && $anime_results && $anime_results->have_posts() ) : ?>
	<!-- ── Resultados: Anime ──────────────────────────────────────── -->
	<section class="archive-publicacoes__secao" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-500) var(--space-400) 0;">
		<h2 style="font-size: var(--text-md-size); font-weight: 700; margin-bottom: var(--space-300); color: var(--neutral-200);">
			<?php esc_html_e( '🎬 Animes', 'vibe-animes' ); ?>
			<span style="font-size: var(--text-xs-size); font-weight: 400; color: var(--neutral-400); margin-left: var(--space-200);">
				(<?php echo esc_html( $anime_results->found_posts ); ?>)
			</span>
		</h2>
		<div style="display: flex; flex-direction: column; gap: 0; border: 1px solid var(--color-border); border-radius: var(--border-radius-300); overflow: hidden; padding: 0 var(--space-400);">
			<?php while ( $anime_results->have_posts() ) : $anime_results->the_post(); ?>
				<?php
				$post_id     = get_the_ID();
				$mal_id      = (int) get_post_meta( $post_id, 'anime_id_mal', true );
				$jikan_cache = $mal_id ? get_transient( 'jikan_anime_full_' . $mal_id ) : false;
				$imagem      = get_the_post_thumbnail_url( $post_id, 'medium' ) ?: ( $jikan_cache ? ( $jikan_cache['images']['webp']['image_url'] ?? $jikan_cache['images']['jpg']['image_url'] ?? '' ) : '' );
				$sinopse     = $jikan_cache && ! empty( $jikan_cache['synopsis'] ) ? $jikan_cache['synopsis'] : get_the_excerpt();
				mm_render_component( 'molecules', 'card-catalogo', array(
					'titulo'     => get_the_title(),
					'url'        => get_permalink(),
					'imagem_url' => esc_url( $imagem ),
					'post_id'    => $post_id,
					'sinopse'    => $sinopse,
				) );
				?>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php if ( $anime_results->found_posts > 6 ) : ?>
			<p style="margin-top: var(--space-300); text-align: center;">
				<?php mm_render_component( 'atoms', 'btn-secondary', array(
					'label' => __( 'Ver todos os animes →', 'vibe-animes' ),
					'url'   => add_query_arg( 'busca', urlencode( $search_query ), get_post_type_archive_link( 'anime' ) ?: home_url('/catalogo/') )
				) ); ?>
			</p>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<?php if ( ( ! $tipo_conteudo_atual || 'manga' === $tipo_conteudo_atual ) && $manga_results && $manga_results->have_posts() ) : ?>
	<!-- ── Resultados: Mangá ──────────────────────────────────────── -->
	<section class="archive-publicacoes__secao" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-500) var(--space-400) 0;">
		<h2 style="font-size: var(--text-md-size); font-weight: 700; margin-bottom: var(--space-300); color: var(--neutral-200);">
			<?php esc_html_e( '📖 Mangás', 'vibe-animes' ); ?>
			<span style="font-size: var(--text-xs-size); font-weight: 400; color: var(--neutral-400); margin-left: var(--space-200);">
				(<?php echo esc_html( $manga_results->found_posts ); ?>)
			</span>
		</h2>
		<div style="display: flex; flex-direction: column; gap: 0; border: 1px solid var(--color-border); border-radius: var(--border-radius-300); overflow: hidden; padding: 0 var(--space-400);">
			<?php while ( $manga_results->have_posts() ) : $manga_results->the_post(); ?>
				<?php
				$post_id      = get_the_ID();
				$manga_mal_id = (int) get_post_meta( $post_id, 'manga_id_mal', true );
				$manga_cache  = $manga_mal_id ? get_transient( 'jikan_manga_full_' . $manga_mal_id ) : false;
				$imagem       = get_the_post_thumbnail_url( $post_id, 'medium' ) ?: ( $manga_cache ? ( $manga_cache['images']['webp']['image_url'] ?? $manga_cache['images']['jpg']['image_url'] ?? '' ) : '' );
				$sinopse      = $manga_cache && ! empty( $manga_cache['synopsis'] ) ? $manga_cache['synopsis'] : get_the_excerpt();
				mm_render_component( 'molecules', 'card-catalogo', array(
					'titulo'     => get_the_title(),
					'url'        => get_permalink(),
					'imagem_url' => esc_url( $imagem ),
					'post_id'    => $post_id,
					'sinopse'    => $sinopse,
				) );
				?>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ( ! $tipo_conteudo_atual || 'post' === $tipo_conteudo_atual ) && $total_blog > 0 ) : ?>
	<!-- ── Resultados: Artigos ───────────────────────────────────── -->
	<section class="archive-publicacoes__secao" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-500) var(--space-400) 0;">
		<h2 style="font-size: var(--text-md-size); font-weight: 700; margin-bottom: var(--space-300); color: var(--neutral-200);">
			<?php esc_html_e( '📰 Artigos', 'vibe-animes' ); ?>
			<span style="font-size: var(--text-xs-size); font-weight: 400; color: var(--neutral-400); margin-left: var(--space-200);">
				(<?php echo esc_html( $total_blog ); ?>)
			</span>
		</h2>
	</section>

	<!-- lista de artigos -->
	<main id="main-content" class="archive-publicacoes__lista">
		<?php mm_render_component( 'organisms', 'lista-publicacoes', array( 'use_main_query' => false, 'posts_per_page' => 12 ) ); ?>
	</main>
	<?php endif; ?>

	<?php if ( $total_geral === 0 ) : ?>
	<!-- Nenhum resultado em nenhuma categoria -->
	<div style="text-align: center; padding: var(--space-800) var(--space-400);">
		<p style="color: var(--neutral-400); font-size: var(--text-md-size);">
			<?php printf( esc_html__( 'Nenhum resultado encontrado para "%s".', 'vibe-animes' ), esc_html( $search_query ) ); ?>
		</p>
		<a href="<?php echo esc_url( home_url('/') ); ?>" class="btn btn--primary" style="margin-top: var(--space-400);">
			<?php esc_html_e( 'Voltar para o início', 'vibe-animes' ); ?>
		</a>
	</div>
	<?php endif; ?>

</div>

<?php
get_footer();


