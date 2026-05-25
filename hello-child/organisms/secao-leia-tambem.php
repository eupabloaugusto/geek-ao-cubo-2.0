<?php
/**
 * Organism: Seção Leia Também (secao-leia-tambem)
 *
 * Grade de posts relacionados exibida no encerramento de artigos.
 * Busca dinamicamente posts relacionados do WordPress por categoria ou exibe mockups mockados.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Parâmetros e Configurações
$title       = isset( $args['title'] ) ? esc_html( $args['title'] ) : __( 'LEIA TAMBÉM', 'hello-elementor-child' );
$posts       = isset( $args['posts'] ) ? $args['posts'] : array();
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// 2. Query Dinâmica do WordPress se estiver em ambiente real e sem posts manuais
if ( empty( $posts ) && is_single() && function_exists( 'get_the_category' ) ) {
	$categories = get_the_category();
	if ( ! empty( $categories ) ) {
		$cat_ids = array();
		foreach ( $categories as $cat ) {
			$cat_ids[] = $cat->term_id;
		}

		// Configuração da Query para 3 posts relacionados na mesma categoria
		$query_args = array(
			'category__in'   => $cat_ids,
			'post__not_in'   => array( get_the_ID() ), // Exclui o post atual
			'posts_per_page' => 3,
			'post_status'    => 'publish',
			'orderby'        => 'rand', // Aleatório para maior dinamismo
		);

		$related_query = new WP_Query( $query_args );

		// Fallback: se houver menos de 3 relacionados na mesma categoria, busca posts recentes gerais
		if ( $related_query->post_count < 3 ) {
			$needed = 3 - $related_query->post_count;
			$exclude = array( get_the_ID() );
			if ( $related_query->have_posts() ) {
				foreach ( $related_query->posts as $p ) {
					$exclude[] = $p->ID;
				}
			}

			$fallback_args = array(
				'post__not_in'   => $exclude,
				'posts_per_page' => $needed,
				'post_status'    => 'publish',
			);

			$fallback_query = new WP_Query( $fallback_args );
			
			// Combina os posts
			$merged_posts = array_merge( $related_query->posts, $fallback_query->posts );
		} else {
			$merged_posts = $related_query->posts;
		}

		// Converte os objetos de post do WP para o array de dados do componente
		foreach ( $merged_posts as $post_obj ) {
			$post_cats = get_the_category( $post_obj->ID );
			$post_cat  = ! empty( $post_cats ) ? $post_cats[0]->name : 'Anime';
			
			$posts[] = array(
				'titulo'     => get_the_title( $post_obj->ID ),
				'url'        => get_permalink( $post_obj->ID ),
				'imagem_url' => get_the_post_thumbnail_url( $post_obj->ID, 'medium_large' ),
				'categoria'  => $post_cat,
				'autor'      => get_the_author_meta( 'display_name', $post_obj->post_author ),
				'data'       => get_the_date( '', $post_obj->ID ),
				'resumo'     => wp_strip_all_tags( get_the_excerpt( $post_obj->ID ) ),
			);
		}
	}
}

// Se não houver posts de forma alguma, impede a renderização ou usa mockup estático completo
if ( empty( $posts ) ) {
	// Mockup estático ideal para Storybook e demonstrações
	$posts = array(
		array(
			'titulo'     => 'Os 10 Melhores Animes de Fantasia Sombria que Você Precisa Assistir Hoje',
			'url'        => '#',
			'imagem_url' => 'atoms/editorial_banner_bg.png',
			'categoria'  => 'LISTAS',
			'autor'      => 'Pedro Augusto',
			'data'       => '22 de Maio de 2026',
			'resumo'     => 'Explore mundos sinistros, magia perigosa e histórias intensas de sobrevivência em nossa seleção imperdível.',
		),
		array(
			'titulo'     => 'Por Que a Nova Temporada de Bleach Está Superando Todas as Expectativas dos Fãs?',
			'url'        => '#',
			'imagem_url' => 'atoms/editorial_banner_bg.png',
			'categoria'  => 'ANÁLISES',
			'autor'      => 'Ana Costa',
			'data'       => '20 de Maio de 2026',
			'resumo'     => 'Uma análise aprofundada dos pontos fortes da adaptação, direção de arte e a incrível fidelidade ao mangá original.',
		),
		array(
			'titulo'     => 'Guia de Temporada: Confira Todos os Lançamentos Confirmados para a Primavera',
			'url'        => '#',
			'imagem_url' => 'atoms/editorial_banner_bg.png',
			'categoria'  => 'GUIAS',
			'autor'      => 'Carlos Silva',
			'data'       => '18 de Maio de 2026',
			'resumo'     => 'Não perca nenhuma data de estreia! Reunimos sinopses, trailers oficiais e plataformas de streaming de todos os animes.',
		)
	);
}
?>
<section class="secao-leia-tambem <?php echo $class; ?>">
	<!-- Cabeçalho do Bloco -->
	<div class="secao-leia-tambem__header">
		<h2 class="secao-leia-tambem__title"><?php echo $title; ?></h2>
	</div>

	<!-- Grade de Cards -->
	<div class="secao-leia-tambem__grid">
		<?php foreach ( $posts as $post ) : ?>
			<?php 
			mm_render_component( 'molecules', 'card-noticia', array(
				'titulo'     => isset( $post['titulo'] ) ? $post['titulo'] : '',
				'url'        => isset( $post['url'] ) ? $post['url'] : '#',
				'imagem_url' => isset( $post['imagem_url'] ) ? $post['imagem_url'] : '',
				'categoria'  => isset( $post['categoria'] ) ? $post['categoria'] : '',
				'autor'      => isset( $post['autor'] ) ? $post['autor'] : '',
				'data'       => isset( $post['data'] ) ? $post['data'] : '',
				'resumo'     => isset( $post['resumo'] ) ? $post['resumo'] : '',
				'variacao'   => 'grid',
			) ); 
			?>
		<?php endforeach; ?>
	</div>
</section>
