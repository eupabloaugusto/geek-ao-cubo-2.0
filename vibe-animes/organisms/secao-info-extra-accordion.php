<?php
/**
 * Organism: Seção de Informações Adicionais em Acordeão (secao-info-extra-accordion)
 *
 * Exibe as informações extras extraídas da biografia (Físico, Habilidades, etc)
 * em um formato dinâmico e organizado de sanfona (accordion).
 * O primeiro item inicia aberto.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : __( 'Informações Adicionais', 'geek-ao-cubo' );
$secoes = isset( $args['secoes'] ) ? (array) $args['secoes'] : array();

if ( empty( $secoes ) ) {
	return;
}
?>

<section class="secao-info-extra-accordion" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-info-extra-accordion__inner">

		<?php mm_render_component( 'organisms', 'secao-titulo', array(
			'titulo' => $titulo,
		) ); ?>

		<div class="secao-info-extra-accordion__list">
			<?php
			$idx = 0;
			foreach ( $secoes as $secao_nome => $conteudo ) :
				if ( empty( $conteudo ) ) {
					continue;
				}

				$grupo_id = 'info-extra-' . sanitize_title( $secao_nome );
				$is_open  = ( 0 === $idx ) ? 'open' : '';
				$idx++;
			?>
				<div class="secao-info-extra-accordion__item js-accordion-item" <?php echo $is_open ? 'data-state="open"' : ''; ?>>
					
					<button 
						type="button" 
						class="secao-info-extra-accordion__trigger js-accordion-trigger" 
						aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
						aria-controls="<?php echo $grupo_id; ?>"
					>
						<span class="secao-info-extra-accordion__trigger-title"><?php echo esc_html( $secao_nome ); ?></span>
						<svg class="secao-info-extra-accordion__trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<polyline points="6 9 12 15 18 9"></polyline>
						</svg>
					</button>

					<div 
						id="<?php echo $grupo_id; ?>" 
						class="secao-info-extra-accordion__content js-accordion-content"
						role="region"
						style="<?php echo $is_open ? '' : 'display: none;'; ?>"
					>
						<div class="secao-info-extra-accordion__content-inner">
							<?php if ( is_array( $conteudo ) ) : ?>
								<ul class="secao-info-extra-accordion__lista">
									<?php foreach ( $conteudo as $lbl => $val ) : ?>
										<li><strong><?php echo esc_html( $lbl ); ?>:</strong> <?php echo esc_html( $val ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<div class="secao-info-extra-accordion__texto">
									<?php echo wpautop( esc_html( $conteudo ) ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
