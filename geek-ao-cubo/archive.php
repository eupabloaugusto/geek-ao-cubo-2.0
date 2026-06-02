<?php
/**
 * Template: Arquivo Genérico (archive.php)
 *
 * Utilizado pelo WordPress para exibir listagens de Categorias, Tags, Autores, etc.
 * Reutiliza a grade e paginação do catálogo de publicações.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Obter URL base do arquivo atual para o formulário de busca
global $wp;
$archive_url = home_url( $wp->request );
?>

<div class="archive-publicacoes">

	<!-- ── Hero Header do Diretório ───────────────────────────────── -->
	<header class="archive-publicacoes__hero" aria-labelledby="archive-publicacoes-titulo">
		<div class="archive-publicacoes__hero-inner">

			<!-- Breadcrumb -->
			<?php mm_render_component( 'molecules', 'breadcrumb', array(
				'items' => array(
					array( 'label' => __( 'Home', 'geek-ao-cubo' ),    'url' => home_url( '/' ) ),
					array( 'label' => strip_tags( get_the_archive_title() ), 'url' => '' ),
				),
			) ); ?>

			<h1 id="archive-publicacoes-titulo" class="archive-publicacoes__titulo">
				<?php echo wp_kses_post( get_the_archive_title() ); ?>
			</h1>
			<p class="archive-publicacoes__subtitulo">
				<?php 
				$description = get_the_archive_description();
				if ( $description ) {
					echo wp_kses_post( $description );
				} else {
					esc_html_e( 'Navegue pelo nosso acervo de publicações.', 'geek-ao-cubo' );
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
		<?php mm_render_component( 'organisms', 'lista-publicacoes', array( 'use_main_query' => true ) ); ?>
	</main>

</div>

<?php
get_footer();
