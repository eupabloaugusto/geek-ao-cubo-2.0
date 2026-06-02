<?php
/**
 * Molecule: Meta Artigo Header (meta-artigo-header)
 *
 * Agrupa de forma semântica e fluida:
 * - badge-categoria (átomo)
 * - meta-autor (átomo)
 * - meta-data (átomo)
 * Ideal para o cabeçalho de posts únicos no blog.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$category_args = isset( $args['category'] ) ? $args['category'] : array();
$author_args   = isset( $args['author'] ) ? $args['author'] : array();
$date_args     = isset( $args['date'] ) ? $args['date'] : array();
$class         = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
?>
<div class="meta-artigo-header <?php echo $class; ?>">
	<!-- 1. Etiqueta de Categoria -->
	<div class="meta-artigo-header__category">
		<?php mm_render_component( 'atoms', 'badge-categoria', $category_args ); ?>
	</div>

	<!-- 2. Grupo de Publicação (Autor + Divisor + Data) -->
	<div class="meta-artigo-header__meta-group">
		<?php mm_render_component( 'atoms', 'meta-autor', $author_args ); ?>
		<span class="meta-artigo-header__divider" aria-hidden="true">•</span>
		<?php mm_render_component( 'atoms', 'meta-data', $date_args ); ?>
	</div>
</div>
