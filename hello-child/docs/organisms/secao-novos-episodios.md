# Seção de Novos Episódios (secao-novos-episodios)

**Tipo:** Organismo  
**Arquivo:** `organisms/secao-novos-episodios.php`  
**CSS:** `organisms/secao-novos-episodios.css`  
**Criado em:** 2026-05-25  

## Descrição

Carrossel horizontal de `card-anime` exibindo os animes com novos episódios do dia. Usa a molécula `trilho-infinito` para scroll infinito com setas de navegação. O cabeçalho exibe o título **"Novos Episódios — {Dia da Semana}"** gerado automaticamente pelo PHP com `date('w')`, sem necessidade de parâmetro externo.

Diferença principal em relação ao `secao-esteira-animes`: o campo `horario` dos cards **não é zerado** — o `badge-horario` aparece visível em cima de cada capa, indicando o horário de transmissão do episódio.

## Moléculas utilizadas

- `molecules/trilho-infinito` — wrapper de scroll infinito com setas
- `molecules/card-anime` — card vertical com `horario` (badge de relógio) ativo

## Parâmetros PHP

| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `animes` | array | `[]` | Lista de animes. Cada item deve ter: `titulo`, `url`, `imagem_url`, `nota`, `horario`, `generos`. O campo `horario` deve ser fornecido em **UTC** (ex: "21:00"). O componente converte automaticamente para BRT (America/Sao_Paulo) e ordena os cards de forma crescente pelo horário convertido. |

> O parâmetro `titulo_secao` **não existe** — o título é gerado internamente pelo PHP com o dia da semana em BRT. O carrossel não tem link "Ver Todos" pois já exibe todos os episódios do dia.

## Lógica de conversão UTC→BRT e ordenação

```php
// Conversão de cada horário UTC para BRT
$tz_brt = new DateTimeZone( 'America/Sao_Paulo' );
$tz_utc = new DateTimeZone( 'UTC' );

foreach ( $animes as &$anime ) {
	if ( ! empty( $anime['horario'] ) ) {
		$dt = new DateTime( '2000-01-01 ' . $anime['horario'], $tz_utc );
		$dt->setTimezone( $tz_brt );
		$anime['horario'] = $dt->format( 'H:i' );
	}
}

// Ordenação crescente por horário BRT
usort( $animes, fn( $a, $b ) => strcmp(
	$a['horario'] ?? '99:99',
	$b['horario'] ?? '99:99'
) );
```

## Lógica do título dinâmico (BRT)

```php
$agora_brt   = new DateTime( 'now', new DateTimeZone( 'America/Sao_Paulo' ) );
$dias_semana = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
$titulo_secao = 'Novos Episódios — ' . $dias_semana[ (int) $agora_brt->format( 'w' ) ];
```

## Exemplo de uso

```php
<?php
mm_render_component( 'organisms', 'secao-novos-episodios', array(
    'animes' => array(
        array(
            'titulo'     => 'Frieren: Beyond Journey\'s End',
            'url'        => '/anime/frieren/',
            'imagem_url' => 'https://exemplo.com/frieren.jpg',
            'nota'       => '9.38',
            'horario'    => '21:00',
            'generos'    => ['Fantasia', 'Drama'],
        ),
        array(
            'titulo'     => 'Chainsaw Man',
            'url'        => '/anime/chainsaw-man/',
            'imagem_url' => 'https://exemplo.com/csm.jpg',
            'nota'       => '8.60',
            'horario'    => '18:00',
            'generos'    => ['Ação', 'Gore'],
        ),
    ),
) );
?>
```

## Responsividade

- Mobile: scroll horizontal com snap, setas visíveis em todos os breakpoints
- Card: `12.5rem` de largura (sincronizado com `--width-card-anime`)
- Setas posicionadas ao centro vertical da imagem de capa (proporção 2:3)
