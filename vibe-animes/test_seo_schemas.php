<?php
/**
 * Test script: Valida todos os schemas JSON-LD e BreadcrumbList estruturados.
 * Deve ser executado acessando http://geekaocubocom.local/wp-content/themes/vibe-animes/test_seo_schemas.php
 */

// Mock idioma antes de carregar o core do WP para contornar cache estático
$active_lang = 'pt-BR';
if ( isset( $_GET['test_lang'] ) ) {
    $clean_lang = preg_replace( '/[^a-z-]/i', '', $_GET['test_lang'] );
    if ( in_array( $clean_lang, array( 'en', 'es', 'fr', 'de' ) ) ) {
        $active_lang = $clean_lang;
        $_SERVER['REQUEST_URI'] = '/' . $clean_lang . '/test_seo_schemas.php';
    }
}

// Busca robusta pelo wp-load.php
$dirs = array(
    dirname(dirname(dirname(dirname(__FILE__)))),
    dirname(dirname(dirname(dirname(__FILE__)))) . '/public',
    dirname(dirname(dirname(__FILE__))),
    dirname(dirname(dirname(dirname(dirname(__FILE__))))),
);

$wp_load_path = '';
foreach ($dirs as $dir) {
    $path = $dir . '/wp-load.php';
    if (file_exists($path)) {
        $wp_load_path = $path;
        break;
    }
}

if (empty($wp_load_path)) {
    die("Erro: Não foi possível localizar o wp-load.php nos caminhos escaneados.");
}

require_once $wp_load_path;

header('Content-Type: text/plain; charset=utf-8');
$detected_lang = vibe_multilingual_get_current_language();

echo "=== VALIDAÇÃO DE SCHEMA MARKUP JSON-LD (Idioma: {$detected_lang}) ===\n\n";

// Garante que posts de teste para CPTs secundários existam no banco de dados
function ensure_test_posts_exist() {
    // 1. Check review
    $reviews = get_posts(array(
        'post_type'   => 'review',
        'post_status' => 'any',
        'numberposts' => 1
    ));
    if ( empty( $reviews ) ) {
        $animes = get_posts(array('post_type' => 'anime', 'numberposts' => 1));
        $anime_id = ! empty($animes) ? $animes[0]->ID : 0;
        
        $post_id = wp_insert_post(array(
            'post_title'  => 'Review de Teste: Solo Leveling',
            'post_type'   => 'review',
            'post_status' => 'publish',
            'post_content'=> 'Esta é uma análise detalhada sobre Solo Leveling...',
        ));
        if ( $post_id ) {
            update_field( 'review_nota', '9.0', $post_id );
            update_field( 'review_veredicto', 'Um dos melhores animes de ação de 2024.', $post_id );
            if ( $anime_id ) {
                update_field( 'review_anime_relacionado', array( $anime_id ), $post_id );
            }
            echo "[INFO] Criado post de review de teste ID: {$post_id}\n";
        }
    }
    
    // 2. Check temporada
    $temporadas = get_posts(array(
        'post_type'   => 'temporada',
        'post_status' => 'any',
        'numberposts' => 1
    ));
    if ( empty( $temporadas ) ) {
        $animes = get_posts(array('post_type' => 'anime', 'numberposts' => 5));
        $anime_ids = array_map(function($a) { return $a->ID; }, $animes);
        
        $post_id = wp_insert_post(array(
            'post_title'  => 'Temporada de Primavera 2026',
            'post_type'   => 'temporada',
            'post_status' => 'publish',
            'post_content'=> 'Esta temporada de Primavera de 2026 está recheada...',
        ));
        if ( $post_id ) {
            update_field( 'temp_periodo', 'primavera', $post_id );
            update_field( 'temp_ano', '2026', $post_id );
            update_field( 'temp_descricao', 'Guia da temporada de Primavera de 2026.', $post_id );
            if ( ! empty( $anime_ids ) ) {
                update_field( 'temp_animes', $anime_ids, $post_id );
            }
            echo "[INFO] Criado post de temporada de teste ID: {$post_id}\n";
        }
    }
}

ensure_test_posts_exist();

// Helper para capturar e decodificar JSON-LD das tags script
function capture_json_ld($post_id, $cpt = '') {
    global $post;
    
    // Salva estado original
    $orig_post = $post;
    
    // Seta post global para simular loop/singular
    if ( $cpt !== 'personagem' ) {
        $post = get_post($post_id);
        setup_postdata($post);
    }
    
    // Mock WP_Query singular state
    global $wp_query;
    $orig_is_singular = $wp_query->is_singular;
    $orig_is_single = $wp_query->is_single;
    $wp_query->is_singular = true;
    $wp_query->is_single = true;
    
    // Se for personagem virtual
    $orig_personagem_id = get_query_var( 'personagem_id' );
    if ( $cpt === 'personagem' ) {
        $wp_query->set( 'personagem_id', $post_id );
    }
    
    // Captura BreadcrumbList
    ob_start();
    mm_inject_breadcrumbs_json_ld();
    $bc_html = ob_get_clean();
    
    // Captura Entity Schema
    ob_start();
    mm_inject_seo_json_ld();
    $entity_html = ob_get_clean();
    
    // Restaura estado original
    $wp_query->is_singular = $orig_is_singular;
    $wp_query->is_single = $orig_is_single;
    if ( $cpt === 'personagem' ) {
        $wp_query->set( 'personagem_id', $orig_personagem_id );
    }
    $post = $orig_post;
    if ($post) {
        setup_postdata($post);
    } else {
        wp_reset_postdata();
    }
    
    // Extrai JSON de dentro dos scripts
    $bc_json = null;
    if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $bc_html, $matches)) {
        $bc_json = json_decode(trim($matches[1]), true);
    }
    
    $entity_json = null;
    if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $entity_html, $matches)) {
        $entity_json = json_decode(trim($matches[1]), true);
    }
    
    return array(
        'breadcrumb' => $bc_json,
        'entity'     => $entity_json
    );
}

// Lista de CPTs a testar e posts correspondentes
$cpts = array('anime', 'manga', 'episodio', 'dublador', 'personagem', 'review', 'post', 'temporada');

foreach ($cpts as $cpt) {
    if ( $cpt === 'personagem' ) {
        $post_id = 1; // Usamos o ID 1 (Spike Spiegel) que já está cacheado nos transients
        $title = 'Spike Spiegel';
        echo "Testando CPT virtual '{$cpt}' [ID: {$post_id} - \"{$title}\"]:\n";
    } else {
        $posts = get_posts(array(
            'post_type'      => $cpt,
            'posts_per_page' => 1,
            'post_status'    => 'publish'
        ));
        
        if (empty($posts)) {
            echo "[WARN] Nenhum post do tipo '{$cpt}' encontrado para testar.\n";
            continue;
        }
        
        $post_id = $posts[0]->ID;
        $title = $posts[0]->post_title;
        echo "Testando CPT '{$cpt}' [ID: {$post_id} - \"{$title}\"]:\n";
    }
    
    // Testa
    $data = capture_json_ld($post_id, $cpt);
    
    // Validações
    if ($data['breadcrumb']) {
        echo "  [OK] BreadcrumbList encontrado e sintaticamente válido.\n";
        echo "       Tipo: " . $data['breadcrumb']['@type'] . " | Itens: " . count($data['breadcrumb']['itemListElement']) . "\n";
    } else {
        echo "  [INFO] BreadcrumbList não gerado (pode ser esperado se não houver trilha).\n";
    }
    
    if ($data['entity']) {
        $entity = $data['entity'];
        echo "  [OK] Entity Schema encontrado e válido.\n";
        echo "       Tipo: " . $entity['@type'] . "\n";
        echo "       ID:   " . ($entity['@id'] ?? 'N/A') . "\n";
        echo "       URL:  " . ($entity['url'] ?? 'N/A') . "\n";
        echo "       Idioma (inLanguage): " . ($entity['inLanguage'] ?? 'N/A') . "\n";
        
        // Verifica localização da URL se idioma for diferente de pt-BR
        if ($detected_lang !== 'pt-BR') {
            $url = $entity['url'] ?? '';
            if (strpos($url, '/' . $detected_lang . '/') !== false) {
                echo "  [OK] URL corretamente localizada para '{$detected_lang}': {$url}\n";
            } else {
                echo "  [FAIL] URL NÃO localizada: {$url}\n";
            }
        }
        
        if (isset($entity['sameAs'])) {
            echo "       sameAs: " . implode(', ', (array)$entity['sameAs']) . "\n";
        }
        if ($cpt === 'manga' && isset($entity['numberOfPages'])) {
            echo "       numberOfPages (Capítulos MangaDex): " . $entity['numberOfPages'] . "\n";
        }
    } else {
        echo "  [FAIL] Entity Schema não foi gerado ou está com formato inválido.\n";
    }
    
    echo "\n";
}

echo "=== FIM DOS TESTES ===\n";
