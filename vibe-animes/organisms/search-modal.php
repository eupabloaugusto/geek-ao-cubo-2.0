<?php
/**
 * Organism: Modal de Busca (search-modal)
 *
 * Modal de busca em tela cheia com efeito glassmorphic de desfoque,
 * contendo o formulário de busca e links rápidos de sugestão.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$placeholder     = isset( $args['placeholder'] ) ? $args['placeholder'] : __( 'Digite sua pesquisa...', 'geek-ao-cubo' );
$sugestoes_titulo = isset( $args['sugestoes_titulo'] ) ? esc_html( $args['sugestoes_titulo'] ) : __( 'Sugestões de Busca', 'geek-ao-cubo' );
$sugestoes_tags   = isset( $args['sugestoes_tags'] ) && is_array( $args['sugestoes_tags'] ) ? $args['sugestoes_tags'] : array(
	array( 'label' => 'Animes', 'url' => home_url( '/animes/' ) ),
	array( 'label' => 'Publicações', 'url' => home_url( '/publicacoes/' ) ),
	array( 'label' => 'Guias', 'url' => home_url( '/category/guias/' ) ),
	array( 'label' => 'One Piece', 'url' => home_url( '/?s=one+piece' ) ),
	array( 'label' => 'Demon Slayer', 'url' => home_url( '/?s=demon+slayer' ) ),
	array( 'label' => 'Solo Leveling', 'url' => home_url( '/?s=solo+leveling' ) ),
);
?>

<div id="search-modal" class="search-modal js-search-modal" role="dialog" aria-modal="true" aria-hidden="true" tabindex="-1" aria-labelledby="search-modal-title">
	
	<!-- 1. Overlay Backdrop de Fundo com Efeito Blur -->
	<div class="search-modal__backdrop js-close-search-modal"></div>
	
	<!-- 2. Painel Centralizado da Busca -->
	<div class="search-modal__dialog">

		<!-- Botão Fechar Acessível para WCAG AA -->
		<button class="search-modal__close js-close-search-modal" aria-label="<?php esc_attr_e( 'Fechar busca', 'geek-ao-cubo' ); ?>">
			<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
			</svg>
		</button>

		<div class="search-modal__content">
			
			<!-- Cabeçalho (Logo do Geek ao Cubo sutil) -->
			<div class="search-modal__brand">
				<?php 
				mm_render_component( 'atoms', 'logo', array(
					'variante' => 'horizontal-02',
					'link'     => false,
					'class'    => 'search-modal__logo'
				) );
				?>
			</div>

			<!-- Formulário de Busca -->
			<div class="search-modal__form-container">
				<?php 
				mm_render_component( 'molecules', 'form-busca', array(
					'placeholder' => $placeholder
				) );
				?>
			</div>

			<!-- Links Rápidos / Sugestões -->
			<?php if ( ! empty( $sugestoes_tags ) ) : ?>
				<div class="search-modal__suggestions">
					<h5 id="search-modal-title" class="search-modal__suggestions-title"><?php echo $sugestoes_titulo; ?></h5>
					<div class="search-modal__tags">
						<?php foreach ( $sugestoes_tags as $tag ) : ?>
							<a href="<?php echo esc_url( $tag['url'] ); ?>" class="search-modal__tag">
								<?php echo esc_html( $tag['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>
