<?php
/**
 * Diagnóstico: Verifica estrutura real dos posts anime no banco
 * Verifica: post_status, anime_id_mal, anime_tipo, e transient de cache
 * 
 * REMOVER APÓS USO - arquivo temporário de diagnóstico
 */
require_once( dirname( __FILE__, 4 ) . '/wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

$animes = get_posts( array(
    'post_type'      => 'anime',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
    'no_found_rows'  => true,
) );

$total = count( $animes );

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnóstico anime</title>";
echo "<style>body{font-family:monospace;background:#111;color:#eee;padding:2rem;line-height:1.6}";
echo "table{border-collapse:collapse;width:100%}th,td{border:1px solid #333;padding:6px 10px;text-align:left}";
echo "th{background:#222}.ok{color:#4ade80}.warn{color:#facc15}.fail{color:#f87171}</style></head><body>";
echo "<h2>📋 Diagnóstico de Posts 'anime' — Total: {$total}</h2>";

$status_counts = array();
$sem_mal_id    = 0;
$sem_tipo      = 0;
$com_tipo      = 0;
$sem_cache     = 0;
$com_cache     = 0;

echo "<table><tr><th>#</th><th>ID WP</th><th>Título</th><th>Status</th><th>anime_id_mal</th><th>anime_tipo</th><th>Transient (cache)</th></tr>";

foreach ( $animes as $i => $post_id ) {
    $post       = get_post( $post_id );
    $status     = $post->post_status;
    $titulo     = esc_html( $post->post_title );
    $mal_id     = get_post_meta( $post_id, 'anime_id_mal', true );
    $tipo       = get_post_meta( $post_id, 'anime_tipo', true );
    $cache      = $mal_id ? get_transient( 'jikan_anime_full_' . $mal_id ) : false;
    $cache_type = $cache ? ( $cache['type'] ?? 'sem [type]' ) : '—';

    $status_counts[ $status ] = ( $status_counts[ $status ] ?? 0 ) + 1;

    if ( ! $mal_id ) { $sem_mal_id++; }

    if ( $tipo ) {
        $com_tipo++;
        $tipo_html = "<span class='ok'>{$tipo}</span>";
    } else {
        $sem_tipo++;
        $tipo_html = "<span class='fail'>ausente</span>";
    }

    if ( $cache ) {
        $com_cache++;
        $cache_html = "<span class='ok'>{$cache_type}</span>";
    } else {
        $sem_cache++;
        $cache_html = "<span class='warn'>sem cache</span>";
    }

    $mal_html = $mal_id
        ? "<span class='ok'>{$mal_id}</span>"
        : "<span class='fail'>ausente</span>";

    echo "<tr><td>" . ( $i + 1 ) . "</td><td>{$post_id}</td><td>{$titulo}</td><td>{$status}</td><td>{$mal_html}</td><td>{$tipo_html}</td><td>{$cache_html}</td></tr>";
}

echo "</table><hr>";
echo "<h3>Resumo</h3>";
echo "<p><strong>Post Status:</strong> ";
foreach ( $status_counts as $s => $c ) {
    echo "{$s}: <strong>{$c}</strong> | ";
}
echo "</p>";
echo "<p>Com <code>anime_id_mal</code>: <span class='ok'>" . ( $total - $sem_mal_id ) . "</span> | Sem: <span class='fail'>{$sem_mal_id}</span></p>";
echo "<p>Com <code>anime_tipo</code>: <span class='ok'>{$com_tipo}</span> | Sem: <span class='fail'>{$sem_tipo}</span></p>";
echo "<p>Com transient de cache: <span class='ok'>{$com_cache}</span> | Sem: <span class='warn'>{$sem_cache}</span></p>";

echo "<h3>Conclusão</h3>";
if ( $total === 0 ) {
    echo "<p class='fail'>⛔ Nenhum post do CPT 'anime' encontrado no banco (em qualquer status).</p>";
} elseif ( $sem_mal_id === $total ) {
    echo "<p class='fail'>⛔ Todos os posts existem mas sem anime_id_mal. O filtro não pode funcionar.</p>";
} elseif ( $com_tipo === 0 && $com_cache === 0 ) {
    echo "<p class='fail'>⛔ Nenhum anime tem anime_tipo no banco E nenhum tem transient de cache. Execute /?run_jikan_backfill=1 para populá-los.</p>";
} elseif ( $com_tipo === 0 && $com_cache > 0 ) {
    echo "<p class='warn'>⚠️ anime_tipo ausente mas o cache existe. O backfill automático via WP-Cron ainda não rodou. Acesse /?run_jikan_backfill=1 para forçar agora.</p>";
} elseif ( $sem_tipo > 0 ) {
    echo "<p class='warn'>⚠️ {$sem_tipo} animes ainda sem anime_tipo. Execute /?run_jikan_backfill=1 para corrigir.</p>";
} else {
    echo "<p class='ok'>✅ Estrutura correta. Todos os animes com anime_id_mal possuem anime_tipo.</p>";
}

echo "</body></html>";
