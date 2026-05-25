<?php
/**
 * Organism: Navigation Drawer
 *
 * Menu lateral (off-canvas) completo e responsivo, ideal para dispositivos mobile.
 * Compõe os seguintes átomos e moléculas:
 * - drawer-overlay (átomo de backdrop com blur)
 * - btn-hamburger (átomo de gatilho, reaproveitado como botão de fechar)
 * - form-busca (molécula de busca integrada)
 * - drawer-link (átomo de link principal/dropdown)
 * - drawer-sub-link (átomo de link secundário)
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_open    = isset( $args['is_open'] ) ? (bool) $args['is_open'] : false;
$menu_items = isset( $args['menu_items'] ) && is_array( $args['menu_items'] ) ? $args['menu_items'] : array();
$logo_text  = isset( $args['logo_text'] ) ? esc_html( $args['logo_text'] ) : get_bloginfo( 'name' );
$search_enabled = isset( $args['search_enabled'] ) ? (bool) $args['search_enabled'] : true;

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

$classes = 'navigation-drawer';
if ( $is_open ) {
	$classes .= ' navigation-drawer--open';
}
?>

<!-- 1. Fundo Escuro com Desfoque (Overlay Backdrop) -->
<?php 
mm_render_component( 'atoms', 'drawer-overlay', array(
	'is_active' => $is_open,
	'id'        => 'nav-drawer-overlay'
) ); 
?>

<!-- 2. Painel Lateral Deslizante -->
<aside id="nav-drawer" class="<?php echo esc_attr( $classes ); ?>" aria-hidden="<?php echo $is_open ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'Menu de Navegação', 'hello-elementor-child' ); ?>">
	<div class="navigation-drawer__panel">
		
		<!-- Cabeçalho do Drawer -->
		<header class="navigation-drawer__header">
			<div class="navigation-drawer__logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navigation-drawer__logo-link" aria-label="<?php esc_attr_e( 'Página Inicial - Geek ao Cubo', 'hello-elementor-child' ); ?>">
					<?php 
					$logo_path = get_stylesheet_directory() . '/Novos-arquivos/Logo geek ao cubo 02.svg';
					if ( file_exists( $logo_path ) ) {
						// Injeta o SVG embutido
						echo file_get_contents( $logo_path );
					} else {
						echo '<span class="navigation-drawer__brand">' . $logo_text . '</span>';
					}
					?>
				</a>
			</div>
			
			<!-- Botão Fechar (X animado via btn-hamburger) -->
			<?php 
			mm_render_component( 'atoms', 'btn-hamburger', array(
				'is_active' => true,
				'class'     => 'navigation-drawer__close',
				'id'        => 'nav-drawer-close-btn'
			) );
			?>
		</header>

		<!-- Corpo do Drawer (Área de Scroll) -->
		<div class="navigation-drawer__body">
			
			<!-- Barra de busca no topo do menu lateral (opcional) -->
			<?php if ( $search_enabled ) : ?>
				<div class="navigation-drawer__search">
					<?php mm_render_component( 'molecules', 'form-busca', array(
						'placeholder' => __( 'Pesquisar no site...', 'hello-elementor-child' )
					) ); ?>
				</div>
			<?php endif; ?>

			<!-- Navegação Principal -->
			<nav class="navigation-drawer__nav" role="navigation" aria-label="<?php esc_attr_e( 'Menu Mobile', 'hello-elementor-child' ); ?>">
				<ul class="navigation-drawer__menu">
					<?php foreach ( $menu_items as $index => $item ) : 
						$has_sublinks = ! empty( $item['sublinks'] ) && is_array( $item['sublinks'] );
						$item_open    = isset( $item['is_open'] ) ? (bool) $item['is_open'] : false;
						$item_active  = isset( $item['is_active'] ) ? (bool) $item['is_active'] : false;
						$item_icon    = isset( $item['icon'] ) ? $item['icon'] : '';
						$item_url     = isset( $item['url'] ) ? $item['url'] : '#';
						$item_label   = isset( $item['label'] ) ? $item['label'] : '';
						?>
						<li class="navigation-drawer__item">
							<?php 
							// Renderiza o link principal ou botão de dropdown
							mm_render_component( 'atoms', 'drawer-link', array(
								'label'        => $item_label,
								'url'          => $has_sublinks ? '#' : $item_url,
								'is_active'    => $item_active,
								'icon'         => $item_icon,
								'has_dropdown' => $has_sublinks,
								'is_open'      => $item_open
							) );
							?>
							
							<?php if ( $has_sublinks ) : ?>
								<!-- Container de Sublinks (Acordeão) -->
								<ul class="drawer-sub-nav <?php echo $item_open ? 'drawer-sub-nav--open' : ''; ?>">
									<?php foreach ( $item['sublinks'] as $sublink ) : 
										$sub_active = isset( $sublink['is_active'] ) ? (bool) $sublink['is_active'] : false;
										$sub_url    = isset( $sublink['url'] ) ? $sublink['url'] : '#';
										$sub_label  = isset( $sublink['label'] ) ? $sublink['label'] : '';
										
										mm_render_component( 'atoms', 'drawer-sub-link', array(
											'label'     => $sub_label,
											'url'       => $sub_url,
											'is_active' => $sub_active
										) );
									endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

		</div>

		<!-- Rodapé do Drawer -->
		<footer class="navigation-drawer__footer">
			<div class="navigation-drawer__social">
				<!-- Ícones sociais com ARIA labels corretos para SEO/Acessibilidade -->
				<a href="#" class="navigation-drawer__social-link" aria-label="Acompanhe no Instagram" target="_blank" rel="noopener noreferrer">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
				</a>
				<a href="#" class="navigation-drawer__social-link" aria-label="Inscreva-se no YouTube" target="_blank" rel="noopener noreferrer">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.517 3.545 12 3.545 12 3.545s-7.517 0-9.388.507a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.871.507 9.388.507 9.388.507s7.517 0 9.388-.507a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
				</a>
			</div>
			<div class="navigation-drawer__copyright">
				<p>&copy; <?php echo date( 'Y' ); ?> Geek ao Cubo. Todos os direitos reservados.</p>
			</div>
		</footer>

	</div>
</aside>
