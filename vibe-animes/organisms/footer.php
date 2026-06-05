<?php
/**
 * Organism: Footer (footer)
 *
 * Rodapé semântico e responsivo otimizado para SEO e acessibilidade.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$logo_text  = isset( $args['logo_text'] ) ? esc_html( $args['logo_text'] ) : get_bloginfo( 'name' );
$copyright  = isset( $args['copyright'] ) ? esc_html( $args['copyright'] ) : sprintf( __( '© %s Vibe Animes. Todos os direitos reservados.', 'vibe-animes' ), date( 'Y' ) );

// Links institucionais/legais descritivos sem "clique aqui"
$footer_menu = isset( $args['footer_menu'] ) ? $args['footer_menu'] : array(
	array( 'label' => __( 'Página Inicial', 'vibe-animes' ), 'url' => home_url( '/' ) ),
	array( 'label' => __( 'Catálogo de Animes', 'vibe-animes' ), 'url' => home_url( '/catalogo-de-animes/' ) ),
	array( 'label' => __( 'Catálogo de Mangás', 'vibe-animes' ), 'url' => home_url( '/catalogo-de-animes/?busca=&tipo_midia=manga' ) ),
	array( 'label' => __( 'Calendário de Lançamentos', 'vibe-animes' ), 'url' => home_url( '/calendario/' ) ),
	array( 'label' => __( 'Políticas de Privacidade', 'vibe-animes' ), 'url' => home_url( '/politica-de-privacidade/' ) ),
	array( 'label' => __( 'Termos de Uso', 'vibe-animes' ), 'url' => home_url( '/termos-de-uso/' ) ),
);
?>
<footer class="footer" role="contentinfo">
	<div class="footer__container">
		
		<div class="footer__top-row">
			<!-- 1. Branding / Logo -->
			<div class="footer__brand">
				<?php
				mm_render_component( 'atoms', 'logo', array(
					'variante' => 'horizontal-02',
					'link'     => true,
					'url'      => home_url( '/' ),
					'class'    => 'footer__logo-img',
					'alt'      => __( 'Voltar para a Página Inicial - Vibe Animes', 'vibe-animes' ),
				) );
				?>
				<p class="footer__description">
					<?php _e( 'O seu portal definitivo sobre a cultura pop japonesa, análises de animes, guias de episódios e muito mais.', 'vibe-animes' ); ?>
				</p>
			</div>

			<!-- 2. Navegação Institucional e Legal (SEO Semântico) -->
			<nav class="footer__nav" aria-label="<?php esc_attr_e( 'Navegação de Rodapé', 'vibe-animes' ); ?>">
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
				<?php _e( 'Desenvolvido com foco em performance e SEO.', 'vibe-animes' ); ?>
			</span>
		</div>

	</div>
</footer>
