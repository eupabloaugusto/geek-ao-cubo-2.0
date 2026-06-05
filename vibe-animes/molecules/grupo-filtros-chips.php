<?php
/**
 * Molecule: Grupo de Chips de Filtro (grupo-filtros-chips)
 *
 * Fieldset semântico com legenda e uma linha de filtro-chips.
 * Suporta seleção única (radio) ou múltipla (checkbox).
 *
 * @package geek-ao-cubo
 *
 * @param string $titulo      Legenda do grupo (ex: "Gênero", "Status").
 * @param string $name        Atributo name base dos chips. Checkboxes recebem name[].
 * @param string $tipo        'checkbox' para multi-select, 'radio' para único. Default: 'checkbox'.
 * @param array  $opcoes      Array associativo slug => label das opções disponíveis.
 * @param array  $selecionados Array de slugs/valores atualmente selecionados.
 * @param string $class       Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo       = isset( $args['titulo'] )       ? esc_html( $args['titulo'] )  : '';
$name         = isset( $args['name'] )         ? esc_attr( $args['name'] )    : 'filtro';
$tipo         = isset( $args['tipo'] )         ? esc_attr( $args['tipo'] )    : 'checkbox';
$opcoes       = isset( $args['opcoes'] )       ? $args['opcoes']              : array();
$selecionados = isset( $args['selecionados'] ) ? (array) $args['selecionados'] : array();
$class        = isset( $args['class'] )        ? esc_attr( $args['class'] )   : '';

if ( empty( $opcoes ) ) {
	return;
}

// Checkboxes precisam de name[] para enviar múltiplos valores
$input_name = ( 'checkbox' === $tipo ) ? $name . '[]' : $name;

// Normaliza selecionados para comparação de string
$selecionados = array_map( 'strval', $selecionados );
?>
<fieldset class="grupo-filtros-chips <?php echo $class; ?>">
	<?php if ( ! empty( $titulo ) ) : ?>
		<legend class="grupo-filtros-chips__titulo"><?php echo $titulo; ?></legend>
	<?php endif; ?>

	<div class="grupo-filtros-chips__lista">
		<?php foreach ( $opcoes as $valor => $label ) :
			$is_checked = in_array( (string) $valor, $selecionados, true );
		?>
			<?php mm_render_component( 'atoms', 'filtro-chip', array(
				'label'   => $label,
				'name'    => $input_name,
				'value'   => $valor,
				'tipo'    => $tipo,
				'checked' => $is_checked,
			) ); ?>
		<?php endforeach; ?>
	</div>
</fieldset>
