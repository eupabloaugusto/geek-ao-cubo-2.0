<?php
/**
 * Organism: Barra Lateral (Sidebar)
 *
 * Compõe os átomos e moléculas: form-busca + lista de destaques + anuncio-adsense.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
$adsense_slot = isset( $args['adsense_slot'] ) ? esc_attr( $args['adsense_slot'] ) : '9876543210';
?>

<aside class="sidebar <?php echo $class; ?>" role="complementary" aria-label="<?php esc_attr_e( 'Barra Lateral', 'hello-elementor-child' ); ?>">
	
	<!-- Seção/Widget: Artigos Recentes (Variação Lista Horizontal) -->
	<section class="sidebar-widget-clean">
		<h3 class="sidebar-widget__title" style="margin-left: var(--space-300);"><?php _e( 'Últimas Novidades', 'hello-elementor-child' ); ?></h3>
		
		<div class="sidebar-posts-column" style="display: flex; flex-direction: column; gap: var(--space-400);">
			<?php 
			// Busca as 4 últimas notícias usando o helper centralizado
			$query_sidebar = mm_query_noticias_recentes( 4 );
			
			if ( $query_sidebar->have_posts() ) {
				while ( $query_sidebar->have_posts() ) {
					$query_sidebar->the_post();
					$featured_img  = get_the_post_thumbnail_url( get_the_ID(), 'large' );
					$categories    = get_the_category();
					$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'hello-elementor-child' );
					
					$post_args = array(
						'titulo'     => get_the_title(),
						'url'        => get_permalink(),
						'imagem_url' => $featured_img,
						'categoria'  => $category_name,
						'autor'      => get_the_author(),
						'data'       => get_the_date(),
						'resumo'     => get_the_excerpt(),
						'variacao'   => 'list'
					);
					
					mm_render_component( 'molecules', 'card-noticia', $post_args );
				}
				wp_reset_postdata();
			} else {
				// Fallback caso não haja posts no banco local
				?>
				<p class="sidebar-posts-empty" style="padding-inline: var(--space-400); color: var(--neutral-400); font-family: var(--font-body); font-size: var(--text-xs-size);"><?php _e( 'Nenhum artigo disponível.', 'hello-elementor-child' ); ?></p>
				<?php
			}
			?>
		</div>
	</section>

	<!-- Seção/Widget 3: Publicidade -->
	<section class="sidebar-widget sidebar-widget--ads">
		<?php 
		// Renderiza o anúncio Adsense
		mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot' => $adsense_slot,
		) ); 
		?>
	</section>

</aside>
