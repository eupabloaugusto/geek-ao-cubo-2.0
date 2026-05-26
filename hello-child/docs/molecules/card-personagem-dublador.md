# card-personagem-dublador

**Tipo:** Molécula  
**Arquivo PHP:** `molecules/card-personagem-dublador.php`  
**Arquivo CSS:** `molecules/card-personagem-dublador.css`  
**Atualizado em:** 2026-05-25 (v2.2.0 — redesign completo)

---

## Descrição

Card focado no **voice actor (dublador)** com foto circular em destaque. Exibe nome do VA, personagem dublado, idioma e linha de meta opcional com episódios e período de atuação (ex: `519 episódios • 2000–2024`).

- **Mobile / Tablet:** `flex-column` centralizado — avatar em cima, texto abaixo
- **Desktop (≥ 64rem):** `flex-row` — avatar à esquerda, info à direita

O card inteiro é um `<a>` clicável quando `va_url` é fornecida; caso contrário, renderiza como `<article>`.

---

## Parâmetros PHP

| Parâmetro | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `va_name` | `string` | ✅ | Nome do dublador |
| `va_image` | `string` | — | URL da foto circular |
| `va_url` | `string` | — | URL do perfil MAL (torna o card um `<a>`) |
| `va_language` | `string` | — | Idioma; default: `'Japonês'` |
| `character_name` | `string` | — | Nome do personagem dublado |
| `episodios` | `int` | — | Nº de episódios participados |
| `ano_inicio` | `int` | — | Ano de início na obra |
| `ano_fim` | `int` | — | Ano de fim na obra |

### Lógica da linha de meta

| Dados disponíveis | Resultado exibido |
|---|---|
| Episódios + anos início e fim | `64 episódios • 2009–2010` |
| Só episódios | `64 episódios` |
| Só anos | `2009–2010` |
| Nenhum | linha omitida |

---

## Átomos utilizados

- `atoms/avatar-personagem` — `size=80` (sobrescrito para `5rem` no mobile, `4.5rem` no desktop via `!important`)

---

## Variáveis CSS utilizadas

- `--neutral-800` (fundo), `--neutral-700` (borda)
- `--neutral-100` (nome), `--neutral-400` (personagem), `--neutral-500` (meta)
- `--color-primary` (idioma + hover border)
- `--font-heading`, `--font-body`
- `--text-xs-size`, `--text-xxs-size`
- `--space-300`, `--space-400`, `--border-radius-200`

---

## SEO / Acessibilidade

- Schema.org `Person` via `itemscope itemtype`
- `itemprop="name"` no nome do VA
- `aria-label` descritivo no link: "Ver perfil do dublador: {nome}"
- Imagem com `alt` gerado pelo átomo `avatar-personagem`

---

## Exemplo de uso

```php
mm_render_component( 'molecules', 'card-personagem-dublador', array(
    'va_name'        => 'Romi Park',
    'va_image'       => 'https://cdn.myanimelist.net/images/voiceactors/1/54283.jpg',
    'va_url'         => 'https://myanimelist.net/people/83/Romi_Park',
    'va_language'    => 'Japonês',
    'character_name' => 'Edward Elric',
    'episodios'      => 64,
    'ano_inicio'     => 2009,
    'ano_fim'        => 2010,
) );
```
