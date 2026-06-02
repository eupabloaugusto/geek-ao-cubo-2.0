<?php
/**
 * Organism: Seção Leia Também (secao-leia-tambem)
 *
 * Grade de posts relacionados exibida no encerramento de artigos.
 * Busca dinamicamente posts relacionados do WordPress por categoria ou exibe mockups mockados.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Parâmetros e Configurações
$title       = isset( $args['title'] ) ? esc_html( $args['title'] ) : __( 'Veja também', 'geek-ao-cubo' );
$posts       = isset( $args['posts'] ) ? $args['posts'] : array();
$class       = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// 2. Query Dinâmica por Similaridade em 3 Camadas
if ( empty( $posts ) && is_single() && function_exists( 'get_the_category' ) ) {
	$current_id = get_the_ID();
	$exclude    = array( $current_id );
	$limit      = 3;

	// Coleta IDs de categorias e tags do post atual
	$categories = get_the_category( $current_id );
	$cat_ids    = wp_list_pluck( $categories, 'term_id' );

	$tags    = get_the_tags( $current_id );
	$tag_ids = ( $tags && ! is_wp_error( $tags ) ) ? wp_list_pluck( $tags, 'term_id' ) : array();

	$collected = array();

	// Camada 1: mesma categoria E mesmas tags (maior similaridade)
	if ( ! empty( $cat_ids ) && ! empty( $tag_ids ) ) {
		$q1 = new WP_Query( array(
			'category__in'   => $cat_ids,
			'tag__in'        => $tag_ids,
			'post__not_in'   => $exclude,
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		foreach ( $q1->posts as $p ) {
			$collected[] = $p;
			$exclude[]   = $p->ID;
		}
	}

	// Camada 2: mesmas tags (sem categoria obrigatória)
	if ( count( $collected ) < $limit && ! empty( $tag_ids ) ) {
		$q2 = new WP_Query( array(
			'tag__in'        => $tag_ids,
			'post__not_in'   => $exclude,
			'posts_per_page' => $limit - count( $collected ),
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		foreach ( $q2->posts as $p ) {
			$collected[] = $p;
			$exclude[]   = $p->ID;
		}
	}

	// Camada 3: mesma categoria (sem tags obrigatórias)
	if ( count( $collected ) < $limit && ! empty( $cat_ids ) ) {
		$q3 = new WP_Query( array(
			'category__in'   => $cat_ids,
			'post__not_in'   => $exclude,
			'posts_per_page' => $limit - count( $collected ),
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		foreach ( $q3->posts as $p ) {
			$collected[] = $p;
			$exclude[]   = $p->ID;
		}
	}

	// Fallback: posts recentes gerais
	if ( count( $collected ) < $limit ) {
		$q4 = new WP_Query( array(
			'post__not_in'   => $exclude,
			'posts_per_page' => $limit - count( $collected ),
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		foreach ( $q4->posts as $p ) {
			$collected[] = $p;
		}
	}

	// Converte objetos WP_Post para o array de dados do componente
	foreach ( $collected as $post_obj ) {
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
	<?php mm_render_component( 'organisms', 'secao-titulo', array(
		'titulo' => $title,
	) ); ?>

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
