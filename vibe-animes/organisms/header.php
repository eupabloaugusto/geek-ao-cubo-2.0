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
 * @package vibe-animes
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
	$primary_location = isset( $locations['primary'] ) ? 'primary' : ( isset( $locations['menu-1'] ) ? 'menu-1' : '' );
	if ( $primary_location ) {
		$wp_menu = wp_get_nav_menu_items( $locations[ $primary_location ] );
		if ( $wp_menu ) {
			$menu_items_map = array();
			foreach ( $wp_menu as $menu_item ) {
				$is_active = false;
				global $wp;
				$current_url = home_url( add_query_arg( array(), $wp->request ) );
				if ( trailingslashit( $menu_item->url ) === trailingslashit( $current_url ) ) {
					$is_active = true;
				}
				$menu_items_map[ $menu_item->ID ] = array(
					'id'        => $menu_item->ID,
					'parent'    => $menu_item->menu_item_parent,
					'label'     => $menu_item->title,
					'url'       => $menu_item->url,
					'is_active' => $is_active,
					'submenu'   => array(),
				);
			}

			// Monta a hierarquia (passando por referência)
			foreach ( $menu_items_map as $id => &$item ) {
				if ( $item['parent'] && isset( $menu_items_map[ $item['parent'] ] ) ) {
					$menu_items_map[ $item['parent'] ]['submenu'][] = &$item;
				}
			}
			unset($item);

			// Filtra apenas os itens de nível raiz
			foreach ( $menu_items_map as $id => $item ) {
				if ( ! $item['parent'] ) {
					$menu_items[] = $item;
				}
			}
		}
	}
}

if ( empty( $menu_items ) ) {
	global $wp;
	$current_url = trailingslashit( home_url( $wp->request ) );
	$menu_items = array(
		array( 'label' => __( 'Início', 'vibe-animes' ),     'url' => home_url( '/' ),           'is_active' => is_front_page() ),
		array( 
			'label'     => __( 'Catálogo', 'vibe-animes' ), 
			'url'       => home_url( '/catalogo-de-animes/' ),     
			'is_active' => trailingslashit( home_url( '/catalogo-de-animes/' ) ) === $current_url || is_singular( 'anime' ) || is_post_type_archive( 'anime' ),
			'submenu'   => array(
				array( 'label' => __( 'Animes', 'vibe-animes' ), 'url' => home_url( '/catalogo-de-animes/?busca=&tipo_midia=serie' ) ),
				array( 'label' => __( 'Mangás', 'vibe-animes' ), 'url' => home_url( '/catalogo-de-animes/?busca=&tipo_midia=manga' ) ),
			),
		),
		array( 'label' => __( 'Personagens', 'vibe-animes' ), 'url' => home_url( '/personagens/' ), 'is_active' => trailingslashit( home_url( '/personagens/' ) ) === $current_url || is_singular( 'personagem' ) || is_post_type_archive( 'personagem' ) ),
		array( 'label' => __( 'Dubladores', 'vibe-animes' ), 'url' => home_url( '/dubladores/' ), 'is_active' => trailingslashit( home_url( '/dubladores/' ) ) === $current_url || is_singular( 'dublador' ) || is_post_type_archive( 'dublador' ) ),
		array( 'label' => __( 'Publicações', 'vibe-animes' ), 'url' => home_url( '/publicacoes/' ), 'is_active' => trailingslashit( home_url( '/publicacoes/' ) ) === $current_url || is_singular( 'post' ) || is_home() ),
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
		<nav class="header__nav" role="navigation" aria-label="<?php esc_attr_e( 'Navegação Principal', 'vibe-animes' ); ?>">
			<ul class="header__menu">
				<?php foreach ( $menu_items as $item ) : ?>
					<li class="header__menu-item <?php echo ! empty( $item['submenu'] ) ? 'header__menu-item--has-submenu' : ''; ?>">
						<?php 
						mm_render_component( 'atoms', 'nav-link', array(
							'label'     => $item['label'],
							'url'       => $item['url'],
							'is_active' => isset( $item['is_active'] ) ? (bool) $item['is_active'] : false,
							'icon'      => isset( $item['icon'] ) ? $item['icon'] : '',
						) ); 
						?>
						<?php if ( ! empty( $item['submenu'] ) ) : ?>
							<ul class="header__dropdown">
								<?php foreach ( $item['submenu'] as $subitem ) : ?>
									<li class="header__dropdown-item">
										<a href="<?php echo esc_url( $subitem['url'] ); ?>" class="header__dropdown-link">
											<?php echo esc_html( $subitem['label'] ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<!-- 4. Barra de Busca Compacta (Tablet/Desktop) -->
		<?php if ( $search_enabled ) : ?>
			<div class="header__search">
				<?php 
				mm_render_component( 'atoms', 'input-busca-compact', array(
					'placeholder' => __( 'Pesquisar...', 'vibe-animes' ),
					'readonly'    => true,
					'class'       => 'header-search-compact',
				) ); 
				?>
			</div>
		<?php endif; ?>

	</div>
</header>



