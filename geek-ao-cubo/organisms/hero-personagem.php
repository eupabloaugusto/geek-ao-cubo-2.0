<?php
/**
 * Organism: Hero Personagem (hero-personagem)
 *
 * Cabeçalho principal da página do personagem.
 * Estrutura herdada do hero-anime para manter consistência extrema.
 *
 * @package geek-ao-cubo
 *
 * @param array $char_data Dados retornados da API Jikan /characters/{id}/full
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$char_data = isset( $args['char_data'] ) ? $args['char_data'] : array();

if ( empty( $char_data ) ) {
	return;
}

$nome      = $char_data['name'] ?? get_the_title();
$kanji     = $char_data['name_kanji'] ?? '';
$imagem    = $char_data['images']['webp']['image_url'] ?? ( $char_data['images']['jpg']['image_url'] ?? '' );
$bio       = $char_data['about'] ?? '';
$favorites = $char_data['favorites'] ?? 0;
$nicknames = $char_data['nicknames'] ?? array();

// Meta itens para o <dl>
$meta_items = array();

// Transforma Nicknames em string
if ( ! empty( $nicknames ) ) {
	$meta_items[] = array( 'label' => __( 'Apelidos', 'geek-ao-cubo' ), 'value' => implode( ', ', $nicknames ) );
}

?>

<section class="hero-personagem" aria-label="<?php echo esc_attr( sprintf( __( 'Detalhes do personagem: %s', 'geek-ao-cubo' ), $nome ) ); ?>">

	<!-- BACKDROP (Mesmo do anime) -->
	<?php if ( ! empty( $imagem ) ) : ?>
		<div class="hero-personagem__backdrop"
			style="background-image: url('<?php echo esc_attr( $imagem ); ?>');"
			aria-hidden="true">
		</div>
		<div class="hero-personagem__overlay" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="hero-personagem__inner">

		<!-- A. Poster -->
		<div class="hero-personagem__poster">
			<!-- Usamos a mesma class da imagem capa para herdar sombras ou estilizamos direto -->
			<div class="hero-personagem__imagem-container">
				<img src="<?php echo esc_url( $imagem ); ?>" alt="<?php echo esc_attr( 'Foto de ' . $nome ); ?>" class="hero-personagem__imagem">
			</div>
		</div>

		<!-- B. Info -->
		<div class="hero-personagem__info">

			<!-- Título -->
			<h1 class="hero-personagem__title"><?php echo esc_html( $nome ); ?></h1>

			<?php if ( trim( $kanji ) !== '' ) : ?>
				<p class="hero-personagem__title-jp" lang="ja"><?php echo esc_html( $kanji ); ?></p>
			<?php endif; ?>

			<!-- Stats Container (ex: Favoritos) -->
			<?php if ( $favorites > 0 ) : ?>
				<div class="hero-personagem__stats-container">
					<?php
					mm_render_component( 'atoms', 'stat-numero', array(
						'number' => number_format_i18n( $favorites ),
						'label'  => __( 'Favoritos', 'geek-ao-cubo' ),
						'icon'   => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
						'class'  => 'hero-personagem__stat-item hero-personagem__stat-item--heart',
					) );
					?>
				</div>
			<?php endif; ?>

			<!-- Meta Info (DL grid) -->
			<?php if ( ! empty( $meta_items ) ) : ?>
				<dl class="hero-personagem__meta">
					<?php foreach ( $meta_items as $item ) : ?>
						<div class="hero-personagem__meta-item">
							<dt class="hero-personagem__meta-label"><?php echo $item['label']; ?></dt>
							<dd class="hero-personagem__meta-value">
								<?php if ( ! empty( $item['url'] ) ) : ?>
									<a href="<?php echo esc_url( $item['url'] ); ?>" class="hero-personagem__meta-link" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html( $item['value'] ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( $item['value'] ); ?>
								<?php endif; ?>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>

			<!-- Sinopse / Bio -->
			<?php if ( ! empty( $bio ) ) : ?>
				<?php mm_render_component( 'organisms', 'secao-titulo', array(
					'titulo' => __( 'Sobre o Personagem', 'geek-ao-cubo' ),
				) ); ?>
				
				<div class="hero-personagem__sinopse js-hero-sinopse">
					<div class="hero-personagem__sinopse-content js-hero-sinopse-content">
						<?php echo wpautop( esc_html( $bio ) ); ?>
						<div class="hero-personagem__sinopse-fade" aria-hidden="true"></div>
					</div>
					<button
						type="button"
						class="hero-personagem__sinopse-toggle js-hero-sinopse-toggle"
						aria-expanded="false"
						data-label-expand="<?php esc_attr_e( 'Ler tudo', 'geek-ao-cubo' ); ?>"
						data-label-collapse="<?php esc_attr_e( 'Ler menos', 'geek-ao-cubo' ); ?>"
					>
						<span class="hero-personagem__sinopse-toggle-text"><?php _e( 'Ler tudo', 'geek-ao-cubo' ); ?></span>
						<svg class="hero-personagem__sinopse-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<polyline points="6 9 12 15 18 9"></polyline>
						</svg>
					</button>
				</div>
			<?php endif; ?>

		</div>
		<!-- /hero-personagem__info -->

	</div>
	<!-- /hero-personagem__inner -->

</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const sinopse = document.querySelector('.js-hero-sinopse');
	if (!sinopse) return;
	
	const textEl = sinopse.querySelector('.js-hero-sinopse-content');
	const btn = sinopse.querySelector('.js-hero-sinopse-toggle');
	if (!textEl || !btn) return;
	
	function checkOverflow() {
		const wasExpanded = sinopse.classList.contains('hero-personagem__sinopse--expanded');
		textEl.style.transition = 'none';
		sinopse.classList.remove('hero-personagem__sinopse--expanded');
		textEl.offsetHeight; // reflow
		const isOverflowing = textEl.scrollHeight > textEl.offsetHeight;
		if (wasExpanded) sinopse.classList.add('hero-personagem__sinopse--expanded');
		textEl.offsetHeight; // reflow
		textEl.style.transition = '';
		btn.style.display = isOverflowing ? 'inline-flex' : 'none';
	}
	
	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(() => requestAnimationFrame(checkOverflow));
	} else {
		requestAnimationFrame(checkOverflow);
	}
	
	btn.addEventListener('click', function() {
		const expanded = sinopse.classList.toggle('hero-personagem__sinopse--expanded');
		this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		const toggleText = this.querySelector('.hero-personagem__sinopse-toggle-text');
		if (toggleText) toggleText.textContent = expanded ? this.dataset.labelCollapse : this.dataset.labelExpand;
	});
	
	let resizeTimeout;
	window.addEventListener('resize', () => {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(checkOverflow, 150);
	});
});
</script>
