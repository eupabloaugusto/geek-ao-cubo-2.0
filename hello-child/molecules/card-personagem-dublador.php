<?php
/**
 * Molecule: Card de Dublador (card-personagem-dublador)
 *
 * Card do voice actor com foto circular em destaque.
 * Mobile/tablet: flex-column centralizado (avatar em cima, info abaixo).
 * Desktop (≥ 64rem): flex-row (avatar esquerda, info direita).
 *
 * @package hello-elementor-child
 *
 * @param string $va_name         Nome do dublador (obrigatório).
 * @param string $va_image        URL da foto do dublador.
 * @param string $va_url          URL do perfil MAL do dublador (opcional).
 * @param string $va_language     Idioma. Default: 'Japonês'.
 * @param string $character_name  Nome do personagem dublado (opcional).
 * @param int    $episodios        Nº de episódios participados (opcional).
 * @param int    $ano_inicio       Ano de início na obra (opcional).
 * @param int    $ano_fim          Ano de fim na obra (opcional).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$va_name        = isset( $args['va_name'] )        ? esc_html( $args['va_name'] )        : '';
$va_image       = isset( $args['va_image'] )       ? esc_url( $args['va_image'] )        : '';
$va_url         = isset( $args['va_url'] )         ? esc_url( $args['va_url'] )          : '';
$va_language    = isset( $args['va_language'] )    ? esc_html( $args['va_language'] )    : __( 'Japonês', 'hello-elementor-child' );
$character_name = isset( $args['character_name'] ) ? esc_html( $args['character_name'] ) : '';
$episodios      = isset( $args['episodios'] )      ? intval( $args['episodios'] )        : 0;
$ano_inicio     = isset( $args['ano_inicio'] )     ? intval( $args['ano_inicio'] )       : 0;
$ano_fim        = isset( $args['ano_fim'] )        ? intval( $args['ano_fim'] )          : 0;

if ( empty( $va_name ) ) {
	return;
}

$meta_parts = array();

if ( $episodios > 0 ) {
	$meta_parts[] = sprintf(
		_n( '%d episódio', '%d episódios', $episodios, 'hello-elementor-child' ),
		$episodios
	);
}

if ( $ano_inicio > 0 && $ano_fim > 0 ) {
	$meta_parts[] = $ano_inicio . '–' . $ano_fim;
} elseif ( $ano_inicio > 0 ) {
	$meta_parts[] = $ano_inicio . '–';
} elseif ( $ano_fim > 0 ) {
	$meta_parts[] = $ano_fim;
}

$meta_str = ! empty( $meta_parts ) ? implode( ' • ', $meta_parts ) : '';

$tag   = ! empty( $va_url ) ? 'a' : 'article';
$attrs = ! empty( $va_url )
	? sprintf(
		'href="%s" aria-label="%s"',
		$va_url,
		esc_attr( sprintf( __( 'Ver perfil do dublador: %s', 'hello-elementor-child' ), $va_name ) )
	)
	: '';
?>

<<?php echo $tag; ?> <?php echo $attrs; ?> class="card-personagem-dublador" itemscope itemtype="https://schema.org/Person">

	<div class="card-personagem-dublador__avatar-wrap">
		<?php
		mm_render_component( 'atoms', 'avatar-personagem', array(
			'image_url'      => $va_image,
			'character_name' => $va_name,
			'size'           => 80,
			'class'          => 'card-personagem-dublador__avatar',
		) );
		?>
	</div>

	<div class="card-personagem-dublador__info">

		<span class="card-personagem-dublador__name" itemprop="name"><?php echo $va_name; ?></span>

		<?php if ( ! empty( $character_name ) ) : ?>
			<span class="card-personagem-dublador__character"><?php echo $character_name; ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $va_language ) ) : ?>
			<span class="card-personagem-dublador__language"><?php echo $va_language; ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $meta_str ) ) : ?>
			<span class="card-personagem-dublador__meta"><?php echo esc_html( $meta_str ); ?></span>
		<?php endif; ?>

	</div>

</<?php echo $tag; ?>>
