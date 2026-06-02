<?php
/**
 * Organism: Seção de Dubladores em Acordeão (secao-personagens-dubladores-accordion)
 *
 * Exibe a lista completa de dubladores (voice actors) do anime agrupados por idioma (ex: Japonês, Brasileiro).
 * Listagem organizada de forma decrescente pela importância/hierarquia dos papéis dos personagens.
 * Grid Responsivo: 3 colunas no desktop/tablet e 2 colunas no mobile.
 * Paginação Dinâmica Computada: Limite inicial de 5 linhas visíveis, com botões "Ver mais" e "Ver menos".
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo     = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : __( 'Dubladores', 'geek-ao-cubo' );
$grupos     = isset( $args['grupos'] ) ? (array) $args['grupos'] : array();
$dubladores = isset( $args['dubladores'] ) ? (array) $args['dubladores'] : array();

if ( empty( $grupos ) && ! empty( $dubladores ) ) {
	foreach ( $dubladores as $dublador ) {
		$dublador_arr = (array) $dublador;
		$lang = isset( $dublador_arr['va_language'] ) ? trim( $dublador_arr['va_language'] ) : 'Japonês';

		if ( strtolower( $lang ) === 'japanese' || strtolower( $lang ) === 'japonês' || strtolower( $lang ) === 'japones' ) {
			$grupo_nome = __( 'Dublagem Japonesa (Original)', 'geek-ao-cubo' );
		} elseif ( strtolower( $lang ) === 'portuguese' || strtolower( $lang ) === 'português' || strtolower( $lang ) === 'portugues' || strtolower( $lang ) === 'brazilian' ) {
			$grupo_nome = __( 'Dublagem Brasileira (Nacional)', 'geek-ao-cubo' );
		} else {
			$grupo_nome = sprintf( __( 'Dublagem %s', 'geek-ao-cubo' ), $lang );
		}

		$grupos[ $grupo_nome ][] = $dublador_arr;
	}
}

if ( empty( $grupos ) ) {
	return;
}
?>

<section class="secao-personagens-dubladores-accordion" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-personagens-dubladores-accordion__inner">

		<?php mm_render_component( 'organisms', 'secao-titulo', array(
			'titulo' => $titulo,
		) ); ?>

		<div class="secao-personagens-dubladores-accordion__list">
			<?php
			$grupo_idx = 0;
			foreach ( $grupos as $grupo_nome => $dubladores ) :
				if ( empty( $dubladores ) ) {
					continue;
				}

				$grupo_id = 'grupo-dub-' . sanitize_title( $grupo_nome );
				// O primeiro acordeão inicia aberto por padrão para melhor UX
				$is_open  = ( 0 === $grupo_idx ) ? 'open' : '';
				$grupo_idx++;
				$total_cards = count( $dubladores );
			?>
				<div class="secao-personagens-dubladores-accordion__item js-accordion-item" <?php echo $is_open ? 'data-state="open"' : ''; ?>>
					
					<!-- Gatilho do Acordeão (Acessível) -->
					<button 
						type="button" 
						class="secao-personagens-dubladores-accordion__trigger js-accordion-trigger" 
						aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
						aria-controls="<?php echo $grupo_id; ?>"
					>
						<span class="secao-personagens-dubladores-accordion__trigger-title"><?php echo esc_html( $grupo_nome ); ?></span>
						<span class="secao-personagens-dubladores-accordion__trigger-badge">
							<?php echo sprintf( _n( '%s integrante', '%s integrantes', $total_cards, 'geek-ao-cubo' ), $total_cards ); ?>
						</span>
						<svg class="secao-personagens-dubladores-accordion__trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<polyline points="6 9 12 15 18 9"></polyline>
						</svg>
					</button>

					<!-- Conteúdo do Acordeão -->
					<div 
						id="<?php echo $grupo_id; ?>" 
						class="secao-personagens-dubladores-accordion__content js-accordion-content"
						role="region"
						style="<?php echo $is_open ? '' : 'display: none;'; ?>"
					>
						<div class="secao-personagens-dubladores-accordion__content-inner">
							
							<!-- Grid de Cards de Dubladores -->
							<div class="secao-personagens-dubladores-accordion__grid js-dub-grid">
								<?php foreach ( $dubladores as $card_idx => $dublador ) : 
									$dub_array = (array) $dublador;
									
									$va_mal_id = isset( $dub_array['va_mal_id'] ) ? (int) $dub_array['va_mal_id'] : 0;
									$local_dub = mm_get_local_dublador_by_mal_id( $va_mal_id );
									
									if ( $local_dub && ! empty( $local_dub['url'] ) ) {
										$dub_array['va_url'] = $local_dub['url'];
									} else {
										$va_name = $dub_array['va_name'] ?? '';
										$dub_array['va_url'] = home_url( '/?s=' . urlencode( $va_name ) );
									}
									
									// Fallback para character_name vazio (cache antigo da Jikan)
									if ( empty( $dub_array['character_name'] ) ) {
										$dub_array['character_name'] = __( 'Personagem', 'geek-ao-cubo' );
									}
								?>
									<div class="js-dub-card" data-index="<?php echo $card_idx; ?>">
										<?php
										mm_render_component( 'molecules', 'card-personagem-dublador', $dub_array );
										?>
									</div>
								<?php endforeach; ?>
							</div>

							<!-- Ações de Paginação (Controladas Dinamicamente via JS) -->
							<div class="secao-personagens-dubladores-accordion__actions js-dub-actions" style="display: none;">
								
								<button 
									type="button" 
									class="btn-dub-action btn-dub-action--more js-dub-more"
									data-total="<?php echo $total_cards; ?>"
									data-current="0"
								>
									<span><?php _e( 'Ver mais', 'geek-ao-cubo' ); ?></span>
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<polyline points="6 9 12 15 18 9"></polyline>
									</svg>
								</button>
								
								<button 
									type="button" 
									class="btn-dub-action btn-dub-action--less js-dub-less"
									style="display: none;"
								>
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<polyline points="18 15 12 9 6 15"></polyline>
									</svg>
									<span><?php _e( 'Ver menos', 'geek-ao-cubo' ); ?></span>
								</button>

							</div>

						</div>
					</div>
					
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
