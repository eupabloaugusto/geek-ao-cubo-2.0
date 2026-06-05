<?php
/**
 * Organism: Hero Anime (hero-anime)
 *
 * Hero principal da página de detalhe do anime.
 * Layout: backdrop desfocado + [ poster | info completa ]
 * Mobile: coluna (poster em cima); Desktop: linha (poster esquerda, info direita).
 *
 * @package geek-ao-cubo
 *
 * @param string       $titulo          Título do anime — obrigatório.
 * @param string       $titulo_japones  Título original em japonês (opcional).
 * @param string       $imagem_backdrop URL da imagem de fundo desfocada (opcional; fallback: imagem_poster).
 * @param string       $imagem_poster   URL da capa poster 2:3 (opcional).
 * @param string       $nota            Nota MAL, ex: "8.74" (opcional).
 * @param string       $status          'airing', 'completed' ou 'upcoming' (opcional).
 * @param string       $tipo            'TV', 'Movie', 'OVA', 'ONA', 'Special' (opcional).
 * @param int          $episodios       Número de episódios (opcional).
 * @param string       $duracao         Duração por episódio, ex: "24 min/ep" (opcional).
 * @param string|array $studio          Nome do estúdio ou ['name'=>'...','url'=>'...'] (opcional).
 * @param int          $ano             Ano de início (opcional).
 * @param string       $temporada       Ex: "Primavera 2003" (opcional).
 * @param string       $classificacao   Ex: "PG-13", "R+" (opcional).
 * @param array        $generos         Array de strings ou arrays ['name'=>'...','url'=>'...'] (opcional).
 * @param string       $sinopse         Sinopse do anime — aceita HTML seguro (opcional).
 * @param string       $url_assistir    URL para assistir (opcional).
 * @param string       $url_lista       URL para adicionar à lista (opcional).
 * @param int|string   $anime_id_mal    ID do anime no MyAnimeList (para atualização dinâmica via Jikan).
 * @param int          $membros         Número de membros/votos (opcional).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo          = isset( $args['titulo'] )          ? esc_html( $args['titulo'] )          : '';
$titulo_japones  = isset( $args['titulo_japones'] )  ? esc_html( $args['titulo_japones'] )  : '';
$imagem_backdrop = isset( $args['imagem_backdrop'] ) ? esc_url( $args['imagem_backdrop'] )  : '';
$imagem_poster   = isset( $args['imagem_poster'] )   ? esc_url( $args['imagem_poster'] )    : '';
$nota            = isset( $args['nota'] )            ? esc_html( $args['nota'] )            : '';
$status          = isset( $args['status'] )          ? sanitize_key( $args['status'] )      : '';
$tipo            = isset( $args['tipo'] )            ? esc_html( $args['tipo'] )            : '';
$episodios       = isset( $args['episodios'] )       ? absint( $args['episodios'] )          : 0;
$duracao         = isset( $args['duracao'] )         ? esc_html( $args['duracao'] )         : '';
$studio_raw      = isset( $args['studio'] )          ? $args['studio']                      : '';
$ano             = isset( $args['ano'] )             ? absint( $args['ano'] )               : 0;
$temporada       = isset( $args['temporada'] )       ? esc_html( $args['temporada'] )       : '';
$classificacao   = isset( $args['classificacao'] )   ? esc_html( $args['classificacao'] )   : '';
$generos         = isset( $args['generos'] )         ? (array) $args['generos']             : array();
$sinopse         = isset( $args['sinopse'] )         ? wp_kses_post( $args['sinopse'] )     : '';
$url_assistir    = isset( $args['url_assistir'] )    ? esc_url( $args['url_assistir'] )     : '';
$label_assistir  = isset( $args['label_assistir'] )  ? esc_html( $args['label_assistir'] )  : __( 'Assistir Agora', 'geek-ao-cubo' );
$url_lista       = isset( $args['url_lista'] )       ? esc_url( $args['url_lista'] )        : '';
$anime_id_mal    = isset( $args['anime_id_mal'] )    ? absint( $args['anime_id_mal'] )      : 0;
$membros         = isset( $args['membros'] )         ? absint( $args['membros'] )           : 0;
$ranking         = isset( $args['ranking'] )         ? esc_html( $args['ranking'] )         : '';
$popularidade    = isset( $args['popularidade'] )    ? esc_html( $args['popularidade'] )    : '';
$idioma          = isset( $args['idioma'] )          ? esc_html( $args['idioma'] )          : '';
$volumes         = isset( $args['volumes'] )         ? absint( $args['volumes'] )           : 0;
$capitulos       = isset( $args['capitulos'] )       ? absint( $args['capitulos'] )         : 0;

if ( empty( $titulo ) ) {
	return;
}

// Normaliza studio: string simples ou array com 'name' e 'url'
$studio_name = '';
$studio_url  = '';
if ( is_array( $studio_raw ) ) {
	$studio_name = isset( $studio_raw['name'] ) ? esc_html( $studio_raw['name'] ) : '';
	$studio_url  = isset( $studio_raw['url'] )  ? esc_url( $studio_raw['url'] )   : '';
} elseif ( is_string( $studio_raw ) && ! empty( $studio_raw ) ) {
	$studio_name = esc_html( $studio_raw );
}

// Backdrop: usa imagem_poster como fallback se imagem_backdrop ausente
$backdrop_url = ! empty( $imagem_backdrop ) ? $imagem_backdrop : $imagem_poster;
?>

<section
	class="hero-anime"
	aria-label="<?php echo esc_attr( sprintf( __( 'Detalhes do anime: %s', 'geek-ao-cubo' ), $titulo ) ); ?>"
	itemscope
	itemtype="https://schema.org/TVSeries"
	<?php if ( $anime_id_mal > 0 ) : ?>data-anime-id="<?php echo esc_attr( $anime_id_mal ); ?>"<?php endif; ?>
	id="hero-anime-<?php echo $anime_id_mal > 0 ? esc_attr( $anime_id_mal ) : 'main'; ?>"
>

	<?php if ( ! empty( $backdrop_url ) ) : ?>
		<div class="hero-anime__backdrop"
			style="background-image: url('<?php echo esc_attr( $backdrop_url ); ?>');"
			aria-hidden="true">
		</div>
		<div class="hero-anime__overlay" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="hero-anime__inner">

		<!-- A. Poster -->
		<div class="hero-anime__poster">
			<?php
			mm_render_component( 'atoms', 'imagem-capa', array(
				'src'          => $imagem_poster,
				'alt'          => sprintf( __( 'Capa oficial do anime %s', 'geek-ao-cubo' ), $titulo ),
				'mostrar_nota' => false,
			) );
			?>
		</div>

		<!-- B. Info -->
		<div class="hero-anime__info">

			<!-- B1. Badges: Status + Tipo -->
			<?php if ( ! empty( $status ) || ! empty( $tipo ) ) : ?>
				<div class="hero-anime__badges">
					<?php if ( ! empty( $status ) ) : ?>
				<span data-jikan-field="status">
				<?php
				mm_render_component( 'atoms', 'badge-status', array(
					'status' => $status,
				) );
				?>
				</span>
			<?php endif; ?>
					<?php if ( ! empty( $tipo ) ) : ?>
						<span class="hero-anime__tipo"><?php echo $tipo; ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<!-- B2. Título H1 (SEO: único na página) -->
			<h1 class="hero-anime__title" itemprop="name"><?php echo $titulo; ?></h1>

			<?php if ( ! empty( $titulo_japones ) ) : ?>
				<p class="hero-anime__title-jp" lang="ja" itemprop="alternateName"><?php echo $titulo_japones; ?></p>
			<?php endif; ?>

			<!-- B3. Score MAL -->
			<?php if ( ! empty( $nota ) ) : ?>
				<div class="hero-anime__score-row" data-jikan-field="score-row">
					<span data-jikan-field="score">
					<?php
					mm_render_component( 'atoms', 'nota-mal', array(
						'nota'  => $nota,
						'class' => 'hero-anime__nota',
					) );
					?>
					</span>
					<?php if ( ! empty( $membros ) ) : ?>
						<span class="hero-anime__score-label" data-jikan-field="membros">
							(<?php echo sprintf( _n( '%s voto', '%s votos', $membros, 'geek-ao-cubo' ), number_format_i18n( $membros ) ); ?>)
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<!-- B3.5. Estatísticas de Destaque (stat-numero) -->
			<?php if ( ! empty( $ranking ) || ! empty( $popularidade ) || ! empty( $membros ) ) : ?>
				<div class="hero-anime__stats-container">
					<?php if ( ! empty( $ranking ) ) : ?>
						<?php
						mm_render_component( 'atoms', 'stat-numero', array(
							'number' => '#' . ltrim( $ranking, '#' ),
							'label'  => __( 'Ranking', 'geek-ao-cubo' ),
							'icon'   => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M18 2H6v3c0 2.76 2.24 5 5 5.91V15H9c-1.1 0-2 .9-2 2v3h10v-3c0-1.1-.9-2-2-2h-2v-4.09c2.76-.91 5-3.15 5-5.91V2zm-12 5V4h2v3c0 .55-.45 1-1 1s-1-.45-1-1zm11 1c-.55 0-1-.45-1-1V4h2v3c0 .55-.45 1-1 1z"/></svg>',
							'class'  => 'hero-anime__stat-item',
						) );
						?>
					<?php endif; ?>

					<?php if ( ! empty( $popularidade ) ) : ?>
						<?php
						mm_render_component( 'atoms', 'stat-numero', array(
							'number' => '#' . ltrim( $popularidade, '#' ),
							'label'  => __( 'Popularidade', 'geek-ao-cubo' ),
							'icon'   => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>',
							'class'  => 'hero-anime__stat-item',
						) );
						?>
					<?php endif; ?>

					<?php if ( ! empty( $membros ) ) : ?>
						<?php
						// Formatação bonita compacta para membros (ex: 1.2M ou formatado com pontos no padrão local)
						$membros_label = number_format_i18n( $membros );
						mm_render_component( 'atoms', 'stat-numero', array(
							'number' => $membros_label,
							'label'  => __( 'Membros', 'geek-ao-cubo' ),
							'icon'   => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
							'class'  => 'hero-anime__stat-item',
						) );
						?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<!-- B4. Gêneros -->
			<?php if ( ! empty( $generos ) ) : ?>
				<div class="hero-anime__genres" role="list" aria-label="<?php esc_attr_e( 'Gêneros do anime', 'geek-ao-cubo' ); ?>">
					<?php
					foreach ( $generos as $gen ) {
						$gen_name = '';
						$gen_url  = '#';
						if ( is_array( $gen ) ) {
							$gen_name = isset( $gen['name'] ) ? $gen['name'] : '';
							$gen_url  = isset( $gen['url'] )  ? $gen['url']  : '#';
						} else {
							$gen_name = $gen;
							$gen_url  = home_url( '/genero/' . sanitize_title( $gen ) . '/' );
						}
						if ( ! empty( $gen_name ) ) {
							mm_render_component( 'atoms', 'badge-genero', array(
								'genero' => $gen_name,
								'url'    => $gen_url,
							) );
						}
					}
					?>
				</div>
			<?php endif; ?>

			<!-- B5. Meta Info -->
			<?php
			$meta_items = array();
			if ( ! empty( $tipo ) )          $meta_items[] = array( 'label' => __( 'Tipo',          'geek-ao-cubo' ), 'value' => $tipo,         'url' => '' );
			if ( $volumes > 0 )              $meta_items[] = array( 'label' => __( 'Volumes',       'geek-ao-cubo' ), 'value' => $volumes,      'url' => '' );
			if ( $capitulos > 0 )            $meta_items[] = array( 'label' => __( 'Capítulos',     'geek-ao-cubo' ), 'value' => $capitulos,    'url' => '' );
			if ( $episodios > 0 )            $meta_items[] = array( 'label' => __( 'Episódios',     'geek-ao-cubo' ), 'value' => $episodios,    'url' => '' );
			if ( ! empty( $idioma ) )        $meta_items[] = array( 'label' => __( 'Áudio',         'geek-ao-cubo' ), 'value' => $idioma,       'url' => '' );
			if ( ! empty( $studio_name ) ) {
				$label_studio = ( $idioma === 'Mangá' ) ? __( 'Autor(es)', 'geek-ao-cubo' ) : __( 'Estúdio', 'geek-ao-cubo' );
				$meta_items[] = array( 'label' => $label_studio, 'value' => $studio_name, 'url' => $studio_url );
			}
			if ( $ano > 0 )                  $meta_items[] = array( 'label' => __( 'Ano',           'geek-ao-cubo' ), 'value' => $ano,          'url' => '' );
			if ( ! empty( $temporada ) )     $meta_items[] = array( 'label' => __( 'Temporada',     'geek-ao-cubo' ), 'value' => $temporada,    'url' => '' );
			if ( ! empty( $duracao ) )       $meta_items[] = array( 'label' => __( 'Duração',       'geek-ao-cubo' ), 'value' => $duracao,      'url' => '' );
			if ( ! empty( $classificacao ) ) $meta_items[] = array( 'label' => __( 'Classificação', 'geek-ao-cubo' ), 'value' => $classificacao,'url' => '' );
			?>
			<?php if ( ! empty( $meta_items ) ) : ?>
				<dl class="hero-anime__meta">
					<?php foreach ( $meta_items as $item ) : ?>
						<div class="hero-anime__meta-item">
							<dt class="hero-anime__meta-label"><?php echo $item['label']; ?></dt>
							<dd class="hero-anime__meta-value">
								<?php if ( ! empty( $item['url'] ) ) : ?>
									<a href="<?php echo esc_url( $item['url'] ); ?>" class="hero-anime__meta-link">
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

			<!-- B6. Sinopse -->
			<?php if ( ! empty( $sinopse ) ) : ?>
				<?php mm_render_component( 'organisms', 'secao-titulo', array(
					'titulo' => __( 'Sinopse', 'geek-ao-cubo' ),
				) ); ?>
				<div class="hero-anime__sinopse js-hero-sinopse">
					<div class="hero-anime__sinopse-content js-hero-sinopse-content" itemprop="description">
						<?php echo $sinopse; ?>
						<div class="hero-anime__sinopse-fade" aria-hidden="true"></div>
					</div>
					<button
						type="button"
						class="hero-anime__sinopse-toggle js-hero-sinopse-toggle"
						aria-expanded="false"
						data-label-expand="<?php esc_attr_e( 'Ler tudo', 'geek-ao-cubo' ); ?>"
						data-label-collapse="<?php esc_attr_e( 'Ler menos', 'geek-ao-cubo' ); ?>"
					>
						<span class="hero-anime__sinopse-toggle-text"><?php _e( 'Ler tudo', 'geek-ao-cubo' ); ?></span>
						<svg class="hero-anime__sinopse-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<polyline points="6 9 12 15 18 9"></polyline>
						</svg>
					</button>
				</div>
			<?php endif; ?>

			<!-- B7. CTAs -->
			<?php if ( ! empty( $url_assistir ) || ! empty( $url_lista ) ) : ?>
				<div class="hero-anime__ctas">
					<?php if ( ! empty( $url_assistir ) ) : ?>
						<?php
						mm_render_component( 'atoms', 'btn-primary', array(
							'label' => $label_assistir,
							'url'   => $url_assistir,
							'class' => 'hero-anime__cta-assistir',
						) );
						?>
					<?php endif; ?>
					<?php if ( ! empty( $url_lista ) ) : ?>
						<?php
						mm_render_component( 'atoms', 'btn-secondary', array(
							'label' => __( 'Adicionar à Lista', 'geek-ao-cubo' ),
							'url'   => $url_lista,
							'class' => 'hero-anime__cta-lista',
						) );
						?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
		<!-- /hero-anime__info -->

	</div>
	<!-- /hero-anime__inner -->

</section>
