<?php
/**
 * Template de Página de Personagem (Serverless)
 *
 * Renderiza dinamicamente dados da API Jikan para um personagem específico.
 * Sem dependência de Custom Post Type no banco de dados.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Pega o ID da URL interceptada pela Rewrite Rule
$mal_id = get_query_var( 'personagem_id' );

if ( ! $mal_id ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	get_template_part( '404' );
	exit;
}

// 1. Busca os dados completos na Jikan API com Stale-While-Revalidate
$jikan_data = Jikan_API::get_character_full( $mal_id );

if ( empty( $jikan_data ) ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	get_template_part( '404' );
	exit;
}

// 1.5 Parse robusto da biografia para extrair metadados e limpar a bio
$about_raw = str_replace( array("\r\n", "\r"), "\n", $jikan_data['about'] ?? '' );
$lines = explode( "\n", $about_raw );

$bio_lines = array();
$secoes_extras = array();
$current_key = null;
$in_bio = false;

foreach ( $lines as $line ) {
	$trimmed = trim( $line );

	if ( $trimmed === '' ) {
		if ( $in_bio ) {
			$bio_lines[] = '';
		} else {
			// Uma linha vazia reseta a chave atual. O próximo texto solto será considerado bio.
			$current_key = null;
		}
		continue;
	}

	// Se não estamos na bio, e achamos um texto longo que termina em pontuação e NÃO tem dois-pontos
	// Consideramos que os metadados acabaram e a biografia principal começou.
	if ( ! $in_bio && strlen( $trimmed ) > 60 && preg_match( '/[\.!?]$/', $trimmed ) && ! preg_match( '/^([A-Z][a-zA-Z0-9\s]{2,25}):/', $trimmed ) ) {
		$in_bio = true;
	}

	if ( $in_bio ) {
		$bio_lines[] = $trimmed;
		continue;
	}

	// Extrai metadados no formato "Chave: Valor"
	if ( preg_match( '/^([A-Z][a-zA-Z0-9\s]{2,25}):\s*(.*)$/', $trimmed, $match ) ) {
		// Ignora se for uma URL falha (http:) ou notas
		if ( strtolower($match[1]) !== 'http' && strtolower($match[1]) !== 'https' && strtolower($match[1]) !== 'source' ) {
			$current_key = trim( $match[1] );
			$val = trim( $match[2] );
			if ( ! isset( $secoes_extras[ $current_key ] ) ) {
				$secoes_extras[ $current_key ] = array();
			}
			if ( $val !== '' ) {
				$secoes_extras[ $current_key ][] = $val;
			}
			continue;
		}
	}

	// Extrai metadados no formato "[Chave]" ou "**Chave**"
	if ( preg_match( '/^(?:\[|\*\*?)([A-Z][a-zA-Z0-9\s]{2,25})(?:\]|\*\*?)$/', $trimmed, $match ) ) {
		$current_key = trim( $match[1] );
		if ( ! isset( $secoes_extras[ $current_key ] ) ) {
			$secoes_extras[ $current_key ] = array();
		}
		continue;
	}

	// Se chegou aqui e tem um current_key, é continuação da lista (ex: lista de magias)
	if ( $current_key ) {
		$secoes_extras[ $current_key ][] = $trimmed;
	} else {
		// Se não tem chave e não é vazio, forçamos o início da bio
		$in_bio = true;
		$bio_lines[] = $trimmed;
	}
}

// Atualiza o array para que o Hero receba APENAS a biografia limpa
$bio_limpa = trim( implode( "\n", $bio_lines ) );
$jikan_data['about'] = preg_replace( "/\n{3,}/", "\n\n", $bio_limpa );

// Traduz e agrupa as chaves extraídas para o português
$dicionario = array(
	'Age' => 'Idade', 'Birthdate' => 'Aniversário', 'Birthday' => 'Aniversário', 'Sign' => 'Signo', 'Zodiac' => 'Signo', 
	'Height' => 'Altura', 'Weight' => 'Peso', 'Blood type' => 'Tipo Sanguíneo', 'Sex' => 'Sexo', 'Gender' => 'Gênero', 
	'Race' => 'Raça', 'Species' => 'Espécie', 'Title' => 'Título', 'Titles' => 'Títulos', 'Aliases' => 'Apelidos', 
	'Affiliation' => 'Afiliação', 'Occupation' => 'Ocupação', 'Status' => 'Status', 'Relatives' => 'Parentes', 
	'Family' => 'Família', 'Partner' => 'Parceiro', 'Familiar' => 'Familiar', 'Classification' => 'Classificação', 
	'Rank' => 'Rank', 'Class' => 'Classe', 'Threat Level' => 'Nível de Ameaça', 'Level' => 'Nível', 
	'Divine protection' => 'Proteção Divina', 'Magic' => 'Magia', 'Ability' => 'Habilidade', 'Abilities' => 'Habilidades', 
	'Skills' => 'Habilidades', 'Skill' => 'Habilidade', 'Power' => 'Poder', 'Powers' => 'Poderes', 'Weapon' => 'Arma', 
	'Weapons' => 'Armas', 'Equipment' => 'Equipamento', 'Relic' => 'Relíquia', 'Teigu' => 'Teigu', 'Zanpakuto' => 'Zanpakuto', 
	'Devil Fruit' => 'Akuma no Mi', 'Quirk' => 'Individualidade', 'Quirks' => 'Individualidades', 'Nen' => 'Nen', 
	'Stand' => 'Stand', 'Breathing Style' => 'Estilo de Respiração', 'Grimoire' => 'Grimório', 'Resistance' => 'Resistência', 
	'Resistances' => 'Resistências', 'Appearance' => 'Aparência', 'Personality' => 'Personalidade', 'Background' => 'História', 
	'History' => 'História', 'Trivia' => 'Curiosidades', 'Quotes' => 'Citações', 'Summary' => 'Resumo'
);

$secoes_traduzidas = array();
foreach ( $secoes_extras as $k => $linhas ) {
	$k_pt = isset($dicionario[$k]) ? $dicionario[$k] : ucfirst(strtolower($k));
	$secoes_traduzidas[$k_pt] = implode( "\n", $linhas );
}
$secoes_extras = $secoes_traduzidas;

// 2. Obras em que atua (Animes)
$animes_list = array();
if ( ! empty( $jikan_data['anime'] ) ) {
	foreach ( $jikan_data['anime'] as $anime_item ) {
		// Tentar buscar se o anime existe no nosso banco para passar Link Juice
		$anime_mal_id = $anime_item['anime']['mal_id'];
		$anime_url = $anime_item['anime']['url'];
		
		global $wpdb;
		$local_post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'anime_id_mal' AND meta_value = %d", $anime_mal_id) );
		
		if ( $local_post_id ) {
			$anime_url = get_permalink( $local_post_id );
		}

		$animes_list[] = array(
			'id'           => $anime_mal_id,
			'title'        => $anime_item['anime']['title'],
			'permalink'    => $anime_url,
			'anime_id_mal' => $anime_mal_id,
			'image_url'    => $anime_item['anime']['images']['webp']['large_image_url'] ?? ( $anime_item['anime']['images']['jpg']['image_url'] ?? '' ),
			'role'         => $anime_item['role'],
		);
	}
}

// 2.5 Obras em que atua (Mangás)
$mangas_list = array();
if ( ! empty( $jikan_data['manga'] ) ) {
	// Importa apenas os 10 mangás mais relevantes
	$mangas_slice = array_slice( $jikan_data['manga'], 0, 10 );
	foreach ( $mangas_slice as $manga_item ) {
		$manga_mal_id = $manga_item['manga']['mal_id'];
		$manga_url = $manga_item['manga']['url'];
		
		global $wpdb;
		$local_post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'manga_id_mal' AND meta_value = %d", $manga_mal_id) );
		
		if ( $local_post_id ) {
			$manga_url = get_permalink( $local_post_id );
		}

		$mangas_list[] = array(
			'id'           => $manga_mal_id,
			'title'        => $manga_item['manga']['title'],
			'permalink'    => $manga_url,
			'manga_id_mal' => $manga_mal_id,
			'image_url'    => $manga_item['manga']['images']['webp']['large_image_url'] ?? ( $manga_item['manga']['images']['jpg']['image_url'] ?? '' ),
			'role'         => $manga_item['role'],
		);
	}
}

// 3. Dubladores
$voices = array();
if ( ! empty( $jikan_data['voices'] ) ) {
	foreach ( $jikan_data['voices'] as $voice ) {
		$va_lang = str_replace( array('Portuguese (BR)', 'Japanese', 'English', 'Spanish', 'French', 'German', 'Italian'), array('Português (BR)', 'Japonês', 'Inglês', 'Espanhol', 'Francês', 'Alemão', 'Italiano'), $voice['language'] );
		$voices[] = array(
			'va_name'     => $voice['person']['name'],
			'va_image'    => $voice['person']['images']['jpg']['image_url'] ?? '',
			'va_url'      => $voice['person']['url'],
			'va_language' => $va_lang,
		);
	}
}

// 4. Personagens do Mesmo Universo
$universe_chars = array();
if ( ! empty( $animes_list ) ) {
	$universe_anime_id = $animes_list[0]['anime_id_mal'];
	$all_chars = Jikan_API::get_anime_characters( $universe_anime_id );
	if ( ! empty( $all_chars ) ) {
		foreach ( $all_chars as $char ) {
			// Ignora o próprio personagem que estamos vendo
			if ( isset( $char['character']['mal_id'] ) && $char['character']['mal_id'] == $mal_id ) {
				continue;
			}
			$universe_chars[] = $char;
		}
	}
}

// Enqueue CSS e JS locais
wp_enqueue_style( 'geek-ao-cubo-single-personagem', get_template_directory_uri() . '/single-personagem.css', array(), filemtime( get_template_directory() . '/single-personagem.css' ) );
wp_enqueue_script( 'geek-ao-cubo-secao-obras-personagem', get_template_directory_uri() . '/organisms/secao-obras-personagem.js', array(), filemtime( get_template_directory() . '/organisms/secao-obras-personagem.js' ), true );

// =========================================================================
// RENDERIZAÇÃO
// =========================================================================
mm_render_component( 'organisms', 'hero-personagem', array(
	'char_data' => $jikan_data,
) );
?>

<div class="personagem-layout">
	<main class="personagem-layout__main" id="main-content">

		<!-- A0. Anúncio AdSense (Banner Topo) -->
		<?php
		mm_render_component( 'atoms', 'anuncio-adsense', array(
			'variacao' => 'banner',
		) );
		?>

		<!-- A0.5. Informações Adicionais (Extraídas da Bio) -->
		<?php if ( ! empty( $secoes_extras ) ) : ?>
			<?php
			// Título dinâmico solicitado
			$titulo_accordion = sprintf( __( 'Especificações de %s', 'geek-ao-cubo' ), esc_html( $jikan_data['name'] ?? '' ) );
			
			mm_render_component( 'organisms', 'secao-info-extra-accordion', array(
				'titulo' => $titulo_accordion,
				'secoes' => $secoes_extras,
			) );
			?>
		<?php endif; ?>

		<!-- A2. Obras em que atua (Animes) -->
		<?php if ( ! empty( $animes_list ) ) : ?>
			<?php mm_render_component( 'organisms', 'secao-obras-personagem', array(
				'titulo' => __( 'Obras em que atua', 'geek-ao-cubo' ),
				'obras'  => $animes_list,
			) ); ?>
		<?php endif; ?>

		<!-- A2.5. Obras em que atua (Mangás) -->
		<?php if ( ! empty( $mangas_list ) ) : ?>
			<div class="secao-obras-personagem__spacer">
				<?php mm_render_component( 'organisms', 'secao-obras-personagem', array(
					'titulo' => __( 'Aparições em Mangás', 'geek-ao-cubo' ),
					'obras'  => $mangas_list,
				) ); ?>
			</div>
		<?php endif; ?>

		<!-- A2. Anúncio AdSense Leaderboard -->
		<?php
		mm_render_component( 'atoms', 'anuncio-adsense', array(
			'variacao'    => 'leaderboard',
			'visibilidade' => 'desktop',
		) );
		?>

		<!-- A3. Dubladores -->
		<?php if ( ! empty( $voices ) ) : ?>
			<section class="secao-vozes-personagem">
				<?php mm_render_component( 'organisms', 'secao-titulo', array(
					'titulo' => __( 'Dubladores (Voice Actors)', 'geek-ao-cubo' ),
				) ); ?>
				
				<div class="secao-vozes-personagem__grid">
					<?php foreach ( $voices as $voice ) : ?>
						<?php mm_render_component( 'molecules', 'card-personagem-dublador', $voice ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- A4. Personagens do Mesmo Universo -->
		<?php if ( ! empty( $universe_chars ) ) : ?>
			<?php mm_render_component( 'organisms', 'secao-esteira-personagens', array(
				'titulo_secao'  => __( 'Personagens do Mesmo Universo', 'geek-ao-cubo' ),
				'personagens'   => $universe_chars,
			) ); ?>
		<?php endif; ?>

		<!-- A5. Anúncio AdSense Banner -->
		<div class="ad-container">
			<?php
			mm_render_component( 'atoms', 'anuncio-adsense', array(
				'variacao' => 'banner',
			) );
			?>
		</div>

	</main>
</div>

<?php
get_footer();
