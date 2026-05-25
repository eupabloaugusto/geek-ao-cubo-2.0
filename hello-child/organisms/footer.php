<?php
/**
 * Organism: Footer (footer)
 *
 * Rodapé semântico e responsivo otimizado para SEO e acessibilidade.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$logo_text  = isset( $args['logo_text'] ) ? esc_html( $args['logo_text'] ) : get_bloginfo( 'name' );
$copyright  = isset( $args['copyright'] ) ? esc_html( $args['copyright'] ) : sprintf( __( '© %s Geek ao Cubo. Todos os direitos reservados.', 'hello-elementor-child' ), date( 'Y' ) );

// Links institucionais/legais descritivos sem "clique aqui"
$footer_menu = isset( $args['footer_menu'] ) ? $args['footer_menu'] : array(
	array( 'label' => __( 'Página Inicial', 'hello-elementor-child' ), 'url' => home_url( '/' ) ),
	array( 'label' => __( 'Catálogo de Animes', 'hello-elementor-child' ), 'url' => home_url( '/anime/' ) ),
	array( 'label' => __( 'Calendário de Lançamentos', 'hello-elementor-child' ), 'url' => home_url( '/calendario/' ) ),
	array( 'label' => __( 'Políticas de Privacidade', 'hello-elementor-child' ), 'url' => home_url( '/politica-de-privacidade/' ) ),
	array( 'label' => __( 'Termos de Uso', 'hello-elementor-child' ), 'url' => home_url( '/termos-de-uso/' ) ),
);
?>
<footer class="footer" role="contentinfo">
	<div class="footer__container">
		
		<div class="footer__top-row">
			<!-- 1. Branding / Logo -->
			<div class="footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer__logo-link" aria-label="<?php esc_attr_e( 'Voltar para a Página Inicial - Geek ao Cubo', 'hello-elementor-child' ); ?>">
					<?php 
					$logo_path = get_stylesheet_directory() . '/Novos-arquivos/Logo geek ao cubo 02.svg';
					if ( file_exists( $logo_path ) ) {
						echo file_get_contents( $logo_path );
					} else {
						echo '<span class="footer__logo-text">' . $logo_text . '</span>';
					}
					?>
				</a>
				<p class="footer__description">
					<?php _e( 'O seu portal definitivo sobre a cultura pop japonesa, análises de animes, guias de episódios e muito mais.', 'hello-elementor-child' ); ?>
				</p>
			</div>

			<!-- 2. Navegação Institucional e Legal (SEO Semântico) -->
			<nav class="footer__nav" aria-label="<?php esc_attr_e( 'Navegação de Rodapé', 'hello-elementor-child' ); ?>">
				<ul class="footer__menu">
					<?php foreach ( $footer_menu as $item ) : ?>
						<li class="footer__menu-item">
							<a href="<?php echo esc_url( $item['url'] ); ?>" class="footer__menu-link">
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>

		<hr class="footer__divider" aria-hidden="true" />

		<!-- 3. Créditos e Copyright -->
		<div class="footer__bottom-row">
			<span class="footer__copyright"><?php echo $copyright; ?></span>
			<span class="footer__attribution">
				<?php _e( 'Desenvolvido com foco em performance e SEO.', 'hello-elementor-child' ); ?>
			</span>
		</div>

	</div>
</footer>
