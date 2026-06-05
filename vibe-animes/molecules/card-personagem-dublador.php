<?php
/**
 * Molecule: Card de Dublador (card-personagem-dublador)
 *
 * Card focado em exibir o dublador, visualmente semelhante à linha de personagem
 * do card-dublagem (caixa com fundo, foto à esquerda, textos à direita).
 *
 * @package geek-ao-cubo
 *
 * @param string $va_name         Nome do dublador (obrigatório).
 * @param string $va_image        URL da foto do dublador.
 * @param string $va_url          URL do perfil MAL do dublador (opcional).
 * @param string $va_language     Idioma. Default: 'Japonês'.
 * @param string $character_name  Nome do personagem.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$va_name        = isset( $args['va_name'] )        ? esc_html( $args['va_name'] )        : '';
$va_image       = isset( $args['va_image'] )       ? esc_url( $args['va_image'] )        : '';
$va_url         = isset( $args['va_url'] )         ? esc_url( $args['va_url'] )          : '';
$va_language    = isset( $args['va_language'] )    ? esc_html( $args['va_language'] )    : __( 'Japonês', 'geek-ao-cubo' );
$character_name = isset( $args['character_name'] ) ? esc_html( $args['character_name'] ) : '';

if ( empty( $va_name ) ) {
	return;
}

$tag   = ! empty( $va_url ) ? 'a' : 'article';
$attrs = ! empty( $va_url ) ? sprintf( 'href="%s" aria-label="%s"', $va_url, esc_attr( $va_name ) ) : '';
?>

<<?php echo $tag; ?> <?php echo $attrs; ?> class="card-personagem-dublador">
	
	<div class="card-personagem-dublador__img-wrapper">
		<?php if ( ! empty( $va_image ) ) : ?>
			<img src="<?php echo $va_image; ?>" alt="<?php echo esc_attr( $va_name ); ?>" class="card-personagem-dublador__img" loading="lazy">
		<?php else: ?>
			<div style="width:100%; height:100%; background:var(--neutral-700); display:flex; align-items:center; justify-content:center; color:var(--neutral-400); font-weight:bold; font-size:1.5rem;">
				<?php echo mb_substr( $va_name, 0, 1 ); ?>
			</div>
		<?php endif; ?>
	</div>
	
	<div class="card-personagem-dublador__info">
		<h3 class="card-personagem-dublador__nome"><?php echo $va_name; ?></h3>
		<span class="card-personagem-dublador__papel">
			<?php if ( ! empty( $character_name ) ) : ?>
				<span class="card-personagem-dublador__char"><?php echo $character_name; ?></span> &bull; 
			<?php endif; ?>
			<?php echo $va_language; ?>
		</span>
	</div>

</<?php echo $tag; ?>>
