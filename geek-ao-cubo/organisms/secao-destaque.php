<?php
/**
 * Organism: Seção de Destaque (secao-destaque)
 *
 * Seção de destaque principal para a homepage do blog.
 * Desktop: Organiza 1 Card Destaque / Hero vertical na direita e 4 Cards Variação Grid na esquerda (2 colunas x 2 linhas).
 * Mobile/A11y-first: O Card de Destaque é declarado primeiro para renderizar no topo naturalmente.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Extração e Validação dos Parâmetros
$post_hero  = isset( $args['post_hero'] ) ? $args['post_hero'] : array();
$posts_grid = isset( $args['posts_grid'] ) ? $args['posts_grid'] : ( isset( $args['posts_sidebar'] ) ? $args['posts_sidebar'] : array() );

// Ignora renderização se os dados do Hero principal estiverem vazios
if ( empty( $post_hero ) ) {
	return;
}
?>

<section class="secao-destaque" aria-label="<?php esc_attr_e( 'Notícias em Destaque', 'geek-ao-cubo' ); ?>">
	
	<!-- Coluna Principal (Destaque): Hero Card (Lado Direito no Desktop) -->
	<div class="secao-destaque__main">
		<?php 
		// Força a variação Hero Vertical para o destaque principal
		$post_hero['variacao'] = 'hero-vertical';
		mm_render_component( 'molecules', 'card-noticia', $post_hero ); 
		?>
	</div>

	<!-- Coluna Lateral: Grade de 4 Cards em Variação Grid (Lado Esquerdo no Desktop) -->
	<?php if ( ! empty( $posts_grid ) && is_array( $posts_grid ) ) : ?>
		<div class="secao-destaque__grid">
			<?php 
			// Renderiza até 4 cards secundários em formato grid
			$count = 0;
			foreach ( $posts_grid as $post_grid_args ) {
				if ( $count >= 4 ) {
					break;
				}
				$post_grid_args['variacao'] = 'grid';
				mm_render_component( 'molecules', 'card-noticia', $post_grid_args );
				$count++;
			}
			?>
		</div>
	<?php endif; ?>

</section>
