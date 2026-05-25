<?php
/**
 * Organism: Seção de Esteira de Animes (secao-esteira-animes)
 *
 * Seção horizontal estilo "esteira/carousel" premium para biblioteca de animes.
 * Combina rolagem nativa gestual/touch (Core Web Vitals friendly) com setas reativas no desktop.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Higienização e Validação dos Argumentos
$titulo_secao  = isset( $args['titulo_secao'] ) ? esc_html( $args['titulo_secao'] ) : '';
$url_ver_todos = isset( $args['url_ver_todos'] ) ? esc_url( $args['url_ver_todos'] ) : '';
$animes        = isset( $args['animes'] ) ? $args['animes'] : array();

// Impede a renderização se a lista de animes estiver vazia
if ( empty( $animes ) || ! is_array( $animes ) ) {
	return;
}

$esteira_id = 'esteira-animes-' . wp_rand( 1000, 9999 );
?>

<section class="secao-esteira-animes js-esteira-container" id="<?php echo esc_attr( $esteira_id ); ?>" aria-label="<?php echo esc_attr( $titulo_secao ); ?>">
	
	<!-- 1. Cabeçalho de Título + Link "Ver Todos" -->
	<?php if ( ! empty( $titulo_secao ) ) : ?>
		<header class="secao-esteira-animes__header">
			<h2 class="secao-esteira-animes__title">
				<?php echo $titulo_secao; ?>
			</h2>
			
			<?php if ( ! empty( $url_ver_todos ) ) : ?>
				<a href="<?php echo $url_ver_todos; ?>" class="secao-esteira-animes__link-all" aria-label="<?php echo esc_attr( sprintf( __( 'Ver todos os animes de: %s', 'hello-elementor-child' ), $titulo_secao ) ); ?>">
					<span><?php _e( 'Ver Todos', 'hello-elementor-child' ); ?></span>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="secao-esteira-animes__link-icon" aria-hidden="true">
						<polyline points="9 18 15 12 9 6"></polyline>
					</svg>
				</a>
			<?php endif; ?>
		</header>
	<?php endif; ?>

	<!-- 2. Trilho Slider com Setas Flutuantes -->
	<div class="secao-esteira-animes__wrapper">
		
		<!-- Seta Anterior -->
		<?php 
		mm_render_component( 'atoms', 'btn-nav-arrow', array( 
			'direction' => 'prev', 
			'class'     => 'secao-esteira-animes__arrow secao-esteira-animes__arrow--prev js-esteira-prev' 
		) ); 
		?>

		<!-- Trilho Físico de Rolagem Horizontal (Scroll Snap Track) -->
		<div class="secao-esteira-animes__track js-esteira-track">
			<?php foreach ( $animes as $anime_args ) : ?>
				<div class="secao-esteira-animes__slide js-esteira-slide">
					<?php 
					// Cards na esteira de catálogo não devem exibir o horário
					$anime_args['horario'] = '';
					mm_render_component( 'molecules', 'card-anime', $anime_args ); 
					?>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Seta Próxima -->
		<?php 
		mm_render_component( 'atoms', 'btn-nav-arrow', array( 
			'direction' => 'next', 
			'class'     => 'secao-esteira-animes__arrow secao-esteira-animes__arrow--next js-esteira-next' 
		) ); 
		?>

	</div>

</section>
