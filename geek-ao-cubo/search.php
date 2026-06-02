<?php
/**
 * Template: Resultados de Busca (search.php)
 *
 * Exibe resultados de busca em duas seções:
 * 1. Anime e Mangá (CPTs próprios, via mm_query_animes_por_letra)
 * 2. Artigos e Publicações (WP main query)
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$search_query = get_search_query();
$archive_url  = home_url( '/' );

// ── Busca em Anime ──────────────────────────────────────────────────────────
$anime_results = mm_query_animes_por_letra( '', array(
	'busca'          => $search_query,
	'posts_per_page' => 6,
	'paged'          => 1,
) );

// ── Busca em Mangá (usa filtro_tipo_midia que já troca o post_type) ──────────
$manga_results = mm_query_animes_por_letra( '', array(
	'busca'             => $search_query,
	'filtro_tipo_midia' => 'manga',
	'posts_per_page'    => 6,
	'paged'             => 1,
) );

global $wp_query;
$total_blog = $wp_query->found_posts;
?>

<div class="archive-publicacoes">

	<!-- ── Hero Header ────────────────────────────────────────────── -->
	<header class="archive-publicacoes__hero" aria-labelledby="search-titulo">
		<div class="archive-publicacoes__hero-inner">

			<?php mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'geek-ao-cubo' ),  'url' => home_url( '/' ) ),
					array( 'label' => __( 'Busca', 'geek-ao-cubo' ), 'url' => '' ),
				),
			) ); ?>

			<h1 id="search-titulo" class="archive-publicacoes__titulo">
				<?php printf( esc_html__( 'Resultados para: %s', 'geek-ao-cubo' ), '<span>' . esc_html( $search_query ) . '</span>' ); ?>
			</h1>
			<p class="archive-publicacoes__subtitulo">
				<?php
				$total_anime = $anime_results ? $anime_results->found_posts : 0;
				$total_manga = $manga_results ? $manga_results->found_posts : 0;
				$total_geral = $total_anime + $total_manga + $total_blog;
				echo esc_html( sprintf(
					_n( 'Encontramos %s resultado.', 'Encontramos %s resultados.', $total_geral, 'geek-ao-cubo' ),
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

	<?php if ( $anime_results && $anime_results->have_posts() ) : ?>
	<!-- ── Resultados: Anime ──────────────────────────────────────── -->
	<section class="archive-publicacoes__secao" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-500) var(--space-400) 0;">
		<h2 style="font-size: var(--text-md-size); font-weight: 700; margin-bottom: var(--space-300); color: var(--neutral-200);">
			<?php esc_html_e( '🎬 Animes', 'geek-ao-cubo' ); ?>
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
				<a href="<?php echo esc_url( add_query_arg( 'busca', urlencode( $search_query ), get_post_type_archive_link( 'anime' ) ?: home_url('/catalogo/') ) ); ?>" class="btn btn--secondary">
					<?php esc_html_e( 'Ver todos os animes →', 'geek-ao-cubo' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<?php if ( $manga_results && $manga_results->have_posts() ) : ?>
	<!-- ── Resultados: Mangá ──────────────────────────────────────── -->
	<section class="archive-publicacoes__secao" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-500) var(--space-400) 0;">
		<h2 style="font-size: var(--text-md-size); font-weight: 700; margin-bottom: var(--space-300); color: var(--neutral-200);">
			<?php esc_html_e( '📖 Mangás', 'geek-ao-cubo' ); ?>
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

	<?php if ( $total_blog > 0 ) : ?>
	<!-- ── Resultados: Artigos ───────────────────────────────────── -->
	<section class="archive-publicacoes__secao" style="max-width: var(--container-max); margin-inline: auto; padding: var(--space-500) var(--space-400) 0;">
		<h2 style="font-size: var(--text-md-size); font-weight: 700; margin-bottom: var(--space-300); color: var(--neutral-200);">
			<?php esc_html_e( '📰 Artigos', 'geek-ao-cubo' ); ?>
			<span style="font-size: var(--text-xs-size); font-weight: 400; color: var(--neutral-400); margin-left: var(--space-200);">
				(<?php echo esc_html( $total_blog ); ?>)
			</span>
		</h2>
	</section>

	<!-- Filtros e lista de artigos -->
	<div class="archive-publicacoes__filtros-desktop">
		<?php mm_render_component( 'organisms', 'barra-filtros-publicacoes', array( 'action_url' => $archive_url ) ); ?>
	</div>
	<div class="archive-publicacoes__filtros-mobile">
		<?php mm_render_component( 'organisms', 'barra-filtros-publicacoes-mobile', array( 'action_url' => $archive_url ) ); ?>
	</div>
	<main id="main-content" class="archive-publicacoes__lista">
		<?php mm_render_component( 'organisms', 'lista-publicacoes', array( 'use_main_query' => false, 'posts_per_page' => 12 ) ); ?>
	</main>
	<?php endif; ?>

	<?php if ( $total_geral === 0 ) : ?>
	<!-- Nenhum resultado em nenhuma categoria -->
	<div style="text-align: center; padding: var(--space-800) var(--space-400);">
		<p style="color: var(--neutral-400); font-size: var(--text-md-size);">
			<?php printf( esc_html__( 'Nenhum resultado encontrado para "%s".', 'geek-ao-cubo' ), esc_html( $search_query ) ); ?>
		</p>
		<a href="<?php echo esc_url( home_url('/') ); ?>" class="btn btn--primary" style="margin-top: var(--space-400);">
			<?php esc_html_e( 'Voltar para o início', 'geek-ao-cubo' ); ?>
		</a>
	</div>
	<?php endif; ?>

</div>

<?php
get_footer();

