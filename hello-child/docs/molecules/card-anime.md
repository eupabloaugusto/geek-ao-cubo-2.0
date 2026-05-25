# Card de Anime (card-anime)

**Tipo:** Molécula  
**Arquivo:** `molecules/card-anime.php`  
**CSS:** `molecules/card-anime.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
Card vertical premium e moderno para exibição de anime individual na biblioteca. Reúne a capa de imagem (com nota do MAL e horário de exibição embutidos) e as pílulas de gênero.

## Átomos utilizados
- `atoms/imagem-capa.php`
- `atoms/badge-genero.php`

## Variáveis CSS utilizadas
- `--neutral-100` (cor inicial do link do título)
- `--color-primary` (cor do título no hover e glows da capa)
- `--border-radius-300` (arredondamento do card)
- `--space-100`, `--space-200`, `--space-300` (alinhamento dos elementos internos)
- `--text-xs-size` (tamanho do título de 16px)

## Parâmetros PHP
| Parâmetro | Tipo | Descrição |
|---|---|---|
| `$titulo` | string | Nome do anime (Obrigatório) |
| `$url` | string | URL permanente do anime (Padrão: `#`) |
| `$imagem_url` | string | URL da capa vertical (proporção 2:3) |
| `$nota` | string / float | Nota atribuída ao anime no MyAnimeList |
| `$horario` | string | Informações de horário de transmissão (ex: "Sábados, 13h") |
| `$generos` | array | Lista de gêneros (pode ser array de strings ou de arrays com `name` e `url`) |
| `$class` | string | Classe CSS customizada adicional |

## SEO aplicado
- Título encapsulado em tag `<h3>` semântica para manter a hierarquia de cabeçalhos.
- Textos alternativos (`alt`) robustos nas imagens de capa incluindo o nome do anime.
- Links separados para o conteúdo principal e as pílulas de gêneros para evitar o erro de acessibilidade de links aninhados.

## Responsividade
- O card expande-se para ocupar 100% da largura do seu container pai. O tamanho ideal do card é controlado de forma responsiva pelo elemento pai (grid ou trilho horizontal), recomendando-se uma largura entre `145px` e `215px`.
- Título limitado a no máximo 2 linhas com elipse (`-webkit-line-clamp`) e área de tamanho mínimo para manter cartões perfeitamente alinhados verticalmente mesmo com tamanhos de títulos desiguais.

## Exemplo de uso
```php
<?php mm_render_component('molecules', 'card-anime', [
    'titulo'     => 'Chainsaw Man',
    'url'        => home_url('/anime/chainsaw-man/'),
    'imagem_url' => 'https://exemplo.com/capa.jpg',
    'nota'       => '8.6',
    'horario'    => 'Sábados, 12h',
    'generos'    => ['Ação', 'Gore', 'Sobrenatural']
]); ?>
```
