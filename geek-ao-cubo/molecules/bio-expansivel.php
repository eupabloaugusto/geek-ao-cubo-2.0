<?php
/**
 * Molecule: Bio Expansível (bio-expansivel)
 *
 * Bloco de texto que exibe algumas linhas e um botão "Ler Mais" usando JS ou apenas CSS.
 *
 * @package geek-ao-cubo
 *
 * @param string $texto Conteúdo de texto rico ou simples da biografia.
 * @param string $class Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$texto = isset( $args['texto'] ) ? wp_kses_post( $args['texto'] ) : '';
$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

if ( empty( $texto ) ) {
	return;
}
?>
<div class="bio-expansivel <?php echo $class; ?>">
	<div class="bio-expansivel__conteudo">
		<?php echo wpautop( $texto ); ?>
	</div>
	
	<button type="button" class="bio-expansivel__btn" aria-expanded="false">
		<span class="bio-expansivel__btn-text">Ler Biografia Completa</span>
		<span class="bio-expansivel__icone">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
		</span>
	</button>
</div>

<script>
(function() {
	// Apenas roda uma vez para cada nova renderização (idealmente deveria ser no footer, mas inline funciona bem para moléculas isoladas)
	document.addEventListener('DOMContentLoaded', function() {
		const btns = document.querySelectorAll('.bio-expansivel__btn');
		btns.forEach(btn => {
			// Evita bind duplo se houver mais de um load
			if (btn.dataset.bioBound) return;
			btn.dataset.bioBound = 'true';

			btn.addEventListener('click', function() {
				const container = this.closest('.bio-expansivel');
				const content = container.querySelector('.bio-expansivel__conteudo');
				const textSpan = this.querySelector('.bio-expansivel__btn-text');
				const icon = this.querySelector('.bio-expansivel__icone');
				
				const isOpen = content.classList.contains('is-open');
				
				if (isOpen) {
					content.classList.remove('is-open');
					icon.classList.remove('is-rotated');
					textSpan.textContent = 'Ler Biografia Completa';
					this.setAttribute('aria-expanded', 'false');
				} else {
					content.classList.add('is-open');
					icon.classList.add('is-rotated');
					textSpan.textContent = 'Recolher Biografia';
					this.setAttribute('aria-expanded', 'true');
				}
			});
		});
	});
})();
</script>
