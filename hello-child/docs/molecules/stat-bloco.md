# Bloco de Estatísticas (stat-bloco)

**Tipo**: Molécula
**Arquivo**: `molecules/stat-bloco.php`
**CSS**: `molecules/stat-bloco.css`
**Criado em**: 2026-05-23

## Descrição

Bloco de estatísticas para página de detalhes do anime. Compõe rating-score + rank + popularidade + membros em um layout grid responsivo.

## Átomos utilizados
- `rating-score` — Nota de destaque com rótulos contextuais

## Variáveis CSS utilizadas
- `--neutral-800` - Cor de fundo do bloco
- `--color-border` - Cor da borda
- `--neutral-100` - Cor dos valores
- `--neutral-400` - Cor dos labels
- `--warning-400` - Cor do rank (dourado)
- `--brand-400` - Cor da popularidade
- `--success-400` - Cor dos membros
- `--text-xxs-size` - Tamanho dos labels
- `--text-md-size` - Tamanho dos valores
- `--text-md-sm-size` - Tamanho dos valores em mobile
- `--font-heading` - Fonte dos valores
- `--font-body` - Fonte dos labels
- `--font-weight-700` - Peso dos labels
- `--space-100`, `--space-200`, `--space-300`, `--space-400`, `--space-500` - Espaçamentos
- `--border-radius-300` - Raio da borda

## Parâmetros PHP
| Parâmetro | Tipo | Descrição |
|---|---|---|
| `score` | string | Nota do anime (ex: "8.5") |
| `score_label` | string | Label da nota (padrão: "Média") |
| `score_votes` | string | Contagem de votos (ex: "15.2k") |
| `rank` | string | Ranking (ex: "#42") |
| `rank_label` | string | Label do rank (padrão: "Ranking") |
| `popularity` | string | Popularidade (ex: "#156") |
| `pop_label` | string | Label da popularidade (padrão: "Popularidade") |
| `members` | string | Membros (ex: "125k") |
| `members_label` | string | Label dos membros (padrão: "Membros") |
| `class` | string | Classes adicionais |

## SEO aplicado
- Estrutura semântica com labels descritivos
- Valores numéricos em texto para leitores de tela

## Responsividade
- **Desktop (≥ 768px):** Grid 3 colunas para estatísticas secundárias
- **Tablet (≤ 768px):** Grid 2 colunas, padding reduzido
- **Mobile (≤ 480px):** Grid 1 coluna para estatísticas secundárias

## Exemplo de uso
```php
mm_render_component('molecules', 'stat-bloco', [
    'score'        => '8.5',
    'score_label'  => 'Média',
    'score_votes'  => '15.2k',
    'rank'         => '#42',
    'rank_label'   => 'Ranking',
    'popularity'   => '#156',
    'pop_label'    => 'Popularidade',
    'members'      => '125k',
    'members_label' => 'Membros'
]);
```
