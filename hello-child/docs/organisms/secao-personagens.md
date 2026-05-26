# secao-personagens

**Tipo:** Organism  
**Arquivo PHP:** `organisms/secao-personagens.php`  
**Arquivo CSS:** `organisms/secao-personagens.css`  
**Depende de:** `molecules/card-personagem`

---

## Descrição

Seção que exibe uma grade de cards cinematográficos de personagem (`card-personagem` — pôster 2:3 com overlay gradiente). Mobile usa scroll horizontal com snap; tablet e desktop usam grid auto-fill responsivo.

---

## Parâmetros PHP

| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `titulo` | `string` | `'Personagens'` | Título do `<h2>` da seção |
| `personagens` | `array` | `[]` | Array de arrays com parâmetros de cada card |

### Estrutura de cada item em `$personagens`

| Chave | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `name` | `string` | ✅ | Nome do personagem |
| `image_url` | `string` | — | URL da imagem de capa (proporção 2:3) |
| `name_kanji` | `string` | — | Nome em japonês/kanji |
| `role` | `string` | — | `'Principal'` / `'Secundário'` |
| `url` | `string` | — | Link para a página do personagem |

---

## Responsividade

| Breakpoint | Comportamento |
|---|---|
| `< 48rem` (mobile) | Scroll horizontal, `scroll-snap-type: x mandatory`, cards `7rem` de largura |
| `≥ 48rem` (tablet) | `grid-template-columns: repeat(auto-fill, minmax(8rem, 1fr))` |
| `≥ 64rem` (desktop) | `grid-template-columns: repeat(auto-fill, minmax(9rem, 1fr))` |

---

## Variáveis CSS utilizadas

- `--container-max`, `--space-300` a `--space-700`
- `--font-heading`, `--text-sm-size`, `--text-md-sm-size`
- `--neutral-100`, `--neutral-700`
- `--border-radius-100`

---

## Exemplo de uso

```php
mm_render_component( 'organisms', 'secao-personagens', array(
    'titulo'      => 'Personagens',
    'personagens' => array(
        array(
            'name'        => 'Edward Elric',
            'name_kanji'  => 'エドワード・エルリック',
            'image_url'   => 'https://cdn.myanimelist.net/images/characters/9/310307.jpg',
            'role'        => 'Principal',
            'url'         => 'https://myanimelist.net/character/11/Edward_Elric',
        ),
        array(
            'name'        => 'Alphonse Elric',
            'name_kanji'  => 'アルフォンス・エルリック',
            'image_url'   => 'https://cdn.myanimelist.net/images/characters/9/214141.jpg',
            'role'        => 'Principal',
            'url'         => 'https://myanimelist.net/character/12/Alphonse_Elric',
        ),
        array(
            'name'  => 'Roy Mustang',
            'role'  => 'Secundário',
        ),
    ),
) );
```
