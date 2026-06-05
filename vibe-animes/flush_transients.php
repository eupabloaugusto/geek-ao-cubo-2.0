<?php
require_once dirname(__FILE__) . '/../../../wp-load.php';

global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_jikan_anime_chars_%' OR option_name LIKE '_transient_timeout_jikan_anime_chars_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_jikan_%' OR option_name LIKE '_transient_timeout_jikan_%'");

echo "All Jikan Transients cleared!";
