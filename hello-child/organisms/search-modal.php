<?php
/**
 * Organism: Modal de Busca (search-modal)
 *
 * Modal de busca em tela cheia com efeito glassmorphic de desfoque,
 * contendo o formulário de busca e links rápidos de sugestão.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$placeholder     = isset( $args['placeholder'] ) ? $args['placeholder'] : __( 'Digite sua pesquisa...', 'hello-elementor-child' );
$sugestoes_titulo = isset( $args['sugestoes_titulo'] ) ? esc_html( $args['sugestoes_titulo'] ) : __( 'Sugestões de Busca', 'hello-elementor-child' );
$sugestoes_tags   = isset( $args['sugestoes_tags'] ) && is_array( $args['sugestoes_tags'] ) ? $args['sugestoes_tags'] : array(
	array( 'label' => 'Animes', 'url' => '#category-animes' ),
	array( 'label' => 'Mangás', 'url' => '#category-mangas' ),
	array( 'label' => 'Guias de Maratona', 'url' => '#category-guias' ),
	array( 'label' => 'Solo Leveling', 'url' => '#tag-solo-leveling' ),
	array( 'label' => 'Demon Slayer', 'url' => '#tag-demon-slayer' ),
	array( 'label' => 'Bleach TYBW', 'url' => '#tag-bleach' ),
);
?>

<div id="search-modal" class="search-modal js-search-modal" role="dialog" aria-modal="true" aria-hidden="true" tabindex="-1">
	
	<!-- 1. Overlay Backdrop de Fundo com Efeito Blur -->
	<div class="search-modal__backdrop js-close-search-modal"></div>
	
	<!-- 2. Painel Centralizado da Busca -->
	<div class="search-modal__dialog">

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
					<h5 class="search-modal__suggestions-title"><?php echo $sugestoes_titulo; ?></h5>
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
