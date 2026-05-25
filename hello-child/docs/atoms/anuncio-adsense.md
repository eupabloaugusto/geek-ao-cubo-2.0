# Container de Anúncio Adsense (anuncio-adsense)

**Tipo:** Átomo  
**Arquivo:** `atoms/anuncio-adsense.php`  
**CSS:** `atoms/anuncio-adsense.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
O Container de Anúncio Adsense (`anuncio-adsense`) é o átomo oficial responsável por abrigar blocos de anúncios publicitários do Google AdSense de forma responsiva e controlada, minimizando o impacto na experiência de leitura.

## Variáveis CSS utilizadas
- `--neutral-900` (fundo do bloco de publicidade)
- `--neutral-700` (borda dashed indicativa)
- `--neutral-500` (cor da tag "Publicidade")
- `--color-primary` (para infos de debug local)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['slot']` | string | `'default'` | O identificador do bloco do anúncio cadastrado no painel do AdSense |
| `$args['class']` | string | `''` | Classes CSS adicionais |

## SEO e Performance (Prevenção de CLS)
- **Prevenção contra Cumulative Layout Shift (CLS):** O container do anúncio possui um estilo de altura mínima reservada (`min-height: 250px`). Isso impede a quebra repentina de layout (empurrão de conteúdo) quando o anúncio do Google carrega tardiamente de forma assíncrona, assegurando pontuação máxima nos Core Web Vitals do Rank Math/WP Rocket.
- Em ambiente de desenvolvimento local, o componente renderiza de forma amigável um card ilustrativo detalhado, permitindo testes visuais sem disparar requisições externas desnecessárias.

## Responsividade
- Totalmente fluido. O script interno do AdSense detecta a largura disponível do container e preenche o espaço ideal automaticamente.

## Exemplo de uso
```php
<?php 
// Renderiza o anúncio da barra lateral
mm_render_component('atoms', 'anuncio-adsense', [
    'slot' => '1234567890'
]); 
?>
```
