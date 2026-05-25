# Imagem de Capa

**Tipo:** Átomo  
**Arquivo:** `atoms/imagem-capa.php`  
**CSS:** `atoms/imagem-capa.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
A imagem de capa é o componente padrão utilizado para renderizar posters de anime de forma responsiva, com a proporção áurea vertical de cartaz (`aspect-ratio: 2/3`). Ela incorpora o lazy loading nativo do navegador para maximizar os Core Web Vitals e suporta a inclusão da Nota MAL como um badge flutuante inferior.

## Variáveis CSS utilizadas
- `--border-radius-300` (arredondamento das bordas)
- `--color-border` (borda padrão neutra)
- `--color-primary` (borda ativa no hover)
- `--neutral-800` / `--neutral-900` (cores do placeholder)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['src']` | string | `''` | URL da imagem. Se vazio, exibe o placeholder dashed |
| `$args['alt']` | string | *Dinâmico* | Texto alternativo de descrição SEO. Requerido para imagens reais |
| `$args['class']` | string | `''` | Classes CSS customizadas |
| `$args['mostrar_nota']` | boolean | `false` | Se verdadeiro, exibe a nota MAL flutuante na parte inferior |
| `$args['nota']` | string | `''` | A nota a ser renderizada no badge |

## SEO aplicado
- Atributo `alt` obrigatório para imagens reais, fornecendo indexação perfeita no Google Imagens.
- Tags `loading="lazy"` e `decoding="async"` para evitar o bloqueio de renderização do navegador.
- Proporção estática de container (`aspect-ratio`) para prevenir o CLS (Cumulative Layout Shift).

## Responsividade
- O container utiliza `width: 100%` e `aspect-ratio: 2/3`, permitindo que ele se adeque fluidamente ao grid de animes.
- Efeito de paralaxe em miniatura: a imagem sofre uma escala suave (`transform: scale(1.06)`) no hover de forma amortecida, enquanto a borda ativa se ilumina.

## Exemplo de uso
```php
<?php 
// Renderizando capa com nota de Solo Leveling
mm_render_component('atoms', 'imagem-capa', [
    'src'          => 'https://modomaratona.com/wp-content/uploads/solo-leveling.webp',
    'alt'          => 'Poster oficial de Solo Leveling na primeira temporada',
    'mostrar_nota' => true,
    'nota'         => '8.72'
]); 

// Renderizando placeholder se a imagem ainda não existir
mm_render_component('atoms', 'imagem-capa', [
    'alt'          => 'Anime ainda sem poster cadastrado'
]);
?>
```
