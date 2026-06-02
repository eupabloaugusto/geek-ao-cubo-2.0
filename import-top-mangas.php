<?php
/**
 * Script para importar os 10 Mangás mais populares via Jikan API.
 * Executar via CLI.
 */

// Bootstraps WordPress
require_once( dirname(__FILE__) . '/wp-load.php' );

echo "Iniciando importacao dos 10 mangas mais populares...\n";

// Endpoint para Top Mangas
$url = 'https://api.jikan.moe/v4/top/manga?limit=10';
$response = wp_remote_get( $url, ['timeout' => 30] );

if ( is_wp_error( $response ) ) {
    die( "Erro de conexao com a Jikan API: " . $response->get_error_message() . "\n" );
}

$body = wp_remote_retrieve_body( $response );
$data = json_decode( $body, true );

if ( empty( $data['data'] ) ) {
    die( "Nenhum dado retornado da API.\n" );
}

$mangas = $data['data'];
$count = 0;

foreach ( $mangas as $manga ) {
    $mal_id = $manga['mal_id'];
    $title = $manga['title'];

    // Verifica se ja existe
    $exists = get_posts( array(
        'post_type'  => 'manga',
        'meta_key'   => 'manga_id_mal',
        'meta_value' => $mal_id,
        'posts_per_page' => 1,
        'fields' => 'ids'
    ) );

    if ( ! empty( $exists ) ) {
        echo "- [Ignorado] Manga '{$title}' (ID: {$mal_id}) ja existe no banco.\n";
        continue;
    }

    // Cria o post
    $post_data = array(
        'post_title'    => sanitize_text_field( $title ),
        'post_status'   => 'publish',
        'post_type'     => 'manga',
    );

    $post_id = wp_insert_post( $post_data );

    if ( ! is_wp_error( $post_id ) ) {
        // Atualiza o ACF ou custom field
        update_post_meta( $post_id, 'manga_id_mal', $mal_id );
        echo "+ [Criado] Manga '{$title}' (Post ID: {$post_id}, MAL ID: {$mal_id}).\n";
        $count++;
    } else {
        echo "x [Erro] Falha ao criar manga '{$title}'.\n";
    }
}

echo "Finalizado! $count mangas importados.\n";
