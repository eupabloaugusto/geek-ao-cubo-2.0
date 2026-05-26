# sidebar-anime-info

**Tipo:** Organism  
**Arquivo PHP:** `organisms/sidebar-anime-info.php`  
**Arquivo CSS:** `organisms/sidebar-anime-info.css`  
**Depende de:** `molecules/stat-bloco`  
**Fonte de dados:** 🔵 MyAnimeList / Jikan API (`/anime/{id}`)

---

## Descrição

Coluna lateral (sidebar) rica em informações técnicas e estatísticas exclusivas sobre o anime. É projetada para complementar o `hero-anime` na mesma página, exibindo **apenas** informações adicionais não duplicadas (evitando repetir o pôster principal, o score de média, classificação, estúdios primários, etc.).

Composta por:
1. **Estatísticas Secundárias Integradas:** Painel contendo ranking de popularidade, ranking de notas e membros (utiliza a molécula `stat-bloco` de forma verticalizada e compacta, ocultando o score já presente no hero).
2. **Metadados Textuais Únicos:** Lista de informações adicionais estruturadas (Exibição exata, Transmissão, Produtores, Licenciadores, Fonte de origem, Temas, Demografia, etc.) iterada dinamicamente a partir de um array plano associativo.

---

## Parâmetros PHP

| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `rank` | `string` | `''` | Rank de popularidade de nota (ex: `"#4"`) |
| `popularity` | `string` | `''` | Posição de popularidade por membros (ex: `"#39"`) |
| `members` | `string` | `''` | Quantidade total de membros/leitores (ex: `"3.2M"`) |
| `metadata` | `array` | `[]` | Array associativo de chave => valor representando as informações textuais da lista. |

---

## Responsividade

- **Desktop (≥ 48rem):** Sidebar de largura fixa e estreita, ideal para compor um layout de duas colunas (coluna principal + lateral). Os itens do `stat-bloco` empilham verticalmente de forma adaptada.
- **Mobile (< 48rem):** Comportamento fluido adaptado para `100%` de largura, integrando-se abaixo ou acima do conteúdo principal dependendo do grid do template de página.

---

## Variáveis CSS utilizadas

- `--container-max`, `--space-200` a `--space-700`
- `--font-heading`, `--font-body`
- `--text-xs-size`, `--text-xxs-size`
- `--neutral-100`, `--neutral-400`, `--neutral-700`, `--neutral-800`
- `--color-primary`, `--brand-300`
- `--border-radius-300`

---

## Exemplo de uso

```php
mm_render_component( 'organisms', 'sidebar-anime-info', array(
    'rank'       => '#4',
    'popularity' => '#39',
    'members'    => '3.2M',
    'metadata'   => array(
        'Exibição'      => '5 de Abr de 2009 a 4 de Jul de 2010',
        'Transmissão'   => 'Domingos às 17:00 (JST)',
        'Produtores'    => 'Aniplex, Square Enix, MBS',
        'Licenciadores' => 'Aniplex of America, Funimation',
        'Fonte'         => 'Mangá',
        'Temas'         => 'Militar, Drama, Fantasia',
        'Demografia'    => 'Shounen'
    ),
) );
```
