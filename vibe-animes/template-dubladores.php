<?php
/**
 * Template Name: Catálogo de Dubladores
 *
 * Modelo estático para a página do catálogo A-Z de dubladores do Vibe Animes.
 * Integra filtros desktop (barra-filtros-dubladores) e mobile (barra-filtros-dubladores-mobile)
 * com a listagem paginada por letra (lista-dubladores).
 *
 * URL: /dubladores/
 * Query params: ?letra=M | ?ordem=populares | ?busca=wendel
 *
 * @package vibe-animes
 * @since   4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$archive_url = esc_url( get_permalink() );
?>

<div class="archive-dublador">

	<!-- ── Hero Header do Diretório ───────────────────────────────── -->
	<header class="archive-dublador__hero" aria-labelledby="archive-dublador-titulo">
		<div class="archive-dublador__hero-inner">

			<!-- Breadcrumb -->
			<?php mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'vibe-animes' ), 'url' => home_url( '/' ) ),
					array( 'label' => get_the_title(), 'url' => '' ),
				),
			) ); ?>

			<h1 id="archive-dublador-titulo" class="archive-dublador__titulo">
				<?php echo esc_html( get_the_title() ); ?>
			</h1>
			<p class="archive-dublador__subtitulo">
				<?php
				$total_dubladores = function_exists( 'mm_count_dubladores_catalogo' )
					? mm_count_dubladores_catalogo()
					: 0;
				if ( $total_dubladores > 0 ) {
					echo esc_html( sprintf(
						_n( '%s dublador no catálogo', '%s dubladores no catálogo', $total_dubladores, 'vibe-animes' ),
						number_format_i18n( $total_dubladores )
					) );
				}
				?>
			</p>

		</div>
	</header>

	<!-- ── Anúncio leaderboard ────────────────────────────────────── -->
	<div class="archive-dublador__anuncio">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'     => 'archive-dublador-leaderboard',
			'variacao' => 'leaderboard',
		) ); ?>
	</div>

	<!-- ── Barra de Filtros DESKTOP (oculta em mobile via CSS) ───── -->
	<div class="archive-dublador__filtros-desktop" aria-label="<?php esc_attr_e( 'Filtros do catálogo', 'vibe-animes' ); ?>">
		<?php mm_render_component( 'organisms', 'barra-filtros-dubladores', array(
			'action_url' => $archive_url,
		) ); ?>
	</div>

	<!-- ── Barra de Filtros MOBILE (oculta em desktop via CSS) ────── -->
	<div class="archive-dublador__filtros-mobile">
		<?php mm_render_component( 'organisms', 'barra-filtros-dubladores-mobile', array(
			'action_url' => $archive_url,
		) ); ?>
	</div>

	<!-- ── Listagem Alfabética ────────────────────────────────────── -->
	<main id="main-content" class="archive-dublador__lista">
		<?php mm_render_component( 'organisms', 'lista-dubladores' ); ?>
	</main>

</div>

<?php
get_footer();

