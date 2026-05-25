<?php
/**
 * Organism: Hero Anime (hero-anime)
 *
 * Hero principal da página de detalhe do anime.
 * Layout: backdrop desfocado + [ poster | info completa ]
 * Mobile: coluna (poster em cima); Desktop: linha (poster esquerda, info direita).
 *
 * @package hello-elementor-child
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
$url_lista       = isset( $args['url_lista'] )       ? esc_url( $args['url_lista'] )        : '';

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
	aria-label="<?php echo esc_attr( sprintf( __( 'Detalhes do anime: %s', 'hello-elementor-child' ), $titulo ) ); ?>"
	itemscope
	itemtype="https://schema.org/TVSeries"
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
				'alt'          => sprintf( __( 'Capa oficial do anime %s', 'hello-elementor-child' ), $titulo ),
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
						<?php
						mm_render_component( 'atoms', 'badge-status', array(
							'status' => $status,
						) );
						?>
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
				<div class="hero-anime__score-row">
					<?php
					mm_render_component( 'atoms', 'nota-mal', array(
						'nota'  => $nota,
						'class' => 'hero-anime__nota',
					) );
					?>
					<span class="hero-anime__score-label"><?php _e( 'no MyAnimeList', 'hello-elementor-child' ); ?></span>
				</div>
			<?php endif; ?>

			<!-- B4. Gêneros -->
			<?php if ( ! empty( $generos ) ) : ?>
				<div class="hero-anime__genres" role="list" aria-label="<?php esc_attr_e( 'Gêneros do anime', 'hello-elementor-child' ); ?>">
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
			if ( ! empty( $tipo ) )          $meta_items[] = array( 'label' => __( 'Tipo',          'hello-elementor-child' ), 'value' => $tipo,         'url' => '' );
			if ( $episodios > 0 )            $meta_items[] = array( 'label' => __( 'Episódios',     'hello-elementor-child' ), 'value' => $episodios,    'url' => '' );
			if ( ! empty( $studio_name ) )   $meta_items[] = array( 'label' => __( 'Estúdio',       'hello-elementor-child' ), 'value' => $studio_name,  'url' => $studio_url );
			if ( $ano > 0 )                  $meta_items[] = array( 'label' => __( 'Ano',           'hello-elementor-child' ), 'value' => $ano,          'url' => '' );
			if ( ! empty( $temporada ) )     $meta_items[] = array( 'label' => __( 'Temporada',     'hello-elementor-child' ), 'value' => $temporada,    'url' => '' );
			if ( ! empty( $duracao ) )       $meta_items[] = array( 'label' => __( 'Duração',       'hello-elementor-child' ), 'value' => $duracao,      'url' => '' );
			if ( ! empty( $classificacao ) ) $meta_items[] = array( 'label' => __( 'Classificação', 'hello-elementor-child' ), 'value' => $classificacao,'url' => '' );
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
				<div class="hero-anime__sinopse">
					<p class="hero-anime__sinopse-text" itemprop="description"><?php echo $sinopse; ?></p>
				</div>
			<?php endif; ?>

			<!-- B7. CTAs -->
			<?php if ( ! empty( $url_assistir ) || ! empty( $url_lista ) ) : ?>
				<div class="hero-anime__ctas">
					<?php if ( ! empty( $url_assistir ) ) : ?>
						<?php
						mm_render_component( 'atoms', 'btn-primary', array(
							'label' => __( 'Assistir Agora', 'hello-elementor-child' ),
							'url'   => $url_assistir,
							'class' => 'hero-anime__cta-assistir',
						) );
						?>
					<?php endif; ?>
					<?php if ( ! empty( $url_lista ) ) : ?>
						<?php
						mm_render_component( 'atoms', 'btn-secondary', array(
							'label' => __( 'Adicionar à Lista', 'hello-elementor-child' ),
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
