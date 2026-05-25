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
	
	<!-- Seção/Widget 1: Busca -->
	<section class="sidebar-widget sidebar-widget--search">
		<h3 class="sidebar-widget__title"><?php _e( 'Pesquisar Conteúdo', 'hello-elementor-child' ); ?></h3>
		<?php 
		// Renderiza a molécula de Busca
		mm_render_component( 'molecules', 'form-busca', array(
			'placeholder' => __( 'Buscar no portal...', 'hello-elementor-child' ),
		) ); 
		?>
	</section>

	<!-- Seção/Widget 2: Destaques da Temporada -->
	<section class="sidebar-widget sidebar-widget--destaques">
		<h3 class="sidebar-widget__title"><?php _e( 'Destaques da Temporada', 'hello-elementor-child' ); ?></h3>
		
		<ul class="sidebar-destaques-list">
			
			<li class="sidebar-destaque-item">
				<div class="sidebar-destaque-item__meta">
					<?php mm_render_component( 'atoms', 'nota-mal', array( 'nota' => '9.13' ) ); ?>
					<?php mm_render_component( 'atoms', 'badge-status', array( 'status' => 'airing' ) ); ?>
				</div>
				<a href="#" class="sidebar-destaque-item__title">Solo Leveling — Temporada 2</a>
			</li>

			<li class="sidebar-destaque-item">
				<div class="sidebar-destaque-item__meta">
					<?php mm_render_component( 'atoms', 'nota-mal', array( 'nota' => '8.92' ) ); ?>
					<?php mm_render_component( 'atoms', 'badge-status', array( 'status' => 'completed' ) ); ?>
				</div>
				<a href="#" class="sidebar-destaque-item__title">Frieren: Beyond Journey's End</a>
			</li>

			<li class="sidebar-destaque-item">
				<div class="sidebar-destaque-item__meta">
					<?php mm_render_component( 'atoms', 'nota-mal', array( 'nota' => '4.85' ) ); ?>
					<?php mm_render_component( 'atoms', 'badge-status', array( 'status' => 'airing' ) ); ?>
				</div>
				<a href="#" class="sidebar-destaque-item__title">Isekai Cheat Magician — Ep 12</a>
			</li>

		</ul>
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
