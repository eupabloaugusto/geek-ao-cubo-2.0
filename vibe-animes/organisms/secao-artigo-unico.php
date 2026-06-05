<?php
/**
 * Organism: Seção de Artigo Único (secao-artigo-unico)
 *
 * Agrupa toda a estrutura visual e semântica de um artigo de blog:
 * - Trilha de Navegação (breadcrumb)
 * - Cabeçalho de Metadados (meta-artigo-header)
 * - Título H1
 * - Imagem de Capa Hero (imagem-capa ampla)
 * - Corpo de Texto Rico do Artigo (com embeds, citações e cards relacionados)
 * - tags-artigo (rodapé do conteúdo)
 * - autor-profile-box (biografia do autor)
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$title       = isset( $args['title'] ) ? esc_html( $args['title'] ) : '';
$hero_url    = isset( $args['hero_url'] ) ? esc_url( $args['hero_url'] ) : '';
$content     = isset( $args['content'] ) ? $args['content'] : ''; // Aceita HTML estruturado simulado ou real
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

$breadcrumb_args  = isset( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
$meta_header_args = isset( $args['meta_header'] ) ? $args['meta_header'] : array();
$tags_args        = isset( $args['tags'] ) ? $args['tags'] : array();
$author_box_args  = isset( $args['author_box'] ) ? $args['author_box'] : array();
$animes_rel       = isset( $args['animes_rel'] ) ? $args['animes_rel'] : array();

// 2. Fallbacks dinâmicos completos para o Loop do WP
if ( empty( $title ) && function_exists( 'get_the_title' ) ) {
	$title = get_the_title();
}
if ( empty( $hero_url ) && function_exists( 'get_the_post_thumbnail_url' ) ) {
	$hero_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
}
if ( empty( $content ) && function_exists( 'get_the_content' ) ) {
	$content = apply_filters( 'the_content', get_the_content() );
}

// Se não houver título, impede renderização
if ( empty( $title ) ) {
	return;
}
?>
<article class="secao-artigo-unico <?php echo $class; ?>">
	<!-- AD: Banner topo do artigo -->
	<div style="margin-bottom: var(--space-300);">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'    => 'single-banner-top',
			'variacao' => 'banner',
		) ); ?>
	</div>

	<!-- 1. Trilha de Navegação (Breadcrumb) -->
	<div class="secao-artigo-unico__nav">
		<?php mm_render_component( 'molecules', 'breadcrumb', $breadcrumb_args ); ?>
	</div>

	<!-- 2. Cabeçalho de Metadados (Categoria + Autor + Data) -->
	<header class="secao-artigo-unico__header">
		<?php mm_render_component( 'molecules', 'meta-artigo-header', $meta_header_args ); ?>
		
		<!-- 3. Título H1 Principal -->
		<h1 class="secao-artigo-unico__title"><?php echo $title; ?></h1>
	</header>

	<!-- 4. Imagem de Destaque Ampla (Hero) -->
	<div class="secao-artigo-unico__hero-frame">
		<?php 
		mm_render_component( 'atoms', 'imagem-capa', array(
			'src'   => $hero_url,
			'alt'   => sprintf( 'Capa de destaque de %s', $title ),
			'class' => 'secao-artigo-unico__hero-image'
		) ); 
		?>
	</div>

	<!-- 5. Corpo de Conteúdo Rico do Artigo -->
	<div class="secao-artigo-unico__body">
		<?php if ( ! empty( $content ) ) : ?>
			<?php echo $content; ?>
		<?php else : ?>
			<!-- Fallback de texto rico estruturado (Crunchyroll News Style) -->
			<p>O universo de animes de fantasia sombria está prestes a receber mais um grande destaque neste ano. A produtora revelou hoje o primeiro visual oficial e confirmou a data de estreia da nova adaptação que promete abalar os corações dos fãs.</p>
			
			<h2>O Retorno dos Guerreiros Lendários</h2>
			<p>Com direção de renomados diretores do setor e produção pelo estúdio responsável por grandes sucessos recentes, a expectativa em torno de roteiro e fluidez de animação está elevadíssima. Segundo o produtor executivo, a equipe está focando em trazer maior profundidade psicológica aos personagens originais.</p>
			
			<blockquote>
				"Queremos criar uma experiência que misture a ação frenética que os fãs adoram com momentos de drama e reflexão profundos. Esta temporada será inesquecível."
				<cite>— Diretor de Animação</cite>
			</blockquote>

			<p>Abaixo, você pode conferir o trailer promocional divulgado durante o evento especial de animes:</p>

			<!-- Exemplo de Átomo Integrado no Corpo: embed-video -->
			<div class="secao-artigo-unico__embedded-block">
				<?php 
				mm_render_component( 'atoms', 'embed-video', array(
					'video_id' => '2DOb04vQ3jY',
					'title'    => 'Demon Slayer - Mugen Train Trailer'
				) ); 
				?>
			</div>

			<p>Para quem deseja acompanhar outros lançamentos e resumos detalhados de episódios, vale a pena conferir a nossa cobertura especial sobre o tema:</p>

			<!-- Exemplo de Molécula Integrada no Corpo: card-noticia-relacionada -->
			<div class="secao-artigo-unico__embedded-block">
				<?php 
				mm_render_component( 'molecules', 'card-noticia-relacionada', array(
					'title'     => 'Guia Completo: Entenda a Ordem Cronológica para Assistir Demon Slayer',
					'url'       => '#',
					'image_url' => $hero_url,
					'category'  => array( 'name' => 'GUIA', 'slug' => 'guia' )
				) ); 
				?>
			</div>

			<p>A estreia oficial está agendada para a próxima temporada de outono no Japão, com transmissão simultânea confirmada para as principais plataformas de streaming no Brasil. Prepare sua maratona!</p>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $animes_rel ) ) : ?>
		<?php
			// Determina título dinâmico baseado nos post types (anime, manga ou misto)
			$has_anime = false;
			$has_manga = false;
			foreach ( $animes_rel as $item ) {
				$pt = get_post_type( $item->ID );
				if ( $pt === 'anime' ) $has_anime = true;
				if ( $pt === 'manga' ) $has_manga = true;
			}
			
			if ( $has_anime && $has_manga ) {
				$dynamic_title = __( 'Animes e Mangás mencionados neste artigo', 'geek-ao-cubo' );
			} elseif ( $has_manga ) {
				$dynamic_title = __( 'Mangás mencionados neste artigo', 'geek-ao-cubo' );
			} else {
				$dynamic_title = __( 'Animes mencionados neste artigo', 'geek-ao-cubo' );
			}
		?>
		<!-- 5.5. Títulos Relacionados do Catálogo -->
		<div class="secao-artigo-unico__animes-relacionados" style="margin-top: var(--space-600); margin-bottom: var(--space-400);">
			<?php 
			mm_render_component( 'organisms', 'secao-titulo', array(
				'titulo'       => $dynamic_title,
				'titulo_class' => 'secao-titulo__main',
			) ); 
			?>
			<div style="display: flex; flex-direction: column; gap: var(--space-200); margin-top: var(--space-300);">
				<?php foreach ( $animes_rel as $anime_post ) : 
					mm_render_component( 'molecules', 'card-anime-horizontal', array(
						'post_id' => $anime_post->ID,
					) );
				endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- 6. Posts Relacionados: Veja Também -->
	<?php mm_render_component( 'organisms', 'secao-leia-tambem', array(
		'title' => __( 'Veja também', 'geek-ao-cubo' ),
	) ); ?>

	<!-- 7. Rodapé do Conteúdo (Tags) -->
	<footer class="secao-artigo-unico__footer">
		<?php mm_render_component( 'molecules', 'tags-artigo', $tags_args ); ?>
	</footer>

	<!-- 8. Seção de Anúncio pré-footer -->
	<?php mm_render_component( 'organisms', 'secao-anuncios', array(
		'slot'    => 'single-pre-footer',
		'variacao' => 'banner',
	) ); ?>
</article>
