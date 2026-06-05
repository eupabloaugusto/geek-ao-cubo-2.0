<?php
/**
 * Organism: Secao Titulo (secao-titulo)
 *
 * Cabeçalho de seção unificado para o portal Geek ao Cubo.
 * Gerencia o título (átomo), sub-badges/contadores opcionais na esquerda,
 * e um botão/link opcional "Ver tudo" na direita.
 *
 * @package geek-ao-cubo
 * @since   4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo       = ! empty( $args['titulo'] ) ? $args['titulo'] : '';
$sub_badge    = ! empty( $args['sub_badge'] ) ? $args['sub_badge'] : '';
$ver_tudo_url = ! empty( $args['ver_tudo_url'] ) ? $args['ver_tudo_url'] : '';
$ver_tudo_lbl = ! empty( $args['ver_tudo_lbl'] ) ? $args['ver_tudo_lbl'] : __( 'Ver tudo', 'geek-ao-cubo' );
$class        = ! empty( $args['class'] ) ? $args['class'] : '';
$tag          = ! empty( $args['tag'] ) ? sanitize_key( $args['tag'] ) : 'h2';
$badge_extra  = ! empty( $args['badge_extra'] ) ? $args['badge_extra'] : '';
$titulo_class = ! empty( $args['titulo_class'] ) ? sanitize_html_class( $args['titulo_class'] ) : 'secao-titulo__main';
?>
<header class="secao-titulo <?php echo esc_attr( $class ); ?>">
	<div class="secao-titulo__left">
		<?php mm_render_component( 'atoms', 'titulo', array(
			'texto' => $titulo,
			'tag'   => $tag,
			'class' => $titulo_class,
		) ); ?>
		
		<?php if ( ! empty( $sub_badge ) ) : ?>
			<span class="secao-titulo__sub-badge"><?php echo esc_html( $sub_badge ); ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $badge_extra ) ) : ?>
			<?php echo wp_kses_post( $badge_extra ); ?>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $ver_tudo_url ) ) : ?>
		<a href="<?php echo esc_url( $ver_tudo_url ); ?>" class="secao-titulo__link-all" aria-label="<?php echo esc_attr( sprintf( __( 'Ver todos de %s', 'geek-ao-cubo' ), $titulo ) ); ?>">
			<span class="secao-titulo__link-text"><?php echo esc_html( $ver_tudo_lbl ); ?></span>
			<svg class="secao-titulo__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
				<polyline points="9 18 15 12 9 6"></polyline>
			</svg>
		</a>
	<?php endif; ?>
</header>
