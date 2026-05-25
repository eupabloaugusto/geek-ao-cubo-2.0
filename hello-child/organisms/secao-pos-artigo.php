<?php
/**
 * Organism: Seção Pós-Artigo (secao-pos-artigo)
 *
 * Seção master pós-conteúdo.
 * Consolida a circulação interna editorial (Leia Também) e a monetização/afiliados (Assistir Agora).
 *
 * No desktop exibe um layout assimétrico de duas colunas (Grid/Sidebar).
 * No mobile, empilha as peças exibindo o 'Assistir Agora' no topo para alta visibilidade.
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Resolução e Higienização de Parâmetros
$related_args = isset( $args['related_args'] ) ? $args['related_args'] : array();
$stream_args  = isset( $args['stream_args'] ) ? $args['stream_args'] : array();
$class        = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

?>
<div class="secao-pos-artigo <?php echo $class; ?>">
	<!-- 1. Coluna Principal: Grade de Posts Relacionados -->
	<div class="secao-pos-artigo__primary">
		<?php mm_render_component( 'organisms', 'secao-leia-tambem', $related_args ); ?>
	</div>

	<!-- 2. Coluna Lateral: Chamada de Streaming (Assistir Agora) -->
	<div class="secao-pos-artigo__secondary">
		<?php mm_render_component( 'molecules', 'sidebar-assistir-agora', $stream_args ); ?>
	</div>
</div>
