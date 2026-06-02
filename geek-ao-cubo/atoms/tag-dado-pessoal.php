<?php
/**
 * Atom: Tag Dado Pessoal (tag-dado-pessoal)
 *
 * Pílula visual para dados rápidos de um perfil (Aniversário, Idade, etc).
 *
 * @package geek-ao-cubo
 *
 * @param string $label  Rótulo (ex: "Aniversário").
 * @param string $valor  Valor (ex: "24 de Junho").
 * @param string $icone  Nome do ícone (opcional).
 * @param string $class  Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label = isset( $args['label'] ) ? esc_html( $args['label'] ) : '';
$valor = isset( $args['valor'] ) ? esc_html( $args['valor'] ) : '';
$icone = isset( $args['icone'] ) ? esc_attr( $args['icone'] ) : '';
$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

if ( empty( $valor ) ) {
	return;
}
?>
<div class="tag-dado-pessoal <?php echo $class; ?>">
	<?php if ( ! empty( $icone ) ) : ?>
		<span class="tag-dado-pessoal__icone">
			<?php mm_render_component( 'atoms', 'icone', array( 'name' => $icone ) ); ?>
		</span>
	<?php endif; ?>
	
	<div class="tag-dado-pessoal__textos">
		<?php if ( ! empty( $label ) ) : ?>
			<span class="tag-dado-pessoal__label"><?php echo $label; ?></span>
		<?php endif; ?>
		<strong class="tag-dado-pessoal__valor"><?php echo $valor; ?></strong>
	</div>
</div>
