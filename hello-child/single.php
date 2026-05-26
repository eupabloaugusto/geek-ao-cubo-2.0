<?php
/**
 * The template for displaying all single posts (Editorial / News).
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

// Loop principal do WordPress
while ( have_posts() ) :
	the_post();

	$post_id   = get_the_ID();
	$author_id = get_the_author_meta( 'ID' );

	// 1. Prepara argumentos para o Cabeçalho de Metadados (meta-artigo-header)
	// A. Categoria
	$categories = get_the_category( $post_id );
	$category_args = array();
	if ( ! empty( $categories ) ) {
		$category_args = array(
			'categoria' => esc_html( $categories[0]->name ),
			'url'       => esc_url( get_category_link( $categories[0]->term_id ) ),
		);
	}

	// B. Autor
	$author_args = array(
		'nome'   => esc_html( get_the_author() ),
		'url'    => esc_url( get_author_posts_url( $author_id ) ),
		'avatar' => esc_url( get_avatar_url( $author_id, array( 'size' => 64 ) ) ),
	);

	// C. Data
	$date_args = array(
		'data' => esc_html( get_the_date() ),
	);

	$meta_header_args = array(
		'category' => $category_args,
		'author'   => $author_args,
		'date'     => $date_args,
	);

	// 2. Prepara argumentos para as Tags do Artigo (tags-artigo)
	$post_tags = get_the_tags( $post_id );
	$tags_mapped = array();
	if ( ! empty( $post_tags ) && ! is_wp_error( $post_tags ) ) {
		foreach ( $post_tags as $t ) {
			$tags_mapped[] = array(
				'nome' => esc_html( $t->name ),
				'url'  => esc_url( get_tag_link( $t->term_id ) ),
			);
		}
	}
	$tags_args = array( 'tags' => $tags_mapped );

	// 3. Prepara argumentos para a Caixa do Autor (autor-profile-box)
	$author_box_args = array(
		'nome'      => esc_html( get_the_author() ),
		'descricao' => esc_html( get_the_author_meta( 'description', $author_id ) ),
		'url'       => esc_url( get_author_posts_url( $author_id ) ),
		'avatar'    => esc_url( get_avatar_url( $author_id, array( 'size' => 120 ) ) ),
	);

	// 4. Prepara argumentos para a Trilha de Navegação (breadcrumb)
	$breadcrumb_args = array(
		'class' => 'secao-artigo-unico__breadcrumb',
	);

	// =========================================================================
	// RENDERIZAÇÃO DO ORGANISMO DE CONTEÚDO PRINCIPAL (secao-artigo-unico)
	// =========================================================================
	?>
	<div class="site-main" id="main-content" style="padding-top: var(--space-400);">
		<div class="container" style="max-width: var(--container-max-width, 75rem); margin-inline: auto;">
			<?php
			mm_render_component( 'organisms', 'secao-artigo-unico', array(
				'title'       => get_the_title(),
				'hero_url'    => get_the_post_thumbnail_url( $post_id, 'large' ),
				'content'     => apply_filters( 'the_content', get_the_content() ),
				'breadcrumb'  => $breadcrumb_args,
				'meta_header' => $meta_header_args,
				'tags'        => $tags_args,
				'author_box'  => $author_box_args,
			) );
			?>

			<!-- =========================================================
			     RODAPÉ DO ARTIGO: LEIA TAMBÉM + CTA ASSISTIR
			     ========================================================= -->
			<?php
			// Customiza a imagem de fundo do CTA lateral com a própria capa do post para sinergia visual
			$cta_bg_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
			if ( empty( $cta_bg_url ) ) {
				$cta_bg_url = get_stylesheet_directory_uri() . '/atoms/editorial_banner_bg.png';
			}

			$stream_args = array(
				'title'         => __( 'RECOMENDAÇÃO', 'hello-elementor-child' ),
				'platform_name' => 'Crunchyroll',
				'description'   => __( 'Assista aos melhores animes da temporada com dublagem e legendas exclusivas em português.', 'hello-elementor-child' ),
				'image_url'     => $cta_bg_url,
				'stream_url'    => 'https://www.crunchyroll.com/',
			);

			mm_render_component( 'organisms', 'secao-pos-artigo', array(
				'class'       => 'secao-pos-artigo--editorial',
				'stream_args' => $stream_args,
				'related_args' => array(
					'title' => __( 'Leia Também', 'hello-elementor-child' ),
				),
			) );
			?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
