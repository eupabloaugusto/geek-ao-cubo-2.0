<?php
/**
 * Organism: Hero Dublador (hero-dublador)
 *
 * Cabeçalho principal da página do dublador.
 * Estrutura herdada do hero-anime para manter consistência extrema.
 *
 * @package geek-ao-cubo
 *
 * @param array $jikan_data Dados retornados da API Jikan /people/{id}/full
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$person_data = isset( $args['jikan_data'] ) ? $args['jikan_data'] : array();

if ( empty( $person_data ) ) {
	return;
}

$nome      = $person_data['name'] ?? get_the_title();
$kanji     = $person_data['given_name'] . ' ' . $person_data['family_name'];
$imagem    = $person_data['images']['jpg']['image_url'] ?? '';
$bio       = $person_data['about'] ?? '';
$favorites = $person_data['favorites'] ?? 0;
$birthday  = $person_data['birthday'] ?? ''; // Vem em formato ISO-8601
$website   = $person_data['website_url'] ?? '';

// 1. EXTRAÇÃO DE DADOS ESCONDIDOS NA BIO
$cidade_natal = '';
$redes_sociais = array();

if ( ! empty( $bio ) ) {
	// Captura a cidade natal
	if ( preg_match( '/(?:Birth place|Hometown|Local de nascimento|Cidade natal):\s*(.+)$/mi', $bio, $matches ) ) {
		$cidade_natal = trim( $matches[1] );
	}
	
	// Captura Redes Sociais
	$patterns_sociais = array(
		'Twitter'   => '/(?:Twitter|X):\s*(.+)$/mi',
		'Facebook'  => '/(?:Facebook|FB):\s*(.+)$/mi',
		'Instagram' => '/(?:Instagram|IG):\s*(.+)$/mi',
		'YouTube'   => '/(?:YouTube|YT):\s*(.+)$/mi',
		'TikTok'    => '/(?:TikTok):\s*(.+)$/mi',
		'Blog'      => '/(?:Blog):\s*(.+)$/mi',
	);

	foreach ( $patterns_sociais as $rede => $pattern ) {
		if ( preg_match( $pattern, $bio, $match ) ) {
			$valor = trim( $match[1] );
			// Remove colchetes ou parênteses se houver (ex: [@user])
			$valor = preg_replace('/[\[\]\(\)]/', '', $valor);
			
			$url = $valor;
			// Se não começar com http, tenta montar a URL
			if ( strpos( $url, 'http' ) !== 0 ) {
				$username = ltrim( $valor, '@' );
				// Remove espaços para o username (algumas vezes vem como "nome do canal" no YT, o que complica, mas o MAL costuma por o @ ou link)
				if ( $rede === 'Twitter' ) $url = 'https://twitter.com/' . rawurlencode( explode(' ', $username)[0] );
				elseif ( $rede === 'Facebook' ) $url = 'https://facebook.com/' . rawurlencode( explode(' ', $username)[0] );
				elseif ( $rede === 'Instagram' ) $url = 'https://instagram.com/' . rawurlencode( explode(' ', $username)[0] );
				elseif ( $rede === 'TikTok' ) $url = 'https://tiktok.com/@' . rawurlencode( explode(' ', $username)[0] );
				elseif ( $rede === 'YouTube' ) {
					// O YouTube é complicado de prever se é /c/, /@/ ou apenas string de busca. Usa como busca ou assume @
					if ( strpos($username, '@') === 0 ) {
						$url = 'https://youtube.com/' . rawurlencode( explode(' ', $username)[0] );
					} else {
						$url = 'https://youtube.com/results?search_query=' . rawurlencode( $username );
					}
				}
			}
			
			$redes_sociais[ $rede ] = array(
				'valor' => $rede, // Label amigável pro botão
				'url'   => $url
			);
		}
	}

	$linhas_para_remover = array(
		'/(?:Blood type|Tipo sanguíneo|Height|Altura|Weight|Peso|Hobbies|Skills|Habilidades|Birth place|Hometown|Local de nascimento|Cidade natal|Twitter|X|Facebook|FB|Instagram|IG|YouTube|YT|TikTok|Blog|Profile|Website)\s*:.*$/mi',
		// Caça perfis/arrobas soltos no início de linhas (ex: "@miyanomamoru_PR")
		'/^@\w+.*$/m',
		// Caça links perdidos que as vezes aparecem na bio
		'/^https?:\/\/.*$/m',
	);
	$bio = preg_replace( $linhas_para_remover, '', $bio );
	$bio = preg_replace( "/\n{3,}/", "\n\n", trim($bio) );
}

// 2. METADADOS DE DATA E LINKS
$idade = '';
$aniversario_formatado = '';
if ( ! empty( $birthday ) ) {
	$data_nasc = new DateTime( $birthday );
	$hoje      = new DateTime();
	$idade     = $hoje->diff( $data_nasc )->y . ' anos';
	
	$meses = array(
		1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
		7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
	);
	$mes_num = (int) $data_nasc->format('n');
	$aniversario_formatado = $data_nasc->format('d') . ' de ' . $meses[$mes_num];
}

// Meta itens para o <dl>
$meta_items = array();
if ( ! empty( $aniversario_formatado ) ) $meta_items[] = array( 'label' => __( 'Aniversário', 'geek-ao-cubo' ), 'value' => $aniversario_formatado );
if ( ! empty( $idade ) )                 $meta_items[] = array( 'label' => __( 'Idade',       'geek-ao-cubo' ), 'value' => $idade );
if ( ! empty( $cidade_natal ) )          $meta_items[] = array( 'label' => __( 'Origem',      'geek-ao-cubo' ), 'value' => $cidade_natal );
if ( ! empty( $website ) )               $meta_items[] = array( 'label' => __( 'Website',     'geek-ao-cubo' ), 'value' => __( 'Acessar site', 'geek-ao-cubo' ), 'url' => $website );

// Adiciona as Redes Sociais no Meta
foreach ( $redes_sociais as $rede => $dados ) {
	$meta_items[] = array( 'label' => $rede, 'value' => __( 'Acessar perfil', 'geek-ao-cubo' ), 'url' => $dados['url'] );
}
?>

<section class="hero-dublador" aria-label="<?php echo esc_attr( sprintf( __( 'Detalhes do dublador: %s', 'geek-ao-cubo' ), $nome ) ); ?>">

	<!-- BACKDROP (Mesmo do anime) -->
	<?php if ( ! empty( $imagem ) ) : ?>
		<div class="hero-dublador__backdrop"
			style="background-image: url('<?php echo esc_attr( $imagem ); ?>');"
			aria-hidden="true">
		</div>
		<div class="hero-dublador__overlay" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="hero-dublador__inner">

		<!-- A. Poster -->
		<div class="hero-dublador__poster">
			<!-- Usamos a mesma class da imagem capa para herdar sombras ou estilizamos direto -->
			<div class="hero-dublador__imagem-container">
				<img src="<?php echo esc_url( $imagem ); ?>" alt="<?php echo esc_attr( 'Foto de ' . $nome ); ?>" class="hero-dublador__imagem">
			</div>
		</div>

		<!-- B. Info -->
		<div class="hero-dublador__info">

			<!-- Título -->
			<h1 class="hero-dublador__title"><?php echo esc_html( $nome ); ?></h1>

			<?php if ( trim( $kanji ) !== '' ) : ?>
				<p class="hero-dublador__title-jp" lang="ja"><?php echo esc_html( $kanji ); ?></p>
			<?php endif; ?>

			<!-- Stats Container (ex: Favoritos) -->
			<?php if ( $favorites > 0 ) : ?>
				<div class="hero-dublador__stats-container">
					<?php
					mm_render_component( 'atoms', 'stat-numero', array(
						'number' => number_format_i18n( $favorites ),
						'label'  => __( 'Favoritos', 'geek-ao-cubo' ),
						'icon'   => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
						'class'  => 'hero-dublador__stat-item hero-dublador__stat-item--heart',
					) );
					?>
				</div>
			<?php endif; ?>

			<!-- Meta Info (DL grid) -->
			<?php if ( ! empty( $meta_items ) ) : ?>
				<dl class="hero-dublador__meta">
					<?php foreach ( $meta_items as $item ) : ?>
						<div class="hero-dublador__meta-item">
							<dt class="hero-dublador__meta-label"><?php echo $item['label']; ?></dt>
							<dd class="hero-dublador__meta-value">
								<?php if ( ! empty( $item['url'] ) ) : ?>
									<a href="<?php echo esc_url( $item['url'] ); ?>" class="hero-dublador__meta-link" target="_blank" rel="noopener noreferrer">
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
					'titulo' => __( 'Biografia', 'geek-ao-cubo' ),
				) ); ?>
				
				<div class="hero-dublador__sinopse js-hero-sinopse">
					<div class="hero-dublador__sinopse-content js-hero-sinopse-content">
						<?php echo wpautop( $bio ); ?>
						<div class="hero-dublador__sinopse-fade" aria-hidden="true"></div>
					</div>
					<button
						type="button"
						class="hero-dublador__sinopse-toggle js-hero-sinopse-toggle"
						aria-expanded="false"
						data-label-expand="<?php esc_attr_e( 'Ler tudo', 'geek-ao-cubo' ); ?>"
						data-label-collapse="<?php esc_attr_e( 'Ler menos', 'geek-ao-cubo' ); ?>"
					>
						<span class="hero-dublador__sinopse-toggle-text"><?php _e( 'Ler tudo', 'geek-ao-cubo' ); ?></span>
						<svg class="hero-dublador__sinopse-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<polyline points="6 9 12 15 18 9"></polyline>
						</svg>
					</button>
				</div>
			<?php endif; ?>

		</div>
		<!-- /hero-dublador__info -->

	</div>
	<!-- /hero-dublador__inner -->

</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const sinopse = document.querySelector('.js-hero-sinopse');
	if (!sinopse) return;
	
	const textEl = sinopse.querySelector('.js-hero-sinopse-content');
	const btn = sinopse.querySelector('.js-hero-sinopse-toggle');
	if (!textEl || !btn) return;
	
	function checkOverflow() {
		const wasExpanded = sinopse.classList.contains('hero-dublador__sinopse--expanded');
		textEl.style.transition = 'none';
		sinopse.classList.remove('hero-dublador__sinopse--expanded');
		textEl.offsetHeight; // reflow
		const isOverflowing = textEl.scrollHeight > textEl.offsetHeight;
		if (wasExpanded) sinopse.classList.add('hero-dublador__sinopse--expanded');
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
		const expanded = sinopse.classList.toggle('hero-dublador__sinopse--expanded');
		this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		const toggleText = this.querySelector('.hero-dublador__sinopse-toggle-text');
		if (toggleText) toggleText.textContent = expanded ? this.dataset.labelCollapse : this.dataset.labelExpand;
	});
	
	let resizeTimeout;
	window.addEventListener('resize', () => {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(checkOverflow, 150);
	});
});
</script>
