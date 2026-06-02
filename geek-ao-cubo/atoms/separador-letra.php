<?php
/**
 * Atom: Separador de Letra (separador-letra)
 *
 * Divisor de seção alfabética no catálogo de animes.
 * Renderiza a letra inicial do grupo + linha horizontal abaixo.
 * Serve como âncora para scroll suave da nav-alfabetica.
 *
 * @package geek-ao-cubo
 *
 * @param string $letra Letra da seção (ex: 'N').
 * @param string $id    ID do elemento para âncora (ex: 'secao-n'). Default gerado da letra.
 * @param string $class Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$letra = isset( $args['letra'] ) ? strtoupper( esc_html( $args['letra'] ) ) : '';
$id    = isset( $args['id'] )    ? esc_attr( $args['id'] )                  : 'secao-' . strtolower( $letra );
$class = isset( $args['class'] ) ? esc_attr( $args['class'] )               : '';

if ( empty( $letra ) ) {
	return;
}
?>
<div class="separador-letra <?php echo $class; ?>" id="<?php echo $id; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Animes com letra %s', 'geek-ao-cubo' ), $letra ) ); ?>">
	<span class="separador-letra__letra" aria-hidden="true"><?php echo $letra; ?></span>
	<hr class="separador-letra__linha" aria-hidden="true">
</div>
