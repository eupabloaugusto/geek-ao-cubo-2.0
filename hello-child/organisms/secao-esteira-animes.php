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

?>

<section class="secao-esteira-animes" aria-label="<?php echo esc_attr( $titulo_secao ); ?>">

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

	<!-- 2. Trilho com setas e scroll infinito (molécula trilho-infinito) -->
	<?php
	ob_start();
	foreach ( $animes as $anime_args ) :
		$anime_args['horario'] = '';
		echo '<div class="secao-esteira-animes__slide js-trilho__slide">';
		mm_render_component( 'molecules', 'card-anime', $anime_args );
		echo '</div>';
	endforeach;
	$track_html = ob_get_clean();

	mm_render_component( 'molecules', 'trilho-infinito', array(
		'track_html'  => $track_html,
		'class'       => 'secao-esteira-animes__wrapper',
		'track_class' => 'secao-esteira-animes__track',
	) );
	?>

</section>
