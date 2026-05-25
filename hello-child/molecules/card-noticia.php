<?php
/**
 * Molecule: Card de Notícia (card-noticia)
 *
 * Card de notícias, reviews e artigos do blog sob uma perspectiva premium de UX,
 * com suporte a 3 variações estruturais (Grid, List e Hero) e micro-ícones de acessibilidade.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Extração e Higienização de Parâmetros
$titulo      = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : '';
$url         = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$imagem_url  = isset( $args['imagem_url'] ) ? esc_url( $args['imagem_url'] ) : '';
$categoria   = isset( $args['categoria'] ) ? esc_html( $args['categoria'] ) : __( 'Geral', 'hello-elementor-child' );
$autor       = isset( $args['autor'] ) ? esc_html( $args['autor'] ) : __( 'Redação', 'hello-elementor-child' );
$data        = isset( $args['data'] ) ? esc_html( $args['data'] ) : '';
$resumo      = isset( $args['resumo'] ) ? esc_html( $args['resumo'] ) : '';
$variacao    = isset( $args['variacao'] ) && in_array( $args['variacao'], array( 'grid', 'list', 'hero', 'hero-vertical' ), true ) ? $args['variacao'] : 'grid';

// Ignora renderização se os dados vitais de link e título estiverem vazios
if ( empty( $titulo ) ) {
	return;
}

// 2. Montagem de Classes Modificadoras
$classes = 'card-noticia card-noticia--' . $variacao;
?>

<a href="<?php echo $url; ?>" class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Leia mais sobre: %s', 'hello-elementor-child' ), $titulo ) ); ?>">
	
	<!-- A. Capa de Mídia Widescreen (16:9) com Gradiente de Profundidade Overlay -->
	<div class="card-noticia__media-wrapper">
		<?php if ( ! empty( $imagem_url ) ) : ?>
			<img src="<?php echo $imagem_url; ?>" 
			     alt="<?php echo esc_attr( sprintf( __( 'Capa de: %s', 'hello-elementor-child' ), $titulo ) ); ?>" 
			     class="card-noticia__image" 
			     loading="lazy" />
			<div class="card-noticia__dark-overlay"></div>
		<?php else : ?>
			<!-- Fallback de imagem com gradiente dinâmico da marca -->
			<div class="card-noticia__fallback">
				<span class="card-noticia__fallback-text">
					<?php echo $categoria; ?>
				</span>
			</div>
		<?php endif; ?>
	</div>

	<!-- B. Conteúdo Textual em 3 Atos (Header, Body, Footer) para Ótimo Proximity Spacing -->
	<div class="card-noticia__content">
		
		<!-- ATO 1: Cabeçalho do Card (Badge + Título) -->
		<div class="card-noticia__header">
			<span class="card-noticia__eyebrow">
				<?php echo $categoria; ?>
			</span>
			<h3 class="card-noticia__title">
				<?php echo $titulo; ?>
			</h3>
		</div>

		<!-- ATO 2: Corpo do Card (Excerpt / Resumo descritivo) -->
		<?php if ( ! empty( $resumo ) ) : ?>
			<div class="card-noticia__body">
				<p class="card-noticia__excerpt">
					<?php echo $resumo; ?>
				</p>
			</div>
		<?php endif; ?>

		<!-- ATO 3: Rodapé do Card (Divisor + Metadados com Micro-Ícones) -->
		<div class="card-noticia__footer">
			<div class="card-noticia__meta">
				
				<!-- Autor com ícone User -->
				<span class="card-noticia__meta-item card-noticia__author">
					<svg class="card-noticia__icon" viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
					<?php printf( __( 'por %s', 'hello-elementor-child' ), $autor ); ?>
				</span>
				
				<!-- Data de Publicação com ícone Clock -->
				<?php if ( ! empty( $data ) ) : ?>
					<span class="card-noticia__meta-sep" aria-hidden="true">•</span>
					<span class="card-noticia__meta-item card-noticia__date">
						<svg class="card-noticia__icon" viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<circle cx="12" cy="12" r="10"></circle>
							<polyline points="12 6 12 12 16 14"></polyline>
						</svg>
						<?php echo $data; ?>
					</span>
				<?php endif; ?>

			</div>
		</div>

	</div>

</a>
