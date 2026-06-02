<?php
/**
 * Template Name: Catálogo de Animes
 *
 * Modelo estático para a página do catálogo A-Z de animes do Geek ao Cubo.
 * Integra filtros desktop (barra-filtros) e mobile (barra-filtros-mobile)
 * com a listagem paginada por letra (lista-catalogo).
 *
 * URL: /animes/
 * Query params: ?letra=M | ?genero[]=acao | ?status_anime=em-exibicao | ?idioma=legendado | ?tipo_midia=serie | ?ordem=populares
 *
 * @package geek-ao-cubo
 * @since   4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$archive_url = esc_url( get_permalink() );
?>

<div class="archive-anime">

	<!-- ── Hero Header do Diretório ───────────────────────────────── -->
	<header class="archive-anime__hero" aria-labelledby="archive-anime-titulo">
		<div class="archive-anime__hero-inner">

			<!-- Breadcrumb -->
			<?php mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'geek-ao-cubo' ), 'url' => home_url( '/' ) ),
					array( 'label' => get_the_title(), 'url' => '' ),
				),
			) ); ?>

			<h1 id="archive-anime-titulo" class="archive-anime__titulo">
				<?php echo esc_html( get_the_title() ); ?>
			</h1>
			<p class="archive-anime__subtitulo">
				<?php
				$total = wp_count_posts( 'anime' );
				$total_pub = $total->publish ?? 0;
				if ( $total_pub > 0 ) {
					echo esc_html( sprintf(
						_n( '%s anime no catálogo', '%s animes no catálogo', $total_pub, 'geek-ao-cubo' ),
						number_format_i18n( $total_pub )
					) );
				}
				?>
			</p>

		</div>
	</header>

	<!-- ── Anúncio leaderboard ────────────────────────────────────── -->
	<div class="archive-anime__anuncio">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'     => 'archive-anime-leaderboard',
			'variacao' => 'leaderboard',
		) ); ?>
	</div>

	<!-- ── Barra de Filtros DESKTOP (oculta em mobile via CSS) ───── -->
	<div class="archive-anime__filtros-desktop" aria-label="<?php esc_attr_e( 'Filtros do catálogo', 'geek-ao-cubo' ); ?>">
		<?php mm_render_component( 'organisms', 'barra-filtros', array(
			'action_url' => $archive_url,
		) ); ?>
	</div>

	<!-- ── Barra de Filtros MOBILE (oculta em desktop via CSS) ────── -->
	<div class="archive-anime__filtros-mobile">
		<?php mm_render_component( 'organisms', 'barra-filtros-mobile', array(
			'action_url' => $archive_url,
		) ); ?>
	</div>

	<!-- ── Listagem Alfabética ────────────────────────────────────── -->
	<main id="main-content" class="archive-anime__lista">
		<?php mm_render_component( 'organisms', 'lista-catalogo' ); ?>
	</main>

</div>

<?php
get_footer();
