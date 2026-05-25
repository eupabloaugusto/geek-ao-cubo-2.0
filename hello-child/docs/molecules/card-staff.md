# card-staff

**Tipo:** Molécula  
**Arquivo PHP:** `molecules/card-staff.php`  
**Arquivo CSS:** `molecules/card-staff.css`  
**Átomo utilizado:** `avatar-personagem`  
**Fonte de dados:** 🔵 MyAnimeList / Jikan API (`/anime/{id}/staff`)  
**Criado em:** 2026-05-25

---

## Descrição

Card horizontal compacto para exibir um membro da equipe de produção de um anime. Exibe avatar circular, nome e cargo (ex.: "Diretor", "Compositor", "Original Creator").

O card inteiro é clicável quando um `staff_url` é fornecido, tornando-se um elemento `<a>`. Sem URL, renderiza como `<div>` não clicável.

---

## Layout

```
┌──────────────────────────────────────────┐
│  [avatar]  Nome do Membro                │
│            Cargo na Produção             │
└──────────────────────────────────────────┘
```

---

## Parâmetros PHP

| Parâmetro     | Tipo     | Obrigatório | Default | Descrição                                   |
|---------------|----------|-------------|---------|---------------------------------------------|
| `staff_name`  | `string` | ✅ Sim      | —       | Nome completo do membro da equipe           |
| `staff_image` | `string` | ❌ Não      | `''`    | URL da foto (fallback SVG se ausente)       |
| `staff_role`  | `string` | ❌ Não      | `''`    | Cargo na produção (ex.: "Diretor")          |
| `staff_url`   | `string` | ❌ Não      | `''`    | URL do perfil MAL (torna o card clicável)   |

---

## Variáveis CSS utilizadas

| Token                   | Uso                                    |
|-------------------------|----------------------------------------|
| `--neutral-800`         | Background do card                     |
| `--neutral-700`         | Borda padrão                           |
| `--neutral-100`         | Cor do nome                            |
| `--neutral-400`         | Cor do cargo                           |
| `--color-primary`       | Borda e nome no hover                  |
| `--font-heading`        | Família do nome                        |
| `--font-body`           | Família do cargo                       |
| `--text-xs-size`        | Tamanho do nome                        |
| `--text-xxs-size`       | Tamanho do cargo e modo compacto       |
| `--space-200/300/400`   | Padding e gap                          |
| `--border-radius-200`   | Arredondamento                         |

---

## SEO & Acessibilidade

- `aria-label="Ver perfil de {nome}"` no `<a>` quando clicável
- `alt="Avatar de {nome}"` gerado pelo átomo `avatar-personagem`
- Fallback SVG de silhueta quando imagem ausente

---

## Responsividade

| Breakpoint       | Comportamento                                          |
|------------------|--------------------------------------------------------|
| `> 30rem`        | Layout padrão: avatar 48px, padding normal             |
| `≤ 30rem (~480px)` | Modo compacto: avatar 40px, padding e gap reduzidos  |

---

## Exemplos de uso

```php
// Card com todos os dados:
mm_render_component( 'molecules', 'card-staff', array(
    'staff_name'  => 'Haruo Sotozaki',
    'staff_image' => 'https://cdn.myanimelist.net/images/voiceactors/sotozaki.jpg',
    'staff_role'  => 'Diretor',
    'staff_url'   => 'https://myanimelist.net/people/12345/',
) );

// Card somente com nome e cargo (sem foto nem link):
mm_render_component( 'molecules', 'card-staff', array(
    'staff_name' => 'Yuki Kajiura',
    'staff_role' => 'Composição Musical',
) );
```
