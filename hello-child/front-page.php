<?php
/**
 * Template Name: Página Inicial (Home)
 *
 * Template seco para a página inicial (capa) do portal Geek ao Cubo.
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

?>

<div class="home-page">

	<!-- 1. SEÇÃO DE TOPO / HERO CARROSSEL (Task 1.2 — Destaques) -->
	<div class="home-page__hero-section">
		<?php
		// Query centralizada para buscar os posts de Destaque
		$query = mm_query_posts_destaque( 4 );

		$posts_carousel = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$featured_img  = get_the_post_thumbnail_url( get_the_ID(), 'large' );
				$categories    = get_the_category();
				$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'hello-elementor-child' );

				$posts_carousel[] = array(
					'titulo'     => get_the_title(),
					'url'        => get_permalink(),
					'imagem_url' => $featured_img,
					'categoria'  => $category_name,
					'autor'      => get_the_author(),
					'data'       => get_the_date(),
					'resumo'     => get_the_excerpt(),
				);
			}
			wp_reset_postdata();
		}

		if ( ! empty( $posts_carousel ) ) {
			mm_render_component( 'organisms', 'secao-carrossel-destaque', array(
				'posts_carousel' => $posts_carousel,
			) );
		} else {
			// Fallback visual estético caso o banco local esteja 100% zerado de posts
			?>
			<div class="home-placeholder-carousel">
				<div class="home-placeholder-carousel__slide">
					<div class="home-placeholder-carousel__badge"><?php _e( 'EM DESTAQUE', 'hello-elementor-child' ); ?></div>
					<h2 class="home-placeholder-carousel__title"><?php _e( 'Nenhum artigo encontrado no banco de dados local.', 'hello-elementor-child' ); ?></h2>
					<p class="home-placeholder-carousel__desc"><?php _e( 'Por favor, execute o script de sincronização ou crie alguns posts de teste no seu painel administrativo local do WordPress.', 'hello-elementor-child' ); ?></p>
					<span class="home-placeholder-carousel__meta"><?php _e( 'Sem posts para exibir', 'hello-elementor-child' ); ?></span>
				</div>
			</div>
			<?php
		}
		?>
	</div>

</div>

<?php
get_footer();

