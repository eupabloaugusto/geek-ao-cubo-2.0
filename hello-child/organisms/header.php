<?php
/**
 * Organism: Responsive Header
 *
 * Cabeçalho principal responsivo e fixo (sticky glassmorphic).
 * Compõe os seguintes átomos e moléculas:
 * - btn-hamburger (para telas mobile/tablet, disparando o off-canvas)
 * - nav-link (menu de links principal para desktop)
 * - input-busca-compact (input de busca compacto para header)
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$search_enabled = isset( $args['search_enabled'] ) ? (bool) $args['search_enabled'] : true;
$logo_text      = isset( $args['logo_text'] ) ? esc_html( $args['logo_text'] ) : get_bloginfo( 'name' );
$menu_items     = isset( $args['menu_items'] ) && is_array( $args['menu_items'] ) ? $args['menu_items'] : array();

// Tenta recuperar dinamicamente do local do menu 'menu-1' se fornecido vazio
if ( empty( $menu_items ) ) {
	$locations = get_nav_menu_locations();
	if ( isset( $locations['menu-1'] ) ) {
		$wp_menu = wp_get_nav_menu_items( $locations['menu-1'] );
		if ( $wp_menu ) {
			foreach ( $wp_menu as $menu_item ) {
				$is_active = false;
				global $wp;
				$current_url = home_url( add_query_arg( array(), $wp->request ) );
				if ( trailingslashit( $menu_item->url ) === trailingslashit( $current_url ) ) {
					$is_active = true;
				}
				$menu_items[] = array(
					'label'     => $menu_item->title,
					'url'       => $menu_item->url,
					'is_active' => $is_active,
				);
			}
		}
	}
}

// Fallback estático caso o menu continue vazio (útil para desenvolvimento ou instalação limpa)
if ( empty( $menu_items ) ) {
	$menu_items = array(
		array( 'label' => __( 'Início', 'hello-elementor-child' ), 'url' => home_url( '/' ), 'is_active' => is_front_page() ),
		array( 'label' => __( 'Animes', 'hello-elementor-child' ), 'url' => home_url( '/anime/' ) ),
		array( 'label' => __( 'Temporadas', 'hello-elementor-child' ), 'url' => home_url( '/temporada/' ) ),
		array( 'label' => __( 'Calendário', 'hello-elementor-child' ), 'url' => home_url( '/calendario/' ) ),
	);
}
?>

<header class="header js-header" role="banner">
	<div class="header__container">
		
		<!-- 1. Gatilho Hamburger (Mobile/Tablet) -->
		<div class="header__mobile-trigger">
			<?php 
			mm_render_component( 'atoms', 'btn-hamburger', array(
				'id'    => 'header-hamburger-trigger',
				'class' => 'js-open-drawer',
			) ); 
			?>
		</div>

		<!-- 2. Logotipo (Centralizado no mobile, esquerda no desktop) -->
		<div class="header__logo">
			<?php
			mm_render_component( 'atoms', 'logo', array(
				'variante' => 'horizontal-02',
				'link'     => true,
				'url'      => home_url( '/' ),
			) );
			?>
		</div>

		<!-- 3. Menu de Navegação Horizontal (Desktop) -->
		<nav class="header__nav" role="navigation" aria-label="<?php esc_attr_e( 'Navegação Principal', 'hello-elementor-child' ); ?>">
			<ul class="header__menu">
				<?php foreach ( $menu_items as $item ) : ?>
					<li class="header__menu-item">
						<?php 
						mm_render_component( 'atoms', 'nav-link', array(
							'label'     => $item['label'],
							'url'       => $item['url'],
							'is_active' => isset( $item['is_active'] ) ? (bool) $item['is_active'] : false,
							'icon'      => isset( $item['icon'] ) ? $item['icon'] : '',
						) ); 
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<!-- 4. Barra de Busca Compacta (Tablet/Desktop) -->
		<?php if ( $search_enabled ) : ?>
			<div class="header__search">
				<?php 
				mm_render_component( 'atoms', 'input-busca-compact', array(
					'placeholder' => __( 'Pesquisar...', 'hello-elementor-child' ),
					'readonly'    => true,
					'class'       => 'header-search-compact',
				) ); 
				?>
			</div>
		<?php endif; ?>

	</div>
</header>


