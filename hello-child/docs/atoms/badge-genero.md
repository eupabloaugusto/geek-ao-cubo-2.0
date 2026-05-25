# Badge de Gênero (badge-genero)

**Tipo:** Átomo  
**Arquivo:** `atoms/badge-genero.php`  
**CSS:** `atoms/badge-genero.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
Exibe uma tag ou pílula representativa do gênero de um anime. É um elemento interativo e clicável, direcionando o usuário para a página de listagem/arquivo daquele gênero, ideal para navegação exploratória interna.

## Variáveis CSS utilizadas
- `--neutral-800` (cor de fundo inicial)
- `--neutral-700` (cor de fundo no hover)
- `--neutral-300` (cor do texto inicial)
- `--neutral-100` (cor do texto no hover)
- `--color-primary` (cor da borda no hover)
- `--border-radius-500` (arredondamento de 32px para o formato pílula)
- `--text-xxs-size` (tamanho do texto de 12px)

## Parâmetros PHP
| Parâmetro | Tipo | Descrição |
|---|---|---|
| `$genero` | string | Nome do gênero exibido na pílula (Obrigatório) |
| `$url` | string | Link para a página de arquivos do gênero (Padrão: `#`) |
| `$class` | string | Classe CSS customizada adicional |

## SEO aplicado
- Título descritivo em `title` no link para otimizar acessibilidade e contexto para leitores de tela (`Ver mais animes de X`).
- Formato semântico usando a tag `<a>`.

## Responsividade
- Totalmente fluido. Adapta-se automaticamente a linhas flexíveis e grids sem quebrar o layout.

## Exemplo de uso
```php
<?php mm_render_component('atoms', 'badge-genero', [
    'genero' => 'Ação',
    'url'    => home_url('/genero/acao/')
]); ?>
```
