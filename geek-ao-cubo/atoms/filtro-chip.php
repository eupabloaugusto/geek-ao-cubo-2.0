<?php
/**
 * Atom: Chip de Filtro (filtro-chip)
 *
 * Pílula selecionável para filtros de busca no catálogo de animes.
 * Renderiza um <label> visível com <input> oculto (checkbox ou radio).
 * A seleção é persistida via CSS :has() e classe --ativo.
 *
 * @package geek-ao-cubo
 *
 * @param string $label   Texto exibido no chip.
 * @param string $name    Atributo name do input.
 * @param string $value   Valor enviado ao filtrar.
 * @param string $tipo    Tipo do input: 'checkbox' (multi) ou 'radio' (único). Default: 'checkbox'.
 * @param bool   $checked Se o chip deve iniciar selecionado.
 * @param string $class   Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label   = isset( $args['label'] )   ? esc_html( $args['label'] )   : '';
$name    = isset( $args['name'] )    ? esc_attr( $args['name'] )    : 'filtro';
$value   = isset( $args['value'] )   ? esc_attr( $args['value'] )   : '';
$tipo    = isset( $args['tipo'] )    ? esc_attr( $args['tipo'] )    : 'checkbox';
$checked = isset( $args['checked'] ) ? (bool) $args['checked']      : false;
$class   = isset( $args['class'] )   ? esc_attr( $args['class'] )   : '';

if ( empty( $label ) ) {
	return;
}

$chip_id = 'chip-' . sanitize_key( $name . '-' . $value );
$ativo   = $checked ? ' filtro-chip--ativo' : '';
?>
<label class="filtro-chip<?php echo $ativo; ?> <?php echo $class; ?>" for="<?php echo $chip_id; ?>">
	<input
		type="<?php echo $tipo; ?>"
		id="<?php echo $chip_id; ?>"
		name="<?php echo $name; ?>"
		value="<?php echo $value; ?>"
		class="filtro-chip__input"
		<?php echo $checked ? 'checked' : ''; ?>
	>
	<?php echo $label; ?>
</label>
