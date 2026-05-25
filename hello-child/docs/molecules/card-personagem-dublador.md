# Card de Personagem e Dublador (card-personagem-dublador)

**Tipo:** Molécula  
**Arquivo:** `molecules/card-personagem-dublador.php`  
**CSS:** `molecules/card-personagem-dublador.css`  
**Criado em:** 2026-05-25  
**Última atualização:** 2026-05-25  

## Descrição
Card horizontal compacto no estilo MAL clássico para exibição conjunta de personagem e voice actor. O lado esquerdo mostra o avatar + nome + role do personagem; o lado direito (espelhado) mostra idioma + nome + avatar do VA. Cada lado é um link independente e clicável quando uma URL é fornecida.

## Átomos utilizados
- `atoms/avatar-personagem.php` (x2 — personagem e VA, tamanho fixo 54px)

## Variáveis CSS utilizadas
- `--neutral-800` (fundo do card)
- `--neutral-700` (borda e separador)
- `--neutral-100` (nome)
- `--neutral-400` (role, idioma)
- `--neutral-500` (role "other")
- `--color-primary` (role "Principal" + hover border)
- `--font-heading`, `--font-body`
- `--text-xs-size`, `--text-xxs-size`
- `--space-300`, `--space-400`
- `--border-radius-200`

## Parâmetros PHP

| Parâmetro | Tipo | Default | Descrição |
|---|---|---|---|
| `$character_name` | string | — | Nome do personagem (obrigatório) |
| `$character_image` | string | `''` | URL da imagem do personagem |
| `$character_role` | string | `'Principal'` | Role: "Principal" / "Secundário" / outro |
| `$character_url` | string | `''` | URL da página do personagem (torna o lado esq. clicável) |
| `$va_name` | string | `''` | Nome do voice actor (se vazio, lado direito não renderiza) |
| `$va_image` | string | `''` | URL da imagem do VA |
| `$va_language` | string | `'Japonês'` | Idioma do VA |
| `$va_url` | string | `''` | URL da página do VA (torna o lado dir. clicável) |

## SEO aplicado
- Links com `aria-label` descritivo ("Ver personagem: X", "Ver dublador: Y")
- Imagens via `avatar-personagem` com `alt` gerado automaticamente
- Lados não-link são `<div>`, evitando links vazios

## Responsividade
- Layout horizontal compacto funciona em todos os breakpoints
- Nome truncado com `text-overflow: ellipsis` para nomes longos em telas pequenas
- Padding interno reduz automaticamente com o `flex: 1` de cada lado

## Exemplo de uso

```php
// Exemplo completo (personagem principal + VA japonês com links)
mm_render_component( 'molecules', 'card-personagem-dublador', array(
    'character_name'  => 'Tanjiro Kamado',
    'character_image' => 'https://cdn.myanimelist.net/images/characters/tanjiro.jpg',
    'character_role'  => 'Principal',
    'character_url'   => home_url( '/personagem/tanjiro-kamado/' ),
    'va_name'         => 'Natsuki Hanae',
    'va_image'        => 'https://cdn.myanimelist.net/images/voiceactors/natsuki-hanae.jpg',
    'va_language'     => 'Japonês',
    'va_url'          => home_url( '/dublador/natsuki-hanae/' ),
) );

// Exemplo sem VA (apenas personagem)
mm_render_component( 'molecules', 'card-personagem-dublador', array(
    'character_name'  => 'Nezuko Kamado',
    'character_role'  => 'Principal',
) );

// Personagem secundário sem links
mm_render_component( 'molecules', 'card-personagem-dublador', array(
    'character_name'  => 'Zenitsu Agatsuma',
    'character_role'  => 'Secundário',
    'va_name'         => 'Hiro Shimono',
    'va_language'     => 'Japonês',
) );
```
