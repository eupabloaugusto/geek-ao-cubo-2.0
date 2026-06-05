<?php
/**
 * Organism: Lista de Publicações (lista-publicacoes)
 *
 * Exibe a listagem completa de publicações (posts), com ordenação e paginação.
 * Utiliza a molécula 'card-postagem'.
 *
 * @package vibe-animes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

$use_main_query = isset( $args['use_main_query'] ) ? $args['use_main_query'] : false;
$ordem          = isset( $_GET['ordem'] ) ? sanitize_text_field( wp_unslash( $_GET['ordem'] ) ) : 'recentes';

$has_extra_filters = ! empty( $_GET['busca'] )
	|| ! empty( $_GET['categoria_slug'] )
	|| ( isset( $_GET['ordem'] ) && 'recentes' !== $ordem );

// Main query só quando não há filtros extras e não estamos em busca global (?s=)
$should_use_main_query = $use_main_query && ! is_search() && ! $has_extra_filters;

if ( $should_use_main_query ) {
	global $wp_query;
	$loop  = $wp_query;
	$paged = max( 1, get_query_var( 'paged' ) );
} else {
	// ── Parâmetros da Query ───────────────────────────────────
	// Tentamos pegar via ?pg= para evitar o 404 da query nativa, senão usamos fallback
	$paged = isset( $_GET['pg'] ) ? max( 1, (int) wp_unslash( $_GET['pg'] ) ) : 1;

	if ( 1 === $paged ) {
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		}
	}

	$per_page = isset( $args['posts_per_page'] ) ? intval( $args['posts_per_page'] ) : 30;

	$query_args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'paged'          => $paged,
		'posts_per_page' => $per_page,
	);

	// Busca Textual (busca= no catálogo editorial; ?s= na busca global do WP)
	$search_term = '';
	if ( isset( $_GET['busca'] ) && '' !== $_GET['busca'] ) {
		$search_term = sanitize_text_field( wp_unslash( $_GET['busca'] ) );
	} elseif ( is_search() ) {
		$search_term = get_search_query();
	}
	if ( '' !== $search_term ) {
		$query_args['s'] = $search_term;
	}

	// Categoria
	if ( isset( $_GET['categoria_slug'] ) && ! empty( $_GET['categoria_slug'] ) ) {
		$query_args['category_name'] = sanitize_text_field( wp_unslash( $_GET['categoria_slug'] ) );
	}

	// Ordenação
	if ( 'antigos' === $ordem ) {
		$query_args['orderby'] = 'date';
		$query_args['order']   = 'ASC';
	} elseif ( 'alfabetica' === $ordem ) {
		$query_args['orderby'] = 'title';
		$query_args['order']   = 'ASC';
	} elseif ( 'populares' === $ordem ) {
		$query_args['orderby']  = 'comment_count'; // ou meta_value_num para views, se houver
		$query_args['order']    = 'DESC';
	} else {
		$query_args['orderby'] = 'date';
		$query_args['order']   = 'DESC'; // recentes (padrão)
	}

	$loop = new WP_Query( $query_args );
}
?>

<div class="lista-publicacoes js-ajax-container js-ajax-scroll-target <?php echo $class; ?>">

	<?php if ( $loop->have_posts() ) : ?>
		
		<div class="lista-publicacoes__grid js-ajax-replace" style="display: flex; flex-direction: column; gap: var(--space-200); margin-top: var(--space-400); max-width: var(--container-max); margin-inline: auto; padding: 0 var(--space-400);">
			
			<?php 
			$index = 0;
			while ( $loop->have_posts() ) : $loop->the_post(); 
				$index++;

				$tags = get_the_tags();
				$tag_name = $tags ? $tags[0]->name : '';
				if ( empty( $tag_name ) ) {
					$categories = get_the_category();
					$tag_name = $categories ? $categories[0]->name : '';
				}

				// O primeiro card ganha destaque (Desktop) se estiver na página 1 e ordenação por recentes, MAS NUNCA em resultados de busca
				$is_destaque = ( $index === 1 && $paged === 1 && $ordem === 'recentes' && ! is_search() && empty( $_GET['busca'] ) );
				$card_class = $is_destaque ? 'card-postagem--destaque' : '';

				mm_render_component( 'molecules', 'card-postagem', array(
					'titulo'     => get_the_title(),
					'url'        => get_permalink(),
					'post_id'    => get_the_ID(),
					'tag'        => $tag_name,
					'data'       => get_the_date(),
					'descricao'  => get_the_excerpt(),
					'class'      => $card_class,
				) );

				// Inserção de anúncios In-Line (Banner) dinâmicos a cada 5 posts
				if ( $index > 0 && $index % 5 === 0 ) {
					echo '<div class="lista-publicacoes__in-line-ad" style="margin: var(--space-200) 0; width: 100%;">';
					mm_render_component( 'atoms', 'anuncio-adsense', array(
						'slot'     => 'publicacoes-in-line-' . $index,
						'variacao' => 'banner',
					) );
					echo '</div>';
				}

			endwhile; 
			?>

		</div>

		<!-- Paginação -->
		<div class="lista-publicacoes__paginacao js-ajax-replace" style="margin-top: var(--space-600); max-width: var(--container-max); margin-inline: auto;">
			<?php
			$base_url = remove_query_arg( 'pg' );
			
			// Se for main query, usamos o padrão do WP. Se não for, usamos o pg para evitar 404
			$pagination_args = array(
				'query'         => $loop,
				'max_num_pages' => $loop->max_num_pages,
				'current_page'  => $paged,
			);
			
			if ( ! $should_use_main_query ) {
				$pagination_args['base']   = add_query_arg( 'pg', '%#%', $base_url );
				$pagination_args['format'] = '?pg=%#%';
			}

			mm_render_component( 'molecules', 'pagination', $pagination_args );
			?>
		</div>

		<!-- Anúncio Banner Base -->
		<div class="lista-publicacoes__ad-bottom" style="margin-top: var(--space-600); width: 100%; text-align: center;">
			<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
				'slot'     => 'publicacoes-bottom-banner',
				'variacao' => 'banner',
			) ); ?>
		</div>

	<?php else : ?>

		<div class="lista-publicacoes__vazio" style="text-align: center; padding: var(--space-600) var(--space-400); max-width: var(--container-max); margin: 0 auto;">
			<h2><?php esc_html_e( 'Nenhuma publicação encontrada.', 'vibe-animes' ); ?></h2>
			<p><?php esc_html_e( 'Tente alterar os filtros ou realizar uma nova busca.', 'vibe-animes' ); ?></p>
		</div>

	<?php endif; ?>

</div>

<?php 
wp_reset_postdata(); 
?>

