<?php
/**
 * Molecule: Card de Anime Recomendado (card-recomendacao)
 *
 * Card vertical no estilo card-anime: media link com imagem-capa (2:3) + bloco
 * de conteúdo com contador de recomendações e título.
 *
 * @package geek-ao-cubo
 *
 * @param string $anime_title Título do anime (obrigatório).
 * @param string $anime_image URL da capa do anime (opcional — fallback se ausente).
 * @param string $anime_url   URL da página do anime (opcional).
 * @param int    $rec_count   Número de recomendações (opcional — omitido se 0).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$anime_title = isset( $args['anime_title'] ) ? esc_html( $args['anime_title'] ) : '';
$anime_image = isset( $args['anime_image'] ) ? esc_url( $args['anime_image'] )  : '';
$anime_url   = isset( $args['anime_url'] )   ? esc_url( $args['anime_url'] )    : '#';
$rec_count   = isset( $args['rec_count'] )   ? (int) $args['rec_count']         : 0;

if ( empty( $anime_title ) ) {
	return;
}
?>

<div class="card-recomendacao">

	<!-- A. Capa (link + imagem-capa) -->
	<a href="<?php echo $anime_url; ?>" class="card-recomendacao__media" aria-label="<?php echo esc_attr( sprintf( __( 'Ver detalhes do anime recomendado %s', 'geek-ao-cubo' ), $anime_title ) ); ?>">
		<?php
		mm_render_component( 'atoms', 'imagem-capa', array(
			'src' => $anime_image,
			'alt' => sprintf( __( 'Capa do anime: %s', 'geek-ao-cubo' ), $anime_title ),
		) );
		?>
	</a>

	<!-- B. Conteúdo: contador + título -->
	<div class="card-recomendacao__content">

		<?php if ( $rec_count > 0 ) : ?>
			<div class="card-recomendacao__count">
				<svg class="card-recomendacao__count-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M11 7C12.6569 7 14 5.65685 14 4C14 2.34315 12.6569 1 11 1C9.34315 1 8 2.34315 8 4C8 5.65685 9.34315 7 11 7Z" fill="currentColor" opacity="0.6"/>
					<path d="M5 8C6.65685 8 8 6.65685 8 5C8 3.34315 6.65685 2 5 2C3.34315 2 2 3.34315 2 5C2 6.65685 3.34315 8 5 8Z" fill="currentColor"/>
					<path d="M5 9C2.79086 9 1 10.7909 1 13V15H9V13C9 10.7909 7.20914 9 5 9Z" fill="currentColor"/>
					<path d="M11 8C9.76 8 8.63 8.45 7.76 9.2C8.54 10.06 9 11.18 9 12.4V15H15V13C15 10.7909 13.2091 9 11 9Z" fill="currentColor" opacity="0.6"/>
				</svg>
				<?php echo number_format_i18n( $rec_count ); ?> <?php _e( 'recomendações', 'geek-ao-cubo' ); ?>
			</div>
		<?php endif; ?>

		<h3 class="card-recomendacao__title">
			<a href="<?php echo $anime_url; ?>" class="card-recomendacao__title-link">
				<?php echo $anime_title; ?>
			</a>
		</h3>

	</div>

</div>
