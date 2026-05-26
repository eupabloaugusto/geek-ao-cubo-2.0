<?php
/**
 * Script de Seed para o portal Geek ao Cubo
 * Popula o banco de dados local com categorias, artigos, animes e episódios fictícios.
 */

// Define que estamos em um contexto administrativo do WordPress
define( 'WP_ADMIN', true );

require_once( 'C:\\Users\\P. Augusto\\Local Sites\\geekaocubocom\\app\\public\\wp-load.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/taxonomy.php' );

echo "=== INICIANDO O SEED DE DADOS LOCAL DO GEEK AO CUBO ===\n";

// 1. UPLOAD DA IMAGEM DE MARCAÇÃO CINZA / PLACEHOLDER
$placeholder_path = 'c:\\Users\\P. Augusto\\Documents\\Geek ao Cubo\\gray_placeholder.png';
$attachment_id = 0;

if ( file_exists( $placeholder_path ) ) {
	// Verifica se a imagem já foi importada anteriormente para evitar duplicidade
	$existing_attachment = get_page_by_title( 'Geek ao Cubo Placeholder', OBJECT, 'attachment' );
	if ( $existing_attachment ) {
		$attachment_id = $existing_attachment->ID;
		echo "-> Imagem de marcação já existente na biblioteca. ID: {$attachment_id}\n";
	} else {
		$temp_file = wp_tempnam( 'gray_placeholder.png' );
		copy( $placeholder_path, $temp_file );

		$file_array = array(
			'name'     => 'gray_placeholder.png',
			'tmp_name' => $temp_file,
		);

		$attachment_id = media_handle_sideload( $file_array, 0, 'Geek ao Cubo Placeholder' );
		if ( is_wp_error( $attachment_id ) ) {
			echo "[AVISO] Falha ao enviar a imagem de marcação: " . $attachment_id->get_error_message() . "\n";
			$attachment_id = 0;
		} else {
			echo "-> Imagem de marcação enviada com sucesso! ID: {$attachment_id}\n";
		}
	}
} else {
	echo "[AVISO] Imagem 'gray_placeholder.png' não encontrada em {$placeholder_path}.\n";
}

$attachment_url = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';

// 2. CRIAÇÃO DE CATEGORIAS E TAGS
echo "\n-> Criando Categorias e Tags...\n";
$cat_destaque_id = wp_create_category( 'Destaque' );
$cat_novidades_id = wp_create_category( 'Novidades' );
$cat_guias_id = wp_create_category( 'Guias' );
$cat_analises_id = wp_create_category( 'Análises' );

// 3. CRIAÇÃO DE ARTIGOS/NOTÍCIAS DE DESTAQUE
$destaque_posts = array(
	array(
		'title'   => 'Solo Leveling Temporada 2: Teaser oficial revela data de estreia para 2026!',
		'content' => 'O fenômeno mundial Solo Leveling acaba de ganhar seu primeiro teaser oficial completo para a segunda temporada. O vídeo detalha o arco do Portão Vermelho e confirma o retorno da equipe de animação original na A-1 Pictures. Fique por dentro de todas as informações, novidades e expectativas para os novos episódios.',
		'excerpt' => 'A-1 Pictures confirma retorno do fenômeno mundial Solo Leveling para a temporada de Janeiro de 2026. Veja o teaser completo.',
		'cats'    => array( $cat_destaque_id, $cat_novidades_id ),
	),
	array(
		'title'   => 'Chainsaw Man: Filme do Arco de Reze tem trailer de tirar o fôlego divulgado',
		'content' => 'O estúdio MAPPA chocou a comunidade gamer e otaku ao revelar o trailer completo de Chainsaw Man – The Movie: Reze-hen. A produção cinematográfica adaptará um dos arcos mais aclamados e emocionais do mangá de Tatsuki Fujimoto. Confira a análise detalhada frame-a-frame do trailer oficial lançado hoje.',
		'excerpt' => 'Estúdio MAPPA revela trailer cinematográfico completo para a adaptação do aclamado arco de Reze. Confira!',
		'cats'    => array( $cat_destaque_id, $cat_novidades_id ),
	),
	array(
		'title'   => 'Demon Slayer: Filme Trilogia do Castelo Infinito ganha nova arte promocional',
		'content' => 'A ufotable revelou uma nova arte conceitual e visual de tirar o fôlego para o primeiro filme da trilogia cinematográfica de Demon Slayer: Kimetsu no Yaiba - Castelo Infinito. A arte destaca Tanjiro Kamado e o temível Muzan Kibutsuji frente a frente no labirinto mutável de Akaza e Nakime.',
		'excerpt' => 'Ufotable impressiona fãs com novo pôster visual focado no confronto final no Castelo Infinito. Veja detalhes.',
		'cats'    => array( $cat_destaque_id, $cat_novidades_id ),
	),
	array(
		'title'   => 'Frieren e a Jornada do Além: Anuncio da 2ª Temporada está mais próximo do que nunca',
		'content' => 'Diversos insiders renomados da indústria japonesa de animes começaram a apontar que a segunda temporada do aclamadíssimo Frieren: Beyond Journey\'s End (Sousou no Frieren) está em produção ativa pela Madhouse e o anúncio oficial deve ocorrer no evento especial de aniversário em Tóquio.',
		'excerpt' => 'Boatos quentes da indústria japonesa confirmam que a sequência da jornada de Frieren está em produção ativa. Entenda.',
		'cats'    => array( $cat_destaque_id, $cat_novidades_id ),
	),
);

echo "\n-> Criando Artigos em Destaque...\n";
foreach ( $destaque_posts as $p ) {
	$existing_post = get_page_by_title( $p['title'], OBJECT, 'post' );
	if ( $existing_post ) {
		echo "   - Post já existe: '{$p['title']}'\n";
	} else {
		$post_id = wp_insert_post( array(
			'post_title'   => $p['title'],
			'post_content' => $p['content'],
			'post_excerpt' => $p['excerpt'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_category'=> $p['cats'],
		) );
		if ( $post_id && $attachment_id ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
		echo "   [OK] Post em Destaque criado: '{$p['title']}' (ID: {$post_id})\n";
	}
}

// 4. CRIAÇÃO DE ARTIGOS/NOTÍCIAS RECENTES COMUNS
$recent_posts = array(
	array(
		'title'   => 'Guia Completo: Em qual ordem assistir a franquia Fate Series em 2026?',
		'content' => 'A franquia Fate da Type-Moon é amplamente conhecida tanto por sua qualidade visual incomparável produzida pela ufotable quanto por sua cronologia confusa e ramificada. Neste guia definitivo, explicamos a ordem cronológica, a ordem de lançamento e a melhor rota recomendada para iniciantes mergulharem no universo dos Servos e Mestres.',
		'excerpt' => 'Explicamos a ordem ideal de visualização, rotas recomendadas e ramificações da lendária franquia da Type-Moon.',
		'cats'    => array( $cat_guias_id ),
	),
	array(
		'title'   => 'Os 10 animes mais aguardados da Temporada de Verão no Japão',
		'content' => 'A Temporada de Julho (Verão japonês) está chegando recheada de retornos triunfais e novas promessas que pretendem dominar as redes sociais. De sequências de comédias românticas populares a novas produções isekai de tirar o fôlego, preparamos nossa lista com os 10 títulos que você absolutamente não pode perder.',
		'excerpt' => 'Prepare sua lista! Catalogamos os animes mais promissores e sequências aguardadas da próxima temporada.',
		'cats'    => array( $cat_novidades_id ),
	),
	array(
		'title'   => 'Crunchyroll anuncia novos Simulcasts dublados em Português para esta semana',
		'content' => 'Os fãs brasileiros de anime têm motivos de sobra para comemorar. A plataforma de streaming Crunchyroll revelou uma expansão massiva na sua grade de quinta-feira, incluindo a estreia de dublagens simultâneas em português brasileiro para três títulos aclamados desta temporada. Veja a lista dos dubladores escalados.',
		'excerpt' => 'Plataforma expande catálogo com dublagens simultâneas em português para grandes sucessos semanais.',
		'cats'    => array( $cat_novidades_id ),
	),
	array(
		'title'   => 'Análise: Por que Kaiju No. 8 redefiniu as expectativas de animação de Shonen?',
		'content' => 'Kaiju No. 8 estreou com uma proposta clássica, mas foi a qualidade de produção e as escolhas audaciosas da Production I.G e do Studio Khara que elevaram a obra a um patamar técnico pouquíssimas vezes visto na história recente. Nesta análise detalhada, discutimos a direção de arte e a trilha sonora de arrepiar.',
		'excerpt' => 'Dissecamos os fatores de produção e escolhas artísticas que transformaram a estreia de Kafka Hibino em um marco visual.',
		'cats'    => array( $cat_analises_id ),
	),
);

echo "\n-> Criando Notícias Recentes...\n";
foreach ( $recent_posts as $p ) {
	$existing_post = get_page_by_title( $p['title'], OBJECT, 'post' );
	if ( $existing_post ) {
		echo "   - Post já existe: '{$p['title']}'\n";
	} else {
		$post_id = wp_insert_post( array(
			'post_title'   => $p['title'],
			'post_content' => $p['content'],
			'post_excerpt' => $p['excerpt'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_category'=> $p['cats'],
		) );
		if ( $post_id && $attachment_id ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
		echo "   [OK] Post de Notícia criado: '{$p['title']}' (ID: {$post_id})\n";
	}
}

// 5. CRIAÇÃO DOS ANIMES (CPT anime)
$animes_data = array(
	'frieren' => array(
		'title' => 'Sousou no Frieren',
		'nota'  => '9.39',
		'hora'  => '14:00',
		'gens'  => array( 'Aventura', 'Fantasia', 'Drama' ),
	),
	'jujutsu' => array(
		'title' => 'Jujutsu Kaisen Season 2',
		'nota'  => '8.82',
		'hora'  => '15:30',
		'gens'  => array( 'Ação', 'Sobrenatural', 'Fantasia' ),
	),
	'chainsaw' => array(
		'title' => 'Chainsaw Man',
		'nota'  => '8.54',
		'hora'  => '13:00',
		'gens'  => array( 'Ação', 'Sobrenatural', 'Gore' ),
	),
	'demonslayer' => array(
		'title' => 'Demon Slayer: Hashira Training Arc',
		'nota'  => '8.65',
		'hora'  => '16:00',
		'gens'  => array( 'Ação', 'Fantasia', 'Histórico' ),
	),
	'solo' => array(
		'title' => 'Solo Leveling',
		'nota'  => '8.45',
		'hora'  => '12:30',
		'gens'  => array( 'Ação', 'Aventura', 'Fantasia' ),
	),
	'vinland' => array(
		'title' => 'Vinland Saga Season 2',
		'nota'  => '8.90',
		'hora'  => '11:00',
		'gens'  => array( 'Ação', 'Aventura', 'Drama', 'Histórico' ),
	),
);

$anime_ids = array();
echo "\n-> Criando CPT Animes...\n";
foreach ( $animes_data as $key => $data ) {
	$existing_anime = get_page_by_title( $data['title'], OBJECT, 'anime' );
	if ( $existing_anime ) {
		$anime_ids[ $key ] = $existing_anime->ID;
		echo "   - Anime já existe: '{$data['title']}' (ID: {$existing_anime->ID})\n";
	} else {
		$post_id = wp_insert_post( array(
			'post_title'  => $data['title'],
			'post_status' => 'publish',
			'post_type'   => 'anime',
		) );
		if ( $post_id ) {
			$anime_ids[ $key ] = $post_id;
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}

			// Configura ACF fields
			update_field( 'anime_nota_mal', $data['nota'], $post_id );
			update_field( 'anime_horario_exibicao', $data['hora'], $post_id );
			if ( $attachment_url ) {
				update_field( 'anime_imagem_capa_url', $attachment_url, $post_id );
			}

			// Associa as taxonomias 'genero'
			$genero_terms = array();
			foreach ( $data['gens'] as $g ) {
				$term = term_exists( $g, 'genero' );
				if ( ! $term ) {
					$term = wp_insert_term( $g, 'genero' );
				}
				if ( ! is_wp_error( $term ) ) {
					$genero_terms[] = (int) $term['term_id'];
				}
			}
			wp_set_post_terms( $post_id, $genero_terms, 'genero' );

			// Adiciona o status padrão
			$status_term = term_exists( 'Lançamento', 'status_exibicao' );
			if ( ! $status_term ) {
				$status_term = wp_insert_term( 'Lançamento', 'status_exibicao' );
			}
			if ( ! is_wp_error( $status_term ) ) {
				wp_set_post_terms( $post_id, array( (int) $status_term['term_id'] ), 'status_exibicao' );
			}

			echo "   [OK] Anime criado: '{$data['title']}' (ID: {$post_id}, Nota: {$data['nota']})\n";
		}
	}
}

// 6. CRIAÇÃO DE EPISÓDIOS (CPT episodio)
$episodes_data = array(
	array(
		'title' => 'Sousou no Frieren — Episódio 28',
		'anime' => 'frieren',
		'num'   => '28',
	),
	array(
		'title' => 'Jujutsu Kaisen Season 2 — Episódio 23',
		'anime' => 'jujutsu',
		'num'   => '23',
	),
	array(
		'title' => 'Chainsaw Man — Episódio 12',
		'anime' => 'chainsaw',
		'num'   => '12',
	),
	array(
		'title' => 'Demon Slayer: Hashira Training Arc — Episódio 8',
		'anime' => 'demonslayer',
		'num'   => '08',
	),
	array(
		'title' => 'Solo Leveling — Episódio 12',
		'anime' => 'solo',
		'num'   => '12',
	),
	array(
		'title' => 'Vinland Saga Season 2 — Episódio 24',
		'anime' => 'vinland',
		'num'   => '24',
	),
);

echo "\n-> Criando CPT Episódios com relacionamentos...\n";
foreach ( $episodes_data as $ep ) {
	$existing_ep = get_page_by_title( $ep['title'], OBJECT, 'episodio' );
	if ( $existing_ep ) {
		echo "   - Episódio já existe: '{$ep['title']}'\n";
	} else {
		$post_id = wp_insert_post( array(
			'post_title'  => $ep['title'],
			'post_status' => 'publish',
			'post_type'   => 'episodio',
		) );
		if ( $post_id ) {
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}

			$related_id = isset( $anime_ids[ $ep['anime'] ] ) ? $anime_ids[ $ep['anime'] ] : 0;
			
			// Atualiza campos ACF do Episódio
			update_field( 'ep_anime_relacionado', array( $related_id ), $post_id );
			update_field( 'ep_numero', $ep['num'], $post_id );
			update_field( 'ep_data_lancamento', date( 'Y-m-d H:i:s' ), $post_id );

			echo "   [OK] Episódio criado: '{$ep['title']}' (ID: {$post_id}, Relacionado ao Anime ID: {$related_id})\n";
		}
	}
}

echo "\n=== SEED DE DADOS CONCLUÍDO COM SUCESSO! ===\n";
