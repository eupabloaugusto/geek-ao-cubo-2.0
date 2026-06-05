<?php
/**
 * Organism: Seção de Esteira de Personagens (secao-esteira-personagens)
 *
 * Seção horizontal estilo "esteira/carousel" premium para personagens.
 * Combina rolagem nativa gestual/touch (Core Web Vitals friendly) com setas reativas no desktop.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Higienização e Validação dos Argumentos
$titulo_secao  = isset( $args['titulo_secao'] ) ? esc_html( $args['titulo_secao'] ) : '';
$url_ver_todos = isset( $args['url_ver_todos'] ) ? esc_url( $args['url_ver_todos'] ) : '';
$personagens   = isset( $args['personagens'] ) ? $args['personagens'] : array();
$anime_slug    = isset( $args['anime_slug'] ) ? sanitize_title( $args['anime_slug'] ) : get_post_field( 'post_name', get_the_ID() );
if ( ! $anime_slug ) {
	$anime_slug = 'anime';
}

// Impede a renderização se a lista estiver vazia
if ( empty( $personagens ) || ! is_array( $personagens ) ) {
	return;
}
?>

<section class="secao-esteira-personagens" aria-label="<?php echo esc_attr( $titulo_secao ); ?>">

	<!-- 1. Cabeçalho de Título + Link "Ver Todos" -->
	<?php if ( ! empty( $titulo_secao ) ) : ?>
		<?php mm_render_component( 'organisms', 'secao-titulo', array(
			'titulo'       => $titulo_secao,
			'ver_tudo_url' => $url_ver_todos,
			'ver_tudo_lbl' => __( 'Ver Todos', 'geek-ao-cubo' ),
		) ); ?>
	<?php endif; ?>

	<!-- 2. Trilho com setas e scroll infinito (molécula trilho-infinito) -->
	<?php
	ob_start();
	foreach ( $personagens as $char ) :
		// Compatibilidade com o retorno da API Jikan /characters
		$char_name = $char['character']['name'] ?? '';
		$char_img  = $char['character']['images']['webp']['image_url'] ?? ( $char['character']['images']['jpg']['image_url'] ?? '' );
		$char_id   = $char['character']['mal_id'] ?? 0;
		$char_role = $char['role'] ?? '';
		
		if ( empty( $char_name ) || empty( $char_img ) ) continue;

		echo '<div class="secao-esteira-personagens__slide js-trilho__slide">';
		mm_render_component( 'molecules', 'card-anime-personagem', array(
			'title'     => $char_name,
			'image_url' => $char_img,
			'permalink' => home_url( '/' . $anime_slug . '/personagem/' . sanitize_title( $char_name ) . '/' ),
			'role'      => $char_role,
		) );
		echo '</div>';
	endforeach;
	$track_html = ob_get_clean();

	mm_render_component( 'molecules', 'trilho-infinito', array(
		'track_html'  => $track_html,
		'class'       => 'secao-esteira-personagens__wrapper',
		'track_class' => 'secao-esteira-personagens__track',
	) );
	?>

</section>
