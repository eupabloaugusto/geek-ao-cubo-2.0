<?php
/**
 * Molecule: Trilha de Navegação (breadcrumb)
 *
 * Renderiza a trilha de navegação (breadcrumb) completa.
 * Suporta Yoast SEO de forma nativa e possui fallback dinâmico completo para o Loop do WP.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

?>
<nav class="breadcrumb <?php echo $class; ?>" aria-label="<?php esc_attr_e( 'Breadcrumb', 'hello-elementor-child' ); ?>">
	<?php if ( function_exists( 'yoast_breadcrumb' ) ) : ?>
		<?php yoast_breadcrumb( '<ol class="breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">', '</ol>' ); ?>
	<?php else : ?>
		<!-- Fallback Dinâmico do WordPress / Storybook -->
		<ol class="breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
			<?php
			// 1. Home
			mm_render_component( 'atoms', 'breadcrumb-item', array(
				'label'          => __( 'Home', 'hello-elementor-child' ),
				'url'            => home_url( '/' ),
				'show_separator' => true,
				'position'       => 1,
			) );

			// 2. Categoria (se for post único ou página de taxonomia)
			$position = 2;
			if ( is_single() ) {
				$categories = get_the_category();
				if ( ! empty( $categories ) ) {
					// Pega a categoria principal/primeira
					$main_category = $categories[0];
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => $main_category->name,
						'url'            => get_category_link( $main_category->term_id ),
						'show_separator' => true,
						'position'       => $position++,
					) );
				}
			} elseif ( is_category() ) {
				$current_cat = get_queried_object();
				if ( $current_cat && isset( $current_cat->name ) ) {
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => $current_cat->name,
						'is_current'     => true,
						'show_separator' => false,
						'position'       => $position++,
					) );
				}
			}

			// 3. Post/Página Atual
			if ( is_single() || is_page() ) {
				mm_render_component( 'atoms', 'breadcrumb-item', array(
					'label'          => get_the_title(),
					'is_current'     => true,
					'show_separator' => false,
					'position'       => $position++,
				) );
			} elseif ( ! is_single() && ! is_category() && ! is_page() ) {
				// Fallback genérico para o Storybook ou páginas estáticas customizadas
				$items = isset( $args['items'] ) ? $args['items'] : array();
				if ( ! empty( $items ) ) {
					$count = count( $items );
					foreach ( $items as $index => $item ) {
						$is_last = ( $index === $count - 1 );
						mm_render_component( 'atoms', 'breadcrumb-item', array(
							'label'          => isset( $item['label'] ) ? $item['label'] : '',
							'url'            => isset( $item['url'] ) ? $item['url'] : '',
							'is_current'     => isset( $item['is_current'] ) ? (bool) $item['is_current'] : $is_last,
							'show_separator' => ! $is_last,
							'position'       => $position++,
						) );
					}
				} else {
					// Fallback absoluto de demonstração estática
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => __( 'Notícias', 'hello-elementor-child' ),
						'url'            => '#',
						'show_separator' => true,
						'position'       => $position++,
					) );
					mm_render_component( 'atoms', 'breadcrumb-item', array(
						'label'          => __( 'Anime', 'hello-elementor-child' ),
						'is_current'     => true,
						'show_separator' => false,
						'position'       => $position++,
					) );
				}
			}
			?>
		</ol>
	<?php endif; ?>
</nav>
