# Seção de Esteira de Animes (secao-esteira-animes)

**Tipo:** Organismo  
**Arquivo:** `organisms/secao-esteira-animes.php`  
**CSS:** `organisms/secao-esteira-animes.css`  
**JS:** `organisms/secao-esteira-animes.js`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-25  

## Descrição
Organismo nobre em formato de trilho horizontal deslizante (esteira/carousel) no estilo de plataformas de streaming premium (Netflix, Crunchyroll). Exibe uma coleção de animes com **scroll infinito** — ao chegar no último card, a esteira continua fluindo de volta para o início de forma invisível, sem nunca travar. Usa Scroll Snap físico nativo com clonagem de slides em JS (técnica triple-buffer).

## Moléculas/Átomos utilizados
- `molecules/card-anime.php`
- `atoms/btn-nav-arrow.php`

## Variáveis CSS utilizadas
- `--neutral-100`, `--neutral-400` (cores do cabeçalho)
- `--color-primary` (destaque de hover)
- `--space-300`, `--space-500`, `--space-800` (alinhamentos estruturais e gap entre cards)
- `--border-radius-300` (arredondamento do trilho)

## Parâmetros PHP
| Parâmetro | Tipo | Descrição |
|---|---|---|
| `$titulo_secao` | string | Título principal da esteira (ex: "Em Exibição nesta Temporada") |
| `$url_ver_todos` | string | URL opcional para a página de arquivos completa ("Ver Todos") |
| `$animes` | array | Lista de arrays contendo os dados de cada `card-anime` a ser renderizado |

## SEO aplicado
- Cabeçalho estruturado usando a tag semântica `<header>` e título com `<h2>` para manter a integridade da hierarquia estrutural SEO do blog.
- Tag `<section>` com `aria-label` associado ao título da seção para acessibilidade de leitores de tela.
- Link de "Ver Todos" com `aria-label` estendido contextualizando qual seção está sendo expandida.

## Responsividade
- **Mobile-first com layout fluido**: A largura dos slides individuais é controlada dinamicamente via `clamp(145px, 22vw, 215px)`.
- **Mobile**: Navegação via swipe nativo (iOS/Android). O scroll infinito funciona também por touch — o teleporte é invisível pois ocorre no intervalo de momentum após o gesto.
- **Desktop**: Setas de navegação sempre ativas (nunca desabilitadas, pois o scroll é infinito). Drag-to-scroll com mouse.

## Arquitetura do Scroll Infinito (JS)
1. **Clonagem dupla**: Ao inicializar, o JS cria duas cópias completas de todos os slides — uma prepended antes do set original, outra appended após.
2. **Posição inicial**: `scrollLeft` é definido para `realSlides[0].offsetLeft` (= largura exata de um set completo), posicionando o viewport no início do set real.
3. **Teleporte invisível**: Um listener de `scroll` monitora a posição continuamente:
   - `scrollLeft < singleSetWidth` → o usuário entrou nos clones-antes → `scrollLeft += singleSetWidth`
   - `scrollLeft >= singleSetWidth * 2` → entrou nos clones-depois → `scrollLeft -= singleSetWidth`
4. **Sem flash**: O teleporte usa `scrollBehavior: auto` + `scrollSnapType: none` por um tick (`setTimeout 0`) para evitar qualquer animação ou re-snap visível.
5. **Resize-safe**: `getSingleSetWidth()` recalcula `offsetLeft` a cada acesso, adaptando automaticamente a quaisquer mudanças de layout.

## Exemplo de uso
```php
<?php mm_render_component('organisms', 'secao-esteira-animes', [
    'titulo_secao'  => 'Temporada de Outono 2026',
    'url_ver_todos' => home_url('/temporada/outono-2026/'),
    'animes'        => [
        [
            'titulo'     => 'Chainsaw Man',
            'url'        => '#',
            'imagem_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&q=80',
            'nota'       => '8.6',
            'horario'    => 'Sábados, 12h',
            'generos'    => ['Ação', 'Gore']
        ],
        [
            'titulo'     => 'Solo Leveling',
            'url'        => '#',
            'imagem_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&q=80',
            'nota'       => '8.9',
            'generos'    => ['Ação', 'Fantasia']
        ]
    ]
]); ?>
```
