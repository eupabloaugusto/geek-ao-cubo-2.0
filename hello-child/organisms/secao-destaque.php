<?php
/**
 * Organism: Seção de Destaque (secao-destaque)
 *
 * Seção de destaque principal para a homepage do blog, organizando
 * 1 Card Destaque / Hero gigante na esquerda e 3 Cards Variação Lista na direita.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Extração e Validação dos Parâmetros
$post_hero     = isset( $args['post_hero'] ) ? $args['post_hero'] : array();
$posts_sidebar = isset( $args['posts_sidebar'] ) ? $args['posts_sidebar'] : array();

// Ignora renderização se os dados do Hero principal estiverem vazios
if ( empty( $post_hero ) ) {
	return;
}
?>

<section class="secao-destaque" aria-label="<?php esc_attr_e( 'Notícias em Destaque', 'hello-elementor-child' ); ?>">
	
	<!-- Coluna da Esquerda: Manchete Principal (Hero Card) -->
	<div class="secao-destaque__main">
		<?php 
		// Força a variação Hero Vertical para o destaque principal
		$post_hero['variacao'] = 'hero-vertical';
		mm_render_component( 'molecules', 'card-noticia', $post_hero ); 
		?>
	</div>

	<!-- Coluna da Direita: Stack de 3 Recomendações Secundárias (List Cards) -->
	<?php if ( ! empty( $posts_sidebar ) && is_array( $posts_sidebar ) ) : ?>
		<div class="secao-destaque__sidebar">
			<?php 
			// Renderiza até 3 cards secundários em formato lista
			$count = 0;
			foreach ( $posts_sidebar as $post_sidebar_args ) {
				if ( $count >= 3 ) {
					break;
				}
				$post_sidebar_args['variacao'] = 'list';
				mm_render_component( 'molecules', 'card-noticia', $post_sidebar_args );
				$count++;
			}
			?>
		</div>
	<?php endif; ?>

</section>
