<?php
/**
 * Atom: Badge de Gênero (badge-genero)
 *
 * Exibe uma tag ou pílula representativa do gênero do anime, clicável e com marcação acessível.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Validação dos Argumentos
$genero = isset( $args['genero'] ) ? esc_html( $args['genero'] ) : '';
$url    = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$class  = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Impede a renderização se o nome do gênero estiver vazio
if ( empty( $genero ) ) {
	return;
}
?>
<a href="<?php echo $url; ?>" class="badge-genero <?php echo $class; ?>" title="<?php echo esc_attr( sprintf( __( 'Ver mais animes de %s', 'geek-ao-cubo' ), $genero ) ); ?>">
	<?php echo $genero; ?>
</a>
