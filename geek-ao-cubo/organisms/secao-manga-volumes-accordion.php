<?php
/**
 * Organism: Seção de Volumes em Acordeão (Proxy)
 *
 * Este arquivo é um proxy de compatibilidade.
 * O componente real é `secao-episodios-accordion`, que é reutilizado diretamente.
 * Mantido apenas para evitar erros caso haja chamadas legadas.
 *
 * @package geek-ao-cubo
 * @deprecated Chame diretamente `secao-episodios-accordion` com os mesmos args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Repassa todos os args recebidos para o componente original.
mm_render_component( 'organisms', 'secao-episodios-accordion', $args ?? array() );
