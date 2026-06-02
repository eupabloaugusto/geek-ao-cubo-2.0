<?php
/**
 * Organism: Seção de Cronologia em Acordeão (Híbrido)
 *
 * Exibe a linha do tempo da franquia. 
 * Para a temporada atual, exibe a lista completa de episódios no acordeão aberto.
 * Para outras temporadas, exibe um "falso acordeão" que atua como link.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo           = isset( $args['titulo'] )           ? esc_html( $args['titulo'] )           : __( 'Cronologia', 'geek-ao-cubo' );
$franchise        = isset( $args['franchise'] )        ? (array) $args['franchise']        : array();
$current_type     = isset( $args['current_type'] )     ? $args['current_type']             : '';
$current_episodes = isset( $args['current_episodes'] ) ? (int) $args['current_episodes']   : 0;
$context          = isset( $args['context'] )          ? $args['context']                  : 'anime'; // 'anime' ou 'manga'

if ( empty( $franchise ) ) {
	return;
}
?>

<section class="secao-episodios-accordion" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-episodios-accordion__inner">

		<?php mm_render_component( 'organisms', 'secao-titulo', array(
			'titulo' => $titulo,
		) ); ?>

		<div class="secao-episodios-accordion__list">
			<?php 
			foreach ( $franchise as $idx => $item ) : 
				$arco_id = 'arco-' . sanitize_title( $item['title'] ) . '-' . $idx;
				
				if ( $item['is_current'] ) : 
					// Traz os episódios/capítulos apenas para a temporada/mangá atual
					$episodios = array();
					if ( 'manga' === $context ) {
						$manga_agg = isset( $args['manga_aggregate'] ) ? $args['manga_aggregate'] : array();
						if ( ! empty( $manga_agg['volumes'] ) ) {
							$all_caps = MangaDex_API::get_all_chapters( $manga_agg );
							foreach ( $all_caps as $cap ) {
								$vol_text = $cap['volume'] ? " (Vol. {$cap['volume']})" : '';
								$episodios[] = array(
									'numero'    => $cap['number'],
									'titulo'    => 'Capítulo ' . $cap['number'] . $vol_text,
									'data'      => '',
									'nota'      => '',
									'permalink' => $cap['url'],
									'filler'    => false,
									'recap'     => false,
								);
							}
						}
					} elseif ( $item['anime_id_mal'] > 0 ) {
						$jikan_eps = Jikan_API::get_anime_episodes( $item['anime_id_mal'] );
						if ( ! empty( $jikan_eps ) ) {
							foreach ( $jikan_eps as $ep ) {
								$data_estreia = '';
								if ( ! empty( $ep['aired'] ) ) {
									$ts = strtotime( $ep['aired'] );
									if ( $ts ) {
										$data_estreia = date_i18n( 'd/m/Y', $ts );
									}
								}
								$episodios[] = array(
									'numero'    => $ep['mal_id'] ?? '',
									'titulo'    => $ep['title'] ?? '',
									'data'      => $data_estreia,
									'nota'      => $ep['score'] ?? '',
									'permalink' => '',
									'filler'    => $ep['filler'] ?? false,
									'recap'     => $ep['recap'] ?? false,
								);
							}
							usort( $episodios, function( $a, $b ) {
								return (float) $a['numero'] <=> (float) $b['numero'];
							} );
						}
					}
			?>
					<div class="secao-episodios-accordion__item js-accordion-item secao-episodios-accordion__item--current" data-state="open">
						
						<!-- Gatilho do Acordeão (Temporada Atual) -->
						<button 
							type="button" 
							class="secao-episodios-accordion__trigger js-accordion-trigger" 
							aria-expanded="true"
							aria-controls="<?php echo $arco_id; ?>"
						>
							<span class="secao-episodios-accordion__trigger-title">
								<?php echo esc_html( $item['title'] ); ?>
							</span>
							<span class="secao-episodios-accordion__trigger-badge">
								<?php 
								if ( $current_type === 'Movie' ) {
									echo 'Filme';
								} elseif ( $current_type === 'TV Special' || $current_type === 'Special' ) {
									echo 'Especial';
								} elseif ( $current_type === 'OVA' || $current_type === 'ONA' ) {
									echo esc_html( $current_type );
								} else {
									$ep_count = $current_episodes > 0 ? $current_episodes : count( $episodios );
									if ( 'manga' === $context ) {
										echo sprintf( _n( '%s capítulo', '%s capítulos', $ep_count, 'geek-ao-cubo' ), $ep_count );
									} else {
										echo sprintf( _n( '%s episódio', '%s episódios', $ep_count, 'geek-ao-cubo' ), $ep_count );
									}
								}
								?>
							</span>
							<svg class="secao-episodios-accordion__trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<polyline points="6 9 12 15 18 9"></polyline>
							</svg>
						</button>
						
						<!-- Corpo do Acordeão (Episódios) -->
						<div 
							id="<?php echo $arco_id; ?>" 
							class="secao-episodios-accordion__content js-accordion-content"
							role="region"
						>
							<div class="secao-episodios-accordion__content-inner">
								<?php if ( empty( $episodios ) ) : ?>
									<div class="secao-episodios-accordion__empty" style="padding: var(--space-400); text-align: center; color: var(--neutral-300);">
										<?php if ( 'manga' === $context ) : ?>
											<p style="margin:0;"><?php esc_html_e( 'A listagem detalhada de capítulos não está disponível para este mangá no momento.', 'geek-ao-cubo' ); ?></p>
										<?php elseif ( in_array( $current_type, ['Movie', 'TV Special', 'Special', 'OVA', 'ONA'] ) ) : ?>
											<p style="margin:0;"><?php esc_html_e( 'Esta é uma obra de formato único (Filme/Especial), não possuindo divisão por episódios.', 'geek-ao-cubo' ); ?></p>
										<?php else : ?>
											<p style="margin:0;"><?php esc_html_e( 'Nenhum episódio cadastrado para esta temporada.', 'geek-ao-cubo' ); ?></p>
										<?php endif; ?>
									</div>
								<?php else : ?>
									<div class="secao-episodios-accordion__table-wrapper">
										<table class="secao-episodios-accordion__table">
											<thead>
												<tr>
													<th scope="col" class="col-num"><?php _e( 'Nº', 'geek-ao-cubo' ); ?></th>
													<th scope="col" class="col-title"><?php echo 'manga' === $context ? __( 'Capítulo', 'geek-ao-cubo' ) : __( 'Título do Episódio', 'geek-ao-cubo' ); ?></th>
													<?php if ( 'manga' !== $context ) : ?>
													<th scope="col" class="col-date"><?php _e( 'Estreia', 'geek-ao-cubo' ); ?></th>
													<th scope="col" class="col-score">
														<span class="col-score__desktop"><?php _e( 'Avaliação', 'geek-ao-cubo' ); ?></span>
														<span class="col-score__mobile"><?php _e( 'Nota', 'geek-ao-cubo' ); ?></span>
													</th>
													<?php endif; ?>
												</tr>
											</thead>
											<tbody class="js-episodes-list">
												<?php foreach ( $episodios as $ep_idx => $ep ) : 
													$date_str = $ep['data'];
													$date_parts = explode( '/', $date_str );
													if ( count( $date_parts ) === 3 ) {
														$day = $date_parts[0];
														$month = $date_parts[1];
														$year = $date_parts[2];
														$century = substr( $year, 0, 2 );
														$short_year = substr( $year, 2, 2 );
														$display_date = "{$day}/{$month}/<span class=\"col-date__century\">{$century}</span>{$short_year}";
													} else {
														$display_date = $date_str;
													}
												?>
													<tr class="js-ep-row" data-index="<?php echo $ep_idx; ?>" style="<?php echo $ep_idx >= 15 ? 'display: none;' : ''; ?>">
														<td class="col-num">
															<span class="ep-num-badge"><?php echo $ep['numero']; ?></span>
														</td>
														<td class="col-title">
															<?php if ( ! empty( $ep['permalink'] ) ) : ?>
																<a href="<?php echo esc_url( $ep['permalink'] ); ?>" class="ep-link" <?php echo 'manga' === $context ? 'target="_blank" rel="noopener"' : ''; ?>>
																	<?php echo esc_html( $ep['titulo'] ); ?>
																</a>
															<?php else : ?>
																<span class="ep-link ep-link--no-url"><?php echo esc_html( $ep['titulo'] ); ?></span>
															<?php endif; ?>
															<?php if ( ! empty( $ep['filler'] ) ) : ?>
																<span class="ep-badge ep-badge--filler"><?php esc_html_e( 'Filler', 'geek-ao-cubo' ); ?></span>
															<?php elseif ( ! empty( $ep['recap'] ) ) : ?>
																<span class="ep-badge ep-badge--recap"><?php esc_html_e( 'Recap', 'geek-ao-cubo' ); ?></span>
															<?php endif; ?>
														</td>
														<?php if ( 'manga' !== $context ) : ?>
														<td class="col-date">
															<?php
															$datetime_attr = '';
															if ( ! empty( $ep['data'] ) ) {
																$ts = strtotime( str_replace( '/', '-', $ep['data'] ) );
																if ( $ts ) {
																	$datetime_attr = date( 'Y-m-d', $ts );
																}
															}
															?>
															<time <?php echo $datetime_attr ? 'datetime="' . esc_attr( $datetime_attr ) . '"' : ''; ?>>
																<?php echo $display_date ?: '—'; ?>
															</time>
														</td>
														<td class="col-score">
															<?php if ( ! empty( $ep['nota'] ) && $ep['nota'] > 0 ) : ?>
																<?php
																mm_render_component( 'atoms', 'nota-mal', array(
																	'nota'  => number_format( (float) $ep['nota'], 2 ),
																	'class' => 'ep-nota',
																) );
																?>
															<?php else : ?>
																<span class="ep-nota-placeholder">—</span>
															<?php endif; ?>
														</td>
														<?php endif; ?>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>

									<?php if ( count( $episodios ) > 15 ) : ?>
										<div class="secao-episodios-accordion__actions">
											<?php
											mm_render_component( 'molecules', 'btn-pagination', array(
												'prefix'      => 'ep',
												'total_items' => count( $episodios ),
												'label_more'  => __( 'Ver mais episódios', 'geek-ao-cubo' )
											) );
											?>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>

					</div>
				
				<?php else : ?>
					
					<!-- Falso Acordeão (Link para outra temporada) -->
					<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="secao-episodios-accordion__item secao-episodios-accordion__item--link" style="display: block; text-decoration: none;">
						<div class="secao-episodios-accordion__trigger">
							<span class="secao-episodios-accordion__trigger-title"><?php echo esc_html( $item['title'] ); ?></span>
							
							<div style="display:flex; align-items:center; gap: var(--space-300);">
								<span class="secao-episodios-accordion__trigger-badge js-fetch-episodes" data-mal-id="<?php echo esc_attr( $item['anime_id_mal'] ); ?>">
									<span class="js-ep-count">...</span>
								</span>
								<span class="secao-episodios-accordion__trigger-icon" aria-hidden="true" style="opacity: 0.6;">
									<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
								</span>
							</div>
						</div>
					</a>

				<?php endif; ?>
				
			<?php endforeach; ?>
		</div>

	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const badges = document.querySelectorAll('.js-fetch-episodes');
    const context = '<?php echo esc_js( $context ); ?>';
    let delayQueue = 0;

    function renderBadge(badge, data) {
        const countSpan = badge.querySelector('.js-ep-count');
        if (!countSpan) return;
        
        if (data.type === 'Movie') {
            countSpan.textContent = 'Filme';
        } else if (data.type === 'TV Special' || data.type === 'Special') {
            countSpan.textContent = 'Especial';
        } else if (data.type === 'OVA' || data.type === 'ONA') {
            countSpan.textContent = data.type;
        } else if (data.episodes) {
            const label = context === 'manga' ? (data.episodes === 1 ? ' capítulo' : ' capítulos') : (data.episodes === 1 ? ' episódio' : ' episódios');
            countSpan.textContent = data.episodes + label;
        } else {
            countSpan.textContent = 'Em exibição';
        }
    }

    badges.forEach((badge) => {
        const malId = badge.getAttribute('data-mal-id');
        if (!malId) return;

        const cacheKey = 'geek_' + context + '_data_' + malId;
        const cachedData = localStorage.getItem(cacheKey);

        if (cachedData) {
            // Renderização instantânea (0ms) a partir da segunda visita/F5
            renderBadge(badge, JSON.parse(cachedData));
        } else {
            // Na primeira visita, busca respeitando o limite da API
            const endpoint = context === 'manga' ? `https://api.jikan.moe/v4/manga/${malId}` : `https://api.jikan.moe/v4/anime/${malId}`;
            setTimeout(() => {
                fetch(endpoint)
                    .then(response => response.json())
                    .then(data => {
                        const countField = context === 'manga' ? data?.data?.chapters : data?.data?.episodes;
                        const itemData = {
                            episodes: countField,
                            type: data?.data?.type
                        };
                        localStorage.setItem(cacheKey, JSON.stringify(itemData));
                        renderBadge(badge, itemData);
                    })
                    .catch(() => {
                        const countSpan = badge.querySelector('.js-ep-count');
                        if (countSpan) countSpan.textContent = '...';
                    });
            }, delayQueue * 1500);
            delayQueue++;
        }
    });
});
</script>
