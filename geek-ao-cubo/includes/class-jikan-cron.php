<?php
/**
 * Jikan API Smart Cron
 *
 * Responsável pelo Pre-Warming do Cache.
 * Foca apenas nos animes que estão em lançamento (Currently Airing), 
 * garantindo velocidade sem gastar limite de requisições.
 */

class Jikan_Cron {
	
	public static function init() {
		add_action( 'jikan_smart_cache_update', [ __CLASS__, 'run_smart_update' ] );
		
		// Registra o cron para rodar a cada hora, se não estiver registrado
		if ( ! wp_next_scheduled( 'jikan_smart_cache_update' ) ) {
			wp_schedule_event( time(), 'hourly', 'jikan_smart_cache_update' );
		}
	}
	
	public static function run_smart_update() {
		// 1. Pega os animes da temporada atual na Jikan
		$response = wp_remote_get( 'https://api.jikan.moe/v4/seasons/now', [
			'timeout' => 15
		]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return; // Falhou, tenta de novo daqui a 1 hora
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		if ( empty( $data['data'] ) ) {
			return;
		}

		$active_mal_ids = [];
		foreach ( $data['data'] as $anime ) {
			$active_mal_ids[] = (int) $anime['mal_id'];
		}

		// 2. Busca no nosso banco quais animes NÓS TEMOS CADASTRADOS que estão nessa lista ativa
		$args = [
			'post_type'      => 'anime',
			'posts_per_page' => -1,
			'fields'         => 'ids', // Otimização: traz só os IDs do WordPress
			'meta_query'     => [
				[
					'key'     => 'anime_id_mal',
					'value'   => $active_mal_ids,
					'compare' => 'IN'
				]
			]
		];

		$query = new WP_Query( $args );
		
		if ( ! empty( $query->posts ) ) {
			foreach ( $query->posts as $post_id ) {
				$mal_id = get_post_meta( $post_id, 'anime_id_mal', true );
				if ( $mal_id ) {
					// Renova o cache com sleep(1) para não estourar rate limit da Jikan (3 req/seg)
					Jikan_API::fetch_and_cache_anime( $mal_id );
					sleep(1); 
					
					Jikan_API::fetch_and_cache_episodes( $mal_id );
					sleep(1);

					// Sincroniza anime_idioma a partir dos dubladores PT-BR.
					Jikan_API::fetch_and_cache_characters( $mal_id );
					sleep(1);
				}
			}
		}
	}
}

// Inicializa a rotina
Jikan_Cron::init();
