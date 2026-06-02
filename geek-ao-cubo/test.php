<?php
require_once('../../../wp-load.php');
global $wpdb;
$types = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'anime_tipo'");
print_r($types);
