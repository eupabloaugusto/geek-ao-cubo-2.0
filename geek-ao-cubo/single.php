<?php
/**
 * The template for displaying all single posts (Editorial / News).
 *
 * @package geek-ao-cubo
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
	<main class="site-main" id="main-content" style="padding-top: var(--space-400);">
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
				'animes_rel'  => get_field('post_animes_relacionados', $post_id)
			) );
			?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
