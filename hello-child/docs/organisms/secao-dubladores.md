# secao-dubladores

**Tipo:** Organism  
**Arquivo PHP:** `organisms/secao-dubladores.php`  
**Arquivo CSS:** `organisms/secao-dubladores.css`  
**Depende de:** `molecules/card-personagem-dublador`

---

## Descrição

Seção que exibe uma coleção de voice actors (dubladores) de um anime. No desktop renderiza um grid de 4 colunas com cards horizontais; em tablet e mobile vira um carrossel horizontal com scroll snap.

---

## Parâmetros PHP

| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `titulo` | `string` | `'Dubladores'` | Título do `<h2>` da seção |
| `dubladores` | `array` | `[]` | Array de arrays com parâmetros de cada card |

### Estrutura de cada item em `$dubladores`

| Chave | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `va_name` | `string` | ✅ | Nome do dublador |
| `va_image` | `string` | — | URL da foto |
| `va_url` | `string` | — | Link para perfil MAL |
| `va_language` | `string` | — | Idioma (default: "Japonês") |
| `character_name` | `string` | — | Nome do personagem dublado |
| `episodios` | `int` | — | Nº de episódios participados |
| `ano_inicio` | `int` | — | Ano de início na obra |
| `ano_fim` | `int` | — | Ano de fim na obra |

---

## Responsividade

| Breakpoint | Comportamento |
|---|---|
| `< 64rem` (mobile + tablet) | Scroll horizontal, `scroll-snap-type: x mandatory`, cards verticais de `8rem` de largura |
| `≥ 64rem` (desktop) | `grid-template-columns: repeat(4, 1fr)`, cards horizontais |

---

## Variáveis CSS utilizadas

- `--container-max`, `--space-300` a `--space-700`
- `--font-heading`, `--text-sm-size`, `--text-md-sm-size`
- `--neutral-100`, `--neutral-700`
- `--border-radius-100`

---

## Exemplo de uso

```php
mm_render_component( 'organisms', 'secao-dubladores', array(
    'titulo'     => 'Dubladores',
    'dubladores' => array(
        array(
            'va_name'        => 'Romi Park',
            'va_image'       => 'https://cdn.myanimelist.net/images/voiceactors/1/54283.jpg',
            'va_url'         => 'https://myanimelist.net/people/83/Romi_Park',
            'va_language'    => 'Japonês',
            'character_name' => 'Edward Elric',
            'episodios'      => 64,
            'ano_inicio'     => 2009,
            'ano_fim'        => 2010,
        ),
        array(
            'va_name'        => 'Maxey Whitehead',
            'va_image'       => 'https://cdn.myanimelist.net/images/voiceactors/3/40141.jpg',
            'va_url'         => 'https://myanimelist.net/people/11746/Maxey_Whitehead',
            'va_language'    => 'Inglês',
            'character_name' => 'Alphonse Elric',
            'episodios'      => 64,
            'ano_inicio'     => 2010,
            'ano_fim'        => 2012,
        ),
    ),
) );
```
