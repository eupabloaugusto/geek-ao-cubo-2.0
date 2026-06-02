<?php
require_once( dirname( __FILE__ ) . '/wp-load.php' );

echo "Iniciando limpeza de banco de dados...\n";

// 1. Delete all 'episodio' posts
$episodes = get_posts([
    'post_type' => 'episodio',
    'numberposts' => -1,
    'post_status' => 'any'
]);
echo "Deletando " . count($episodes) . " episódios...\n";
foreach ($episodes as $ep) {
    wp_delete_post($ep->ID, true);
}
echo "Episódios deletados.\n";

// 2. Delete animes without 'anime_id_mal'
$animes = get_posts([
    'post_type' => 'anime',
    'numberposts' => -1,
    'post_status' => 'any'
]);
$deleted_animes = 0;
foreach ($animes as $anime) {
    $mal_id = get_post_meta($anime->ID, 'anime_id_mal', true);
    if (empty($mal_id)) {
        wp_delete_post($anime->ID, true);
        $deleted_animes++;
    }
}
echo "Deletados $deleted_animes animes sem MAL ID configurado.\n";

// 3. Clean up postmeta for remaining animes
global $wpdb;
$meta_keys_to_delete = [
    'anime_nota_mal', 'anime_studio', 'anime_tipo', 'anime_ano', 
    'anime_duracao', 'anime_classificacao', 'anime_status', 
    'anime_broadcast_day', 'anime_broadcast_time', 'anime_generos',
    'anime_relations', 'anime_streaming', 'anime_trailer_url', 'anime_rank'
];

foreach ($meta_keys_to_delete as $key) {
    $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", $key) );
    $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", '_' . $key) );
}
echo "Metadados obsoletos deletados.\n";

echo "Limpeza concluída com sucesso!\n";
