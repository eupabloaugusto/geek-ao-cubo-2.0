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

			<?php
			$sel_tipo_midia = isset( $_GET['tipo_midia'] ) ? sanitize_key( wp_unslash( $_GET['tipo_midia'] ) ) : '';
			$post_name = get_post() ? get_post()->post_name : '';
			$is_manga = ( 'manga' === $sel_tipo_midia || strpos( $post_name, 'manga' ) !== false );
			$catalogo_titulo = $is_manga ? __( 'Catálogo de Mangás', 'geek-ao-cubo' ) : get_the_title();
			?>
			<!-- Breadcrumb -->
			<?php mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'geek-ao-cubo' ), 'url' => home_url( '/' ) ),
					array( 'label' => $catalogo_titulo, 'url' => '' ),
				),
			) ); ?>

			<h1 id="archive-anime-titulo" class="archive-anime__titulo">
				<?php echo esc_html( $catalogo_titulo ); ?>
			</h1>
			<p class="archive-anime__subtitulo">
				<?php
				$total_post_type = $is_manga ? 'manga' : 'anime';
				$total = wp_count_posts( $total_post_type );
				$total_pub = $total->publish ?? 0;
				if ( $total_pub > 0 ) {
					if ( $is_manga ) {
						echo esc_html( sprintf(
							_n( '%s mangá no catálogo', '%s mangás no catálogo', $total_pub, 'geek-ao-cubo' ),
							number_format_i18n( $total_pub )
						) );
					} else {
						echo esc_html( sprintf(
							_n( '%s anime no catálogo', '%s animes no catálogo', $total_pub, 'geek-ao-cubo' ),
							number_format_i18n( $total_pub )
						) );
					}
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
