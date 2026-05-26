<?php
/**
 * Organism: Seção Notícias Recentes (secao-noticias-recentes)
 *
 * Exibe as notícias em um layout editorial de alta visibilidade.
 * O primeiro card da listagem se torna destaque (Variação Hero Horizontal)
 * e os cards seguintes ficam em uma grade responsiva (Variação Grid).
 *
 * @package hello-elementor-child
 *
 * @param string $titulo       Título da seção. Default: 'Notícias Recentes'.
 * @param array  $noticias     Array plano de notícias. Cada item deve ter os parâmetros do card-noticia.
 * @param string $ver_mais_url URL para link "Ver mais" no rodapé. Opcional.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo       = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : __( 'Notícias Recentes', 'hello-elementor-child' );
$noticias     = isset( $args['noticias'] ) ? (array) $args['noticias'] : array();
$ver_mais_url = isset( $args['ver_mais_url'] ) ? esc_url( $args['ver_mais_url'] ) : '';

if ( empty( $noticias ) ) {
	return;
}

// Lógica Editorial: primeiro item vira Destaque (Hero), o restante vira Grid
$destaque  = ! empty( $noticias ) ? array_shift( $noticias ) : null;
$restantes = $noticias;
?>

<section class="secao-noticias-recentes" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-noticias-recentes__inner">

		<!-- Título da Seção -->
		<h2 class="secao-noticias-recentes__title"><?php echo $titulo; ?></h2>

		<!-- 1. Bloco de Destaque Editorial -->
		<?php if ( ! empty( $destaque ) ) : ?>
			<div class="secao-noticias-recentes__destaque">
				<?php
				$destaque['variacao'] = 'hero';
				mm_render_component( 'molecules', 'card-noticia', $destaque );
				?>
			</div>
		<?php endif; ?>

		<!-- 2. Grade de Notícias Secundárias -->
		<?php if ( ! empty( $restantes ) ) : ?>
			<div class="secao-noticias-recentes__grid">
				<?php foreach ( $restantes as $noticia ) : ?>
					<?php
					$noticia['variacao'] = 'grid';
					mm_render_component( 'molecules', 'card-noticia', $noticia );
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- 3. Rodapé com Botão "Ver Mais" -->
		<?php if ( ! empty( $ver_mais_url ) ) : ?>
			<div class="secao-noticias-recentes__footer">
				<a href="<?php echo $ver_mais_url; ?>" class="btn btn--secondary secao-noticias-recentes__button">
					<?php _e( 'Ver mais notícias', 'hello-elementor-child' ); ?>
				</a>
			</div>
		<?php endif; ?>

	</div>
</section>
