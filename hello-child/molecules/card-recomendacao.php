<?php
/**
 * Molecule: Card de Anime Recomendado (card-recomendacao)
 *
 * Card horizontal compacto para exibir um anime recomendado.
 * Layout: [thumbnail poster 2:3] [título (até 2 linhas) + contador de recomendações]
 * O card inteiro é clicável se anime_url for fornecido.
 *
 * @package hello-elementor-child
 *
 * @param string $anime_title Título do anime (obrigatório).
 * @param string $anime_image URL da capa do anime (opcional — exibe fallback se ausente).
 * @param string $anime_url   URL da página do anime (opcional — torna o card clicável).
 * @param int    $rec_count   Número de recomendações (opcional — omitido se 0 ou ausente).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$anime_title = isset( $args['anime_title'] ) ? esc_html( $args['anime_title'] )  : '';
$anime_image = isset( $args['anime_image'] ) ? esc_url( $args['anime_image'] )   : '';
$anime_url   = isset( $args['anime_url'] )   ? esc_url( $args['anime_url'] )     : '';
$rec_count   = isset( $args['rec_count'] )   ? (int) $args['rec_count']          : 0;

if ( empty( $anime_title ) ) {
	return;
}

$tag   = ! empty( $anime_url ) ? 'a' : 'div';
$attrs = ! empty( $anime_url )
	? sprintf(
		'href="%s" aria-label="%s" ',
		$anime_url,
		esc_attr( sprintf( __( 'Ver recomendação: %s', 'hello-elementor-child' ), $anime_title ) )
	)
	: '';
?>

<<?php echo $tag; ?> <?php echo $attrs; ?>class="card-recomendacao">

	<div class="card-recomendacao__thumb">
		<?php
		mm_render_component( 'atoms', 'imagem-capa', array(
			'src'   => $anime_image,
			'alt'   => sprintf( __( 'Capa de %s', 'hello-elementor-child' ), $anime_title ),
			'class' => 'card-recomendacao__imagem',
		) );
		?>
	</div>

	<div class="card-recomendacao__info">
		<span class="card-recomendacao__title"><?php echo $anime_title; ?></span>
		<?php if ( $rec_count > 0 ) : ?>
			<span class="card-recomendacao__count">
				<svg class="card-recomendacao__count-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M11 7C12.6569 7 14 5.65685 14 4C14 2.34315 12.6569 1 11 1C9.34315 1 8 2.34315 8 4C8 5.65685 9.34315 7 11 7Z" fill="currentColor" opacity="0.6"/>
					<path d="M5 8C6.65685 8 8 6.65685 8 5C8 3.34315 6.65685 2 5 2C3.34315 2 2 3.34315 2 5C2 6.65685 3.34315 8 5 8Z" fill="currentColor"/>
					<path d="M5 9C2.79086 9 1 10.7909 1 13V15H9V13C9 10.7909 7.20914 9 5 9Z" fill="currentColor"/>
					<path d="M11 8C9.76 8 8.63 8.45 7.76 9.2C8.54 10.06 9 11.18 9 12.4V15H15V13C15 10.7909 13.2091 9 11 9C10.98 9 10.97 8.999 10.95 8.998" fill="currentColor" opacity="0.6"/>
				</svg>
				<?php echo number_format_i18n( $rec_count ); ?> <?php _e( 'recomendações', 'hello-elementor-child' ); ?>
			</span>
		<?php endif; ?>
	</div>

</<?php echo $tag; ?>>
