<?php
/**
 * Includes: Módulo de Monetização (AdSense & Links de Afiliados)
 *
 * Gerencia a injeção dinâmica de blocos de anúncios com base no tamanho
 * do post e enforça tags de patrocinados em links externos de parceiros.
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Helper: Obtém a renderização de um componente atômico via buffer de saída (captura de HTML string).
 *
 * @param string $type Component type ('atoms', 'molecules', 'organisms', 'templates')
 * @param string $name Component file slug
 * @param array  $args Arguments passed to the component
 * @return string      HTML renderizado do componente
 */
function mm_get_rendered_component( $type, $name, $args = array() ) {
	ob_start();
	mm_render_component( $type, $name, $args );
	return ob_get_clean();
}

/**
 * Task 5.1: Injeção Dinâmica de Anúncios AdSense no corpo do post
 *
 * Regras:
 *   - Post curto (< 3 parágrafos): 1 banner no final.
 *   - Post médio (3 a 6 parágrafos): 1 banner após o parágrafo 3, e 1 no final.
 *   - Post longo (>= 7 parágrafos): 1 banner após o parágrafo 3, 1 após o 6, e 1 no final.
 *
 * @param string $content Conteúdo original do post.
 * @return string          Conteúdo processado com anúncios injetados.
 */
function mm_inject_adsense_in_content( $content ) {
	// Garante que a injeção ocorre apenas em views singulares do loop principal
	if ( ! is_singular( array( 'post', 'review' ) ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	// Divide o conteúdo pelas tags de parágrafo </p>
	$paragraphs = explode( '</p>', $content );
	$count      = count( $paragraphs ) - 1; // O último elemento após o último </p> é ignorado

	if ( $count <= 0 ) {
		return $content;
	}

	// Renderiza os contêineres atômicos do AdSense
	// 1. Post Curto (< 3 parágrafos)
	if ( $count < 3 ) {
		$ad_end = mm_get_rendered_component( 'atoms', 'anuncio-adsense', array(
			'slot'  => 'slot-fim-artigo',
			'class' => 'anuncio-adsense--end',
		) );
		$content .= $ad_end;
	}
	// 2. Post Médio (3 a 6 parágrafos)
	elseif ( $count >= 3 && $count <= 6 ) {
		$ad_middle = mm_get_rendered_component( 'atoms', 'anuncio-adsense', array(
			'slot'  => 'slot-meio-artigo',
			'class' => 'anuncio-adsense--middle',
		) );
		$ad_end    = mm_get_rendered_component( 'atoms', 'anuncio-adsense', array(
			'slot'  => 'slot-fim-artigo',
			'class' => 'anuncio-adsense--end',
		) );

		// Injeta após o 3º parágrafo (índice 2)
		$paragraphs[2] .= $ad_middle;
		$content = implode( '</p>', $paragraphs ) . $ad_end;
	}
	// 3. Post Longo (>= 7 parágrafos)
	else {
		$ad_middle1 = mm_get_rendered_component( 'atoms', 'anuncio-adsense', array(
			'slot'  => 'slot-meio-1-artigo',
			'class' => 'anuncio-adsense--middle-1',
		) );
		$ad_middle2 = mm_get_rendered_component( 'atoms', 'anuncio-adsense', array(
			'slot'  => 'slot-meio-2-artigo',
			'class' => 'anuncio-adsense--middle-2',
		) );
		$ad_end     = mm_get_rendered_component( 'atoms', 'anuncio-adsense', array(
			'slot'  => 'slot-fim-artigo',
			'class' => 'anuncio-adsense--end',
		) );

		// Injeta após o 3º (índice 2) e o 6º parágrafo (índice 5)
		$paragraphs[2] .= $ad_middle1;
		$paragraphs[5] .= $ad_middle2;
		$content = implode( '</p>', $paragraphs ) . $ad_end;
	}

	return $content;
}
add_filter( 'the_content', 'mm_inject_adsense_in_content', 10 );

/**
 * Task 5.4: Enforcer de links de afiliados
 *
 * Filtra o conteúdo do post buscando por links de afiliados parceiros
 * (Shopee, Amazon, Mercado Livre) e injetando target="_blank" rel="sponsored"
 * automaticamente, prevenindo penalizações de SEO por links patrocinados não declarados.
 *
 * @param string $content Conteúdo original do post.
 * @return string          Conteúdo processado.
 */
function mm_enforce_affiliate_links( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}

	// Padrão que casa tags de link completas e extrai o href
	$pattern = '/<a\s+[^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i';

	return preg_replace_callback( $pattern, function( $matches ) {
		$full_tag = $matches[0];
		$url      = $matches[1];

		// Lista de parceiros a enforçar
		$partners   = array( 'shopee.com', 'amazon.com', 'amzn.to', 'mercadolivre.com' );
		$is_partner = false;

		foreach ( $partners as $partner ) {
			if ( stripos( $url, $partner ) !== false ) {
				$is_partner = true;
				break;
			}
		}

		if ( $is_partner ) {
			// Remove atributos rel e target antigos caso existam para evitar duplicidades
			$cleaned_tag = preg_replace( '/\s+rel=["\'][^"\']*["\']/i', '', $full_tag );
			$cleaned_tag = preg_replace( '/\s+target=["\'][^"\']*["\']/i', '', $cleaned_tag );

			// Injeta rel="sponsored" target="_blank" no início da tag <a>
			$new_tag = preg_replace( '/^<a/i', '<a target="_blank" rel="sponsored"', $cleaned_tag );
			return $new_tag;
		}

		return $full_tag;
	}, $content );
}
add_filter( 'the_content', 'mm_enforce_affiliate_links', 20 );
