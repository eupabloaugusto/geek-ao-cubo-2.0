# relacionado-item

**Tipo:** Molécula  
**Arquivo PHP:** `molecules/relacionado-item.php`  
**Arquivo CSS:** `molecules/relacionado-item.css`  
**Átomo utilizado:** `imagem-capa`  
**Fonte de dados:** 🔵 MyAnimeList / Jikan API (`/anime/{id}/relations`)  
**Criado em:** 2026-05-25

---

## Descrição

Card horizontal compacto para exibir um anime relacionado. Mostra thumbnail poster (4rem de largura, proporção 2:3), tipo de relação em destaque (ex: "SEQUÊNCIA") e título truncado em até 2 linhas.

O card inteiro é clicável quando `anime_url` é fornecido (`<a>`). Sem URL, renderiza como `<div>`.

---

## Layout

```
┌──────────────────────────────────────────────┐
│ [thumb] SEQUÊNCIA                            │
│         Título do Anime Relacionado          │
└──────────────────────────────────────────────┘
```

---

## Parâmetros PHP

| Parâmetro       | Tipo     | Obrigatório | Default | Descrição                                        |
|-----------------|----------|-------------|---------|--------------------------------------------------|
| `anime_title`   | `string` | ✅ Sim      | —       | Título do anime relacionado                      |
| `anime_image`   | `string` | ❌ Não      | `''`    | URL da capa (fallback placeholder se ausente)    |
| `anime_url`     | `string` | ❌ Não      | `''`    | URL da página do anime (torna o card clicável)   |
| `relation_type` | `string` | ❌ Não      | `''`    | Tipo de relação (ex: "Sequência", "Prequel")     |

---

## Tipos de relação comuns (Jikan API)

| Valor API         | Exibição sugerida |
|-------------------|-------------------|
| `Sequel`          | Sequência          |
| `Prequel`         | Prequel            |
| `Alternative Setting` | Universo Alternativo |
| `Alternative Version` | Versão Alternativa |
| `Side Story`      | História Paralela  |
| `Summary`         | Resumo             |
| `Full Story`      | História Completa  |
| `Spin-off`        | Spin-off           |
| `Adaptation`      | Adaptação          |
| `Other`           | Outros             |

---

## Variáveis CSS utilizadas

| Token                 | Uso                                       |
|-----------------------|-------------------------------------------|
| `--neutral-800`       | Background do card                        |
| `--neutral-700`       | Borda padrão                              |
| `--neutral-100`       | Cor do título                             |
| `--color-primary`     | Cor do tipo de relação e hover            |
| `--font-heading`      | Família do título                         |
| `--font-body`         | Família do tipo de relação                |
| `--text-xs-size`      | Tamanho do título                         |
| `--text-xxs-size`     | Tamanho do tipo de relação                |
| `--space-100/300/400` | Gaps e padding                            |
| `--border-radius-200` | Arredondamento do card                    |

---

## SEO & Acessibilidade

- `aria-label="Ver anime relacionado: {título}"` no `<a>` quando clicável
- `alt="Capa de {título}"` na imagem de capa
- Fallback placeholder do átomo `imagem-capa` quando imagem ausente

---

## Responsividade

| Breakpoint          | Comportamento                                       |
|---------------------|-----------------------------------------------------|
| `> 30rem`           | Thumbnail 4rem, padding normal                      |
| `≤ 30rem (~480px)`  | Thumbnail 3.5rem, padding reduzido                  |

---

## Exemplos de uso

```php
// Item completo com todos os dados:
mm_render_component( 'molecules', 'relacionado-item', array(
    'anime_title'   => 'Fullmetal Alchemist (2003)',
    'anime_image'   => 'https://cdn.myanimelist.net/images/anime/4/19644.jpg',
    'anime_url'     => home_url( '/anime/fullmetal-alchemist/' ),
    'relation_type' => 'Adaptação',
) );

// Sem imagem e sem link:
mm_render_component( 'molecules', 'relacionado-item', array(
    'anime_title'   => 'Attack on Titan: The Final Season',
    'relation_type' => 'Sequência',
) );
```
