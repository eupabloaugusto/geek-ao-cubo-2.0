<?php
/**
 * Atom: Vídeo Incorporado (embed-video)
 *
 * Exibe um wrapper de vídeo responsivo com carregamento sob demanda (facade-pattern) para otimização de performance.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$video_id        = isset( $args['video_id'] ) ? sanitize_text_field( $args['video_id'] ) : '';
$placeholder_url = isset( $args['placeholder_url'] ) ? esc_url( $args['placeholder_url'] ) : '';
$title           = isset( $args['title'] ) ? esc_attr( $args['title'] ) : __( 'Trailer do Anime', 'geek-ao-cubo' );
$class           = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

if ( empty( $video_id ) ) {
	return;
}

// Fallback para thumbnail padrão do YouTube em alta resolução se não for fornecida imagem customizada
if ( empty( $placeholder_url ) ) {
	$placeholder_url = "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
}

// Identificador único da instância para possibilitar múltiplos vídeos na mesma página sem conflitos de ID
$unique_id = uniqid( 'video-' );
?>
<div class="embed-video <?php echo $class; ?>" id="<?php echo $unique_id; ?>" data-video-id="<?php echo $video_id; ?>">
	<!-- Placeholder de Performance (Lightbox/Facade) -->
	<div class="embed-video__placeholder" style="background-image: url('<?php echo $placeholder_url; ?>');" onclick="mm_load_video_iframe('<?php echo $unique_id; ?>', '<?php echo $video_id; ?>', '<?php echo $title; ?>')">
		<div class="embed-video__overlay"></div>
		
		<button type="button" class="embed-video__play-btn" aria-label="<?php echo esc_attr( sprintf( __( 'Assistir vídeo: %s', 'geek-ao-cubo' ), $title ) ); ?>">
			<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path d="M8 5v14l11-7z"/>
			</svg>
		</button>
	</div>
</div>
