<?php
/**
 * Template Name: Página de Publicações (Blog)
 *
 * Modelo estático para a página de publicações do portal Geek ao Cubo.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$archive_url = esc_url( get_permalink() );
?>

<div class="archive-publicacoes">

	<!-- ── Hero Header do Diretório ───────────────────────────────── -->
	<header class="archive-publicacoes__hero" aria-labelledby="archive-publicacoes-titulo">
		<div class="archive-publicacoes__hero-inner">

			<!-- Breadcrumb -->
			<?php mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'geek-ao-cubo' ), 'url' => home_url( '/' ) ),
					array( 'label' => get_the_title(), 'url' => '' ),
				),
			) ); ?>

			<h1 id="archive-publicacoes-titulo" class="archive-publicacoes__titulo">
				<?php echo esc_html( get_the_title() ); ?>
			</h1>
			<p class="archive-publicacoes__subtitulo">
				<?php
				$total = wp_count_posts( 'post' );
				$total_pub = $total->publish ?? 0;
				if ( $total_pub > 0 ) {
					echo esc_html( sprintf(
						_n( '%s publicação', '%s publicações', $total_pub, 'geek-ao-cubo' ),
						number_format_i18n( $total_pub )
					) );
				}
				?>
			</p>

		</div>
	</header>

	<!-- ── Anúncio leaderboard ────────────────────────────────────── -->
	<div class="archive-publicacoes__anuncio">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'     => 'archive-publicacoes-leaderboard',
			'variacao' => 'leaderboard',
		) ); ?>
	</div>

	<!-- ── Barra de Filtros DESKTOP (oculta em mobile via CSS) ───── -->
	<div class="archive-publicacoes__filtros-desktop" aria-label="<?php esc_attr_e( 'Filtros de publicações', 'geek-ao-cubo' ); ?>">
		<?php mm_render_component( 'organisms', 'barra-filtros-publicacoes', array(
			'action_url' => $archive_url,
		) ); ?>
	</div>

	<!-- ── Barra de Filtros MOBILE (oculta em desktop via CSS) ────── -->
	<div class="archive-publicacoes__filtros-mobile">
		<?php mm_render_component( 'organisms', 'barra-filtros-publicacoes-mobile', array(
			'action_url' => $archive_url,
		) ); ?>
	</div>

	<!-- ── Listagem Cronológica ────────────────────────────────────── -->
	<main id="main-content" class="archive-publicacoes__lista">
		<?php mm_render_component( 'organisms', 'lista-publicacoes' ); ?>
	</main>

</div>

<?php
get_footer();
