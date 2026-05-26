# secao-staff

**Tipo:** Organismo  
**Arquivo:** `organisms/secao-staff.php`  
**CSS:** `organisms/secao-staff.css`  
**Criado em:** 2026-05-25  
**Última atualização:** 2026-05-25  

## Descrição

Seção de equipe de produção para a página de detalhe do anime. Agrupa dinamicamente os membros da equipe por cargo (`role_group`) preservando a ordem de aparição no array. Cada grupo exibe um subtítulo H3 com barra laranja à esquerda (padrão do sistema) e até `$max_per_group` cards `card-staff` em grade responsiva. Botão opcional "Ver equipe completa" no rodapé. Sem JavaScript.

## Moléculas utilizadas

- `molecules/card-staff.php` — cada membro da equipe (avatar + nome + cargo)

## Parâmetros PHP

| Parâmetro | Tipo | Obrigatório | Padrão | Descrição |
|---|---|---|---|---|
| `titulo` | string | — | `'Equipe de Produção'` | Título da seção (H2) |
| `staff` | array | ✅ | — | Array plano de membros. Cada item aceita os parâmetros de `card-staff` + campo extra `role_group` |
| `max_per_group` | int | — | `6` | Limite de cards exibidos por grupo de cargo |
| `ver_mais_url` | string | — | `''` | URL do botão no rodapé (omitido se vazio) |
| `ver_mais_label` | string | — | `'Ver equipe completa'` | Label customizável do botão |

### Campos do item de staff

Cada item do array `staff` aceita os parâmetros nativos de `card-staff` mais o campo de agrupamento:

| Campo | Tipo | Descrição |
|---|---|---|
| `staff_name` | string | ✅ Nome do membro (obrigatório) |
| `staff_image` | string | URL da foto (opcional — fallback silhueta) |
| `staff_url` | string | URL do perfil (torna card clicável) |
| `staff_role` | string | Cargo exibido no card (ex: "Diretor") |
| `role_group` | string | Grupo de agrupamento (ex: "Direção") — não é passado ao card |

## Variáveis CSS utilizadas

- `--neutral-100`, `--neutral-300` — títulos e subtítulos
- `--color-primary` — barra laranja dos subtítulos de grupo
- `--font-heading` — tipografia
- `--text-sm-size`, `--text-md-sm-size`, `--text-xs-size` — escala tipográfica
- `--space-200` a `--space-700` — espaçamentos
- `--border-left: 0.1875rem` — barra de realce laranja
- `--container-max` — largura máxima do inner
- `--icon-xs` — ícone do botão

## SEO aplicado

- `aria-label` na `<section>` com o título
- `rel="nofollow noopener"` + `target="_blank"` no botão externo

## Responsividade

- **Mobile (375px):** grade de 1 coluna, padding `--space-400`
- **Tablet (≥ 48rem):** grade de 2 colunas
- **Desktop (≥ 64rem):** grade de 3 colunas, padding-inline `--space-600`, título maior

## Exemplo de uso

```php
mm_render_component( 'organisms', 'secao-staff', array(
    'titulo'        => 'Equipe de Produção',
    'max_per_group' => 6,
    'ver_mais_url'  => 'https://myanimelist.net/anime/5114/staff',
    'staff'         => array(
        array(
            'staff_name'  => 'Haruo Sotozaki',
            'staff_image' => 'https://cdn.myanimelist.net/images/voiceactors/1/40617.jpg',
            'staff_url'   => 'https://myanimelist.net/people/8143/',
            'staff_role'  => 'Diretor',
            'role_group'  => 'Direção',
        ),
        array(
            'staff_name'  => 'Yuki Kajiura',
            'staff_image' => 'https://cdn.myanimelist.net/images/voiceactors/2/57146.jpg',
            'staff_url'   => 'https://myanimelist.net/people/2246/',
            'staff_role'  => 'Composição Musical',
            'role_group'  => 'Música',
        ),
        array(
            'staff_name' => 'Akira Matsushima',
            'staff_role' => 'Design de Personagens',
            'role_group' => 'Animação',
        ),
    ),
) );
```
