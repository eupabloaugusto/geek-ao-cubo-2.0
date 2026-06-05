<?php
/**
 * Template Name: Catálogo de Personagens
 *
 * Modelo estático para a página do catálogo A-Z de personagens do Vibe Animes.
 * Integra filtros desktop (barra-filtros-personagens) e mobile (barra-filtros-personagens-mobile)
 * com a listagem paginada por letra (lista-personagens).
 *
 * URL: /personagens/
 * Query params: ?letra=M | ?ordem=populares | ?busca=naruto
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

<div class="archive-personagem">

	<!-- ── Hero Header do Diretório ───────────────────────────────── -->
	<header class="archive-personagem__hero" aria-labelledby="archive-personagem-titulo">
		<div class="archive-personagem__hero-inner">

			<!-- Breadcrumb -->
			<?php mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'vibe-animes' ), 'url' => home_url( '/' ) ),
					array( 'label' => get_the_title(), 'url' => '' ),
				),
			) ); ?>

			<h1 id="archive-personagem-titulo" class="archive-personagem__titulo">
				<?php echo esc_html( get_the_title() ); ?>
			</h1>
			<p class="archive-personagem__subtitulo">
				<?php
				$total_personagens = function_exists( 'mm_count_personagens_catalogo' )
					? mm_count_personagens_catalogo()
					: 0;
				if ( $total_personagens > 0 ) {
					echo esc_html( sprintf(
						_n( '%s personagem no catálogo', '%s personagens no catálogo', $total_personagens, 'vibe-animes' ),
						number_format_i18n( $total_personagens )
					) );
				}
				?>
			</p>

		</div>
	</header>

	<!-- ── Anúncio leaderboard ────────────────────────────────────── -->
	<div class="archive-personagem__anuncio">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'     => 'archive-personagem-leaderboard',
			'variacao' => 'leaderboard',
		) ); ?>
	</div>

	<!-- ── Barra de Filtros DESKTOP (oculta em mobile via CSS) ───── -->
	<div class="archive-personagem__filtros-desktop" aria-label="<?php esc_attr_e( 'Filtros do catálogo', 'vibe-animes' ); ?>">
		<?php mm_render_component( 'organisms', 'barra-filtros-personagens', array(
			'action_url' => $archive_url,
		) ); ?>
	</div>

	<!-- ── Barra de Filtros MOBILE (oculta em desktop via CSS) ────── -->
	<div class="archive-personagem__filtros-mobile">
		<?php mm_render_component( 'organisms', 'barra-filtros-personagens-mobile', array(
			'action_url' => $archive_url,
		) ); ?>
	</div>

	<!-- ── Listagem Alfabética ────────────────────────────────────── -->
	<main id="main-content" class="archive-personagem__lista">
		<?php mm_render_component( 'organisms', 'lista-personagens' ); ?>
	</main>

</div>

<?php
get_footer();

