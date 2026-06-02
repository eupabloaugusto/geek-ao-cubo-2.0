# Briefing v4 — modomaratona.com
> Blog de anime com renda passiva orgânica via SEO, Adsense e afiliados  
> Atualizado com: Atomic Design via IA, tema geek-ao-cubo (standalone), sistema de documentação estruturado, vitrine de componentes, design tokens definitivos

---

## 1. Visão geral do projeto

| | |
|---|---|
| **Domínio** | modomaratona.com |
| **Nicho principal** | Anime (expansão futura: games, mangá, tech geek) |
| **Plataforma** | WordPress |
| **Tema** | geek-ao-cubo — tema standalone, zero bloat, 100% controlado via CSS |
| **Monetização principal** | Google Adsense |
| **Monetização secundária** | Afiliados (Shopee, Mercado Livre, Amazon), Mediavine (meta futura) |
| **Meta de tráfego** | 300.000 visitas/mês |
| **Horizonte realista** | 18 a 24 meses |

---

## 2. Projeção de tráfego e receita

### Tráfego por fase

| Período | Visitas/mês estimadas |
|---|---|
| Mês 1–2 | 500 – 3.000 |
| Mês 3 | 10.000 – 40.000 |
| Mês 6 | 50.000 – 120.000 |
| Mês 12 | 150.000 – 300.000 |
| Mês 18–24 | 300.000+ |

### Receita estimada (Adsense)

| Visitas/mês | Pageviews | RPM efetivo (c/ adblock ~50%) | Receita estimada |
|---|---|---|---|
| 40.000 | 60.000 | R$ 6 – R$ 12 | R$ 180 – R$ 360 |
| 120.000 | 180.000 | R$ 8 – R$ 15 | R$ 720 – R$ 1.800 |
| 300.000 | 450.000 | R$ 10 – R$ 18 | R$ 3.000 – R$ 8.000 |

> **Nota:** RPM flutua por perfil de usuário. Visitantes com histórico de compra recente geram R$ 25–60 RPM via remarketing automático do Adsense.

### Anúncios por página
- **2 anúncios** como padrão — equilibra receita e experiência
- Posts curtos (resumo de episódio): 1–2 anúncios
- Posts longos (guias, listas): até 3 anúncios
- Controle por tipo de post via template PHP customizado
- Todos os links de afiliado com `rel="sponsored"` obrigatório

### Adblock
- Estimativa de 40–60% do público usando adblock (perfil jovem)
- **Não implementar popup bloqueante** — penalização Google (interstitials intrusivos desde 2017)
- Usar banner fixo não intrusivo pedindo desativação do adblock
- Afiliados como receita paralela imune ao adblock

---

## 3. Stack técnico

### Tema: geek-ao-cubo

Tema WordPress standalone — extremamente leve, sem estilos próprios de terceiros e sem jQuery no frontend. Toda a identidade visual é 100% responsabilidade do CSS customizado via Atomic Design.

**Por que geek-ao-cubo:**
- Zero CSS desnecessário — sem resetar estilos genéricos de tema pai
- Estrutura semântica limpa — fácil de sobrescrever com BEM
- Tema standalone — não depende de tema pai, sem risco de updates quebrarem o visual
- Nenhum bloat de pagebuilder — a IA gera o HTML/PHP diretamente

**Estrutura de arquivos do tema:**
```
geek-ao-cubo/
├── style.css              ← identificação do tema
├── functions.php          ← enqueue de estilos e scripts
├── design-tokens.css      ← TODAS as CSS custom properties (fonte única da verdade)
├── atoms/                 ← componentes atômicos (botão, input, badge, tag...)
├── molecules/             ← combinações de átomos (card-anime, form-busca...)
├── organisms/             ← seções completas (header, footer, grid-temporada...)
├── templates/             ← templates de página por CPT
├── docs/                  ← documentação de cada organismo
└── Novos-arquivos/        ← pasta de entrada para logo.svg e assets externos
```

### Arquitetura de desenvolvimento

- **PHP** — gerado e mantido pela IA. Estrutura, CPTs, lógica, REST API. O designer não edita PHP.
- **HTML** — gerado pela IA com marcações claras de onde o designer pode editar. Estrutura semântica com classes BEM.
- **CSS** — responsabilidade total do designer. Todo o visual controlado via CSS custom properties.

> **Regra absoluta:** toda a interface vem de CSS custom properties. Nenhum valor de cor, tipografia, espaçamento, borda ou sombra é escrito diretamente no HTML ou PHP — sempre via variável.

---

## 4. Atomic Design — estrutura completa gerada por IA

### Filosofia

A IA é responsável por construir e manter todo o sistema de Atomic Design do blog. Cada elemento — desde um botão até uma seção completa — nasce como um átomo e evolui de forma documentada. Nada é criado de forma avulsa.

### Hierarquia

```
Átomos (atoms/)
    → elementos mínimos e indivisíveis
    → botão, input, label, badge, tag, ícone, imagem, anúncio-adsense

Moléculas (molecules/)
    → combinação de 2+ átomos com função específica
    → card-anime, card-episodio, form-busca, carrossel-afiliados

Organismos (organisms/)
    → seções completas compostas por moléculas e átomos
    → header, footer, grid-animes, sidebar, secao-veja-tambem

Templates (templates/)
    → estrutura de página por CPT, sem conteúdo real
    → single-anime.php, single-episodio.php, archive-temporada.php

Páginas
    → templates preenchidos com conteúdo real via WordPress
```

### Inventário de átomos (base inicial)

| Átomo | Arquivo | Descrição |
|---|---|---|
| Botão primário | `atoms/btn-primary.php` | CTA principal — ex: "Ver mais episódios" |
| Botão secundário | `atoms/btn-secondary.php` | Ação secundária — ex: "Voltar" |
| Input de busca | `atoms/input-busca.php` | Campo de texto com ícone de lupa |
| Badge de gênero | `atoms/badge-genero.php` | Tag clicável — ex: "Ação", "Romance" |
| Badge de status | `atoms/badge-status.php` | Ex: "Em exibição", "Finalizado" |
| Nota MAL | `atoms/nota-mal.php` | Exibe nota do MyAnimeList com estrela |
| Imagem de capa | `atoms/imagem-capa.php` | WebP com lazy load e alt text SEO |
| Ícone SVG | `atoms/icone.php` | SVG inline com aria-label |
| Anúncio Adsense | `atoms/anuncio-adsense.php` | Container responsivo para anúncio |
| Breadcrumb | `atoms/breadcrumb.php` | Navegação hierárquica com schema markup |
| Aviso adblock | `atoms/aviso-adblock.php` | Banner não intrusivo pedindo desativação |

### Inventário de moléculas (base inicial)

| Molécula | Arquivo | Átomos utilizados |
|---|---|---|
| Card de anime | `molecules/card-anime.php` | imagem-capa + badge-genero + nota-mal + btn-primary |
| Card de episódio | `molecules/card-episodio.php` | imagem-capa + badge-status + texto |
| Card de review | `molecules/card-review.php` | nota-mal + prós/contras + btn-primary |
| Form de busca | `molecules/form-busca.php` | input-busca + btn-primary |
| Item de afiliado | `molecules/item-afiliado.php` | imagem-capa + texto + btn-primary (sponsored) |
| Carrossel de afiliados | `molecules/carrossel-afiliados.php` | múltiplos item-afiliado + lazy load |
| Nav item | `molecules/nav-item.php` | ícone + label + link |
| Meta do post | `molecules/meta-post.php` | data + autor + tempo de leitura |

### Inventário de organismos (base inicial)

| Organismo | Arquivo | Descrição |
|---|---|---|
| Header | `organisms/header.php` | Logo + nav horizontal/hamburguer + busca |
| Footer | `organisms/footer.php` | Links + créditos + links legais |
| Grid de animes | `organisms/grid-animes.php` | auto-fill responsivo de cards |
| Sidebar | `organisms/sidebar.php` | Busca + destaques + anúncio |
| Seção "Veja também" | `organisms/secao-veja-tambem.php` | scroll mobile / grid desktop |
| Hero do anime | `organisms/hero-anime.php` | capa grande + dados + CTA |
| Lista de episódios | `organisms/lista-episodios.php` | episódios ordenados com paginação |
| Calendário de temporada | `organisms/calendario-temporada.php` | grid por dia da semana |

---

## 5. Sistema de documentação

### Estrutura da pasta `docs/`

Cada organismo possui seu próprio arquivo de documentação. A IA atualiza automaticamente a documentação ao criar ou modificar qualquer componente.

```
docs/
├── README.md                  ← índice geral + convenções globais
├── atoms/
│   ├── btn-primary.md
│   ├── input-busca.md
│   └── ... (um .md por átomo)
├── molecules/
│   ├── card-anime.md
│   └── ... (um .md por molécula)
├── organisms/
│   ├── header.md
│   └── ... (um .md por organismo)
└── changelog.md               ← log de todas as alterações com data
```

### Modelo de documentação por componente

```markdown
# [Nome do Componente]

**Tipo:** Átomo / Molécula / Organismo  
**Arquivo:** `atoms/nome-do-componente.php`  
**CSS:** `atoms/nome-do-componente.css`  
**Criado em:** YYYY-MM-DD  
**Última atualização:** YYYY-MM-DD  

## Descrição
O que esse componente faz e quando é usado.

## Átomos utilizados (para moléculas/organismos)
- `atoms/nome.php`

## Variáveis CSS utilizadas
- `--color-primary`
- `--text-md`

## Parâmetros PHP
| Parâmetro | Tipo | Descrição |
|---|---|---|
| `$titulo` | string | Título exibido no componente |

## SEO aplicado
- alt text nas imagens
- aria-label nos ícones
- schema markup (se aplicável)

## Responsividade
- Mobile (375px): descrição do comportamento
- Desktop (1280px): descrição do comportamento

## Exemplo de uso
```php
get_template_part('atoms/btn-primary', null, ['label' => 'Ver anime', 'url' => $url]);
```
```

### Changelog obrigatório (`docs/changelog.md`)

Toda criação, alteração ou remoção de componente deve ser registrada:

```markdown
## [YYYY-MM-DD]

### Adicionado
- `atoms/badge-status.php` — badge de status do anime (em exibição / finalizado)

### Alterado
- `molecules/card-anime.php` — adicionado suporte ao parâmetro `$mostrar_nota`

### Removido
- (nenhum)
```

---

## 6. Vitrine de componentes (`storybook.html`)

### Propósito

Toda vez que um átomo, molécula, organismo ou template é criado ou alterado, a IA deve atualizar um arquivo `storybook.html` — uma página HTML estática que exibe visualmente todos os componentes do sistema, nomeados e organizados por categoria.

Essa vitrine é a referência visual viva do Atomic Design. Ela permite ao designer ver, revisar e validar qualquer componente sem precisar acessar o WordPress.

### Localização

```
geek-ao-cubo/
└── storybook.html    ← vitrine visual, sempre atualizada
```

### Estrutura da página

```
storybook.html
├── Cabeçalho — nome do projeto + data da última atualização
├── Navegação interna — âncoras para cada seção
├── Seção: Tokens de design
│   └── paleta de cores, escala tipográfica, espaçamentos, raios, sombras
├── Seção: Átomos
│   └── cada átomo renderizado com: nome + descrição curta + visualização
├── Seção: Moléculas
│   └── cada molécula renderizada com: nome + átomos utilizados + visualização
├── Seção: Organismos
│   └── cada organismo renderizado com: nome + descrição + visualização
└── Seção: Templates
    └── cada template com: nome + CPT correspondente + esboço de layout
```

### Regras da vitrine

- **Atualização obrigatória:** toda criação ou alteração de componente no Atomic Design deve refletir imediatamente no `storybook.html`
- **Nomeação visível:** cada componente exibe seu nome exato de arquivo (ex: `atoms/btn-primary.php`) junto à visualização
- **Categorias separadas:** átomos, moléculas, organismos e templates são seções distintas com cabeçalho próprio
- **Tokens visuais:** a seção de tokens exibe as CSS custom properties com sua cor/valor real renderizado — não apenas o nome da variável
- **Sem dependência de WordPress:** o arquivo é HTML puro, funciona aberto diretamente no browser, sem servidor
- **CSS inline ou via `design-tokens.css`:** usa as mesmas variáveis do projeto para que a vitrine reflita o visual real
- **Data de atualização no topo:** exibe quando foi a última modificação do arquivo

### Exemplo de entrada na vitrine (átomo)

```html
<!-- Seção: Átomos -->
<article class="sb-component" id="btn-primary">
  <header class="sb-component__header">
    <h3 class="sb-component__name">Botão Primário</h3>
    <code class="sb-component__file">atoms/btn-primary.php</code>
    <p class="sb-component__desc">CTA principal — ex: "Ver mais episódios"</p>
  </header>
  <div class="sb-component__preview">
    <button class="btn btn--primary">Ver mais episódios</button>
  </div>
</article>
```

### Registro no changelog

Toda atualização do `storybook.html` deve ser registrada no `docs/changelog.md`:

```markdown
### Alterado
- `storybook.html` — adicionado átomo `badge-status` na seção Átomos
```

---

## 7. Pasta "Novos-arquivos" — fluxo de assets externos

### Propósito

Ponto de entrada único para qualquer asset externo (logo, ícones, fontes, imagens institucionais). A IA coleta o arquivo e o redireciona ao local correto dentro da estrutura do projeto.

```
Novos-arquivos/
├── logo.svg              ← enviado pelo designer
├── favicon.png           ← enviado pelo designer
└── ... outros assets
```

### Fluxo automático

```
Designer deposita arquivo em Novos-arquivos/
    → IA identifica o tipo de arquivo
    → IA move para o local correto:
        logo.svg      → /img/logo.svg + atoms/logo.php atualizado
        favicon.png   → /img/favicon.png + functions.php atualizado
        ícone-X.svg   → /img/icons/icone-X.svg + atoms/icone.php atualizado
    → IA atualiza a documentação do átomo afetado
    → IA registra no changelog.md
    → Pasta Novos-arquivos/ fica vazia
```

> **Regra:** a pasta `Novos-arquivos/` nunca deve acumular arquivos. Após o processamento, ela fica vazia aguardando o próximo envio.

---

## 8. Regras absolutas de desenvolvimento (`.windsurfrules`)

```
TEMA: geek-ao-cubo — tema standalone, sem tema pai

ATOMIC DESIGN:
- Nada é criado de forma avulsa — tudo segue a hierarquia atoms → molecules → organisms
- Cada novo componente recebe seu arquivo .php, seu arquivo .css e sua documentação .md
- Nenhum componente é duplicado — sempre reutilizar o que já existe
- A IA não inventa estrutura — apenas monta componentes existentes

CSS:
- Nunca use valores fixos de cor, tipografia ou espaçamento
- Sempre use CSS custom properties definidas em design-tokens.css
- TODOS os valores em rem, nunca px (exceto breakpoints e bordas de 1px)
- Conversão: px ÷ 16 = rem (ex: 24px = 1.5rem)
- Cada componente tem seu próprio arquivo CSS
- Responsividade sempre dentro do arquivo do componente

RESPONSIVIDADE:
- Mobile-first obrigatório com clamp()
- Layout fluido entre 375px (mínimo) e 1280px (máximo)
- Sem breakpoints rígidos — usar auto-fill + minmax + clamp()

PHP:
- Template parts do WordPress com get_template_part()
- HTML semântico sem classes utilitárias
- Parâmetros passados via array $args
- Nunca editar PHP para mudanças visuais

SEO:
- SEO estratégico em TODO lugar onde é possível aplicar
- alt text otimizado em todas as imagens (obrigatório)
- aria-label em todos os ícones interativos
- Schema markup nos templates: Article, FAQPage, BreadcrumbList
- H1 único por página — hierarquia semântica
- rel="sponsored" em 100% dos links de afiliado

DOCUMENTAÇÃO:
- Toda criação ou alteração de componente → atualizar o .md correspondente
- Toda criação ou alteração → registrar no changelog.md
- Toda criação ou alteração → atualizar storybook.html com o componente na seção correta
- Nada é feito sem documentação — sem exceções

NOVOS ASSETS:
- Depositar em Novos-arquivos/
- IA processa e redireciona ao local correto
- Documentação atualizada automaticamente
```

---

## 9. Design System — CSS custom properties

Todas as decisões visuais vivem em um único arquivo `design-tokens.css`:

```css
:root {

    /* =========================================
       BRAND
    ========================================= */
    --brand-100: #FDE2D1;
    --brand-200: #FBC5A4;
    --brand-300: #F3A574;
    --brand-400: #E78A51;
    --brand-500: #F56B15;
    --brand-600: #B95415;
    --brand-700: #9B440E;
    --brand-800: #7D3509;
    --brand-900: #672B05;

    /* =========================================
       NEUTRAL
    ========================================= */
    --neutral-100: #F2F5F9;
    --neutral-200: #E3E8EF;
    --neutral-300: #CDD5E0;
    --neutral-400: #97A3B6;
    --neutral-500: #687489;
    --neutral-600: #4A5567;
    --neutral-700: #374153;
    --neutral-800: #1A1D22;
    --neutral-900: #0D0E11;

    /* =========================================
       STATUS
    ========================================= */
    --error-100: #FCE6D2; --error-200: #FAC7A7; --error-300: #F09E78;
    --error-400: #E17655; --error-500: #CE3F23; --error-600: #B12619;
    --error-700: #941211; --error-800: #770B12; --error-900: #620613;

    --success-100: #E0FDD6; --success-200: #BCFBAE; --success-300: #8DF384;
    --success-400: #63E764; --success-500: #34D844; --success-600: #26B941;
    --success-700: #1A9B3E; --success-800: #107D38; --success-900: #096735;

    --warning-100: #FDF5CA; --warning-200: #FBE896; --warning-300: #F4DE61;
    --warning-400: #E9CF3A; --warning-500: #EBCA0E; --warning-600: #BCA000;
    --warning-700: #9D8500; --warning-800: #7F6C00; --warning-900: #695900;

    /* =========================================
       SEMANTIC COLORS
    ========================================= */
    --color-primary:    var(--brand-500);    /* #F56B15 */
    --color-secondary:  var(--neutral-900);  /* #0D0E11 */
    --color-background: var(--color-secondary);
    --color-text:       var(--neutral-100);  /* #F2F5F9 */

    /* =========================================
       TYPOGRAPHY
    ========================================= */
    --font-heading: 'Hanken Grotesk', sans-serif;
    --font-body:    'Inter', sans-serif;

    --text-xxl-size:    4.5rem;    /* 72px  */ --text-xxl-height:    6.75rem;  --text-xxl-weight:    700;
    --text-xl-size:     3.5rem;    /* 56px  */ --text-xl-height:     5.25rem;  --text-xl-weight:     700;
    --text-lg-size:     3rem;      /* 48px  */ --text-lg-height:     3.625rem; --text-lg-weight:     700;
    --text-md-lg-size:  2.5rem;    /* 40px  */ --text-md-lg-height:  3.125rem; --text-md-lg-weight:  600;
    --text-md-size:     2rem;      /* 32px  */ --text-md-height:     2.5rem;   --text-md-weight:     600;
    --text-md-sm-size:  1.5rem;    /* 24px  */ --text-md-sm-height:  2.375rem; --text-md-sm-weight:  600;
    --text-sm-size:     1.25rem;   /* 20px  */ --text-sm-height:     1.875rem; --text-sm-weight:     400;
    --text-xs-size:     1rem;      /* 16px  */ --text-xs-height:     1.5rem;   --text-xs-weight:     400;
    --text-xxs-size:    0.75rem;   /* 12px  */ --text-xxs-height:    1.25rem;  --text-xxs-weight:    400;

    /* =========================================
       LAYOUT & SPACING
    ========================================= */
    --container-max:  80rem;   /* 1280px */
    --height-header:  4.5rem;  /* 72px   */

    --space-100: 0.25rem;  /* 4px  */
    --space-200: 0.5rem;   /* 8px  */
    --space-300: 1rem;     /* 16px */
    --space-400: 1.5rem;   /* 24px */
    --space-500: 2rem;     /* 32px */
    --space-600: 2.5rem;   /* 40px */
    --space-700: 3rem;     /* 48px */
    --space-800: 3.5rem;   /* 56px */
    --space-900: 4rem;     /* 64px */

    /* =========================================
       BORDERS & RADIUS
    ========================================= */
    --border-radius-100: 0.25rem;  /* 4px  */
    --border-radius-200: 0.5rem;   /* 8px  */
    --border-radius-300: 1rem;     /* 16px */
    --border-radius-400: 1.5rem;   /* 24px */
    --border-radius-500: 2rem;     /* 32px */
    --border-radius-600: 2.5rem;   /* 40px */
    --border-radius-700: 3rem;     /* 48px */
    --border-radius-800: 3.5rem;   /* 56px */
    --border-radius-900: 4rem;     /* 64px */

    /* =========================================
       ICONS
    ========================================= */
    --icon-xs: 1rem;    /* 16px */
    --icon-sm: 1.25rem; /* 20px */
    --icon-md: 1.5rem;  /* 24px */
    --icon-lg: 2rem;    /* 32px */
    --icon-xl: 2.5rem;  /* 40px */

    /* =========================================
       BREAKPOINTS (referência)
    ========================================= */
    --bp-mobile:  375px;
    --bp-tablet:  768px;
    --bp-desktop: 1280px;
}
```

---

## 10. Responsividade

- Abordagem: **layout fluido com `clamp()`** — sem breakpoints rígidos
- Figma usa min/max width nos frames — o layout flui continuamente entre os extremos
- Export de 2 frames por componente: 375px (mínimo) e 1280px (máximo)
- A IA interpreta os dois extremos e gera CSS fluido com `clamp()`

```css
/* Exemplo de saída gerada pela IA */
.card-anime__title {
    font-size: clamp(var(--text-sm), 2.5vw, var(--text-xl));
    padding: clamp(var(--space-sm), 3vw, var(--space-lg));
}

.grid-animes {
    grid-template-columns: repeat(
        auto-fill,
        minmax(clamp(200px, 30vw, 300px), 1fr)
    );
}
```

### Documento de regras de responsividade (entregue à IA em todo prompt)

```
Breakpoints: min 375px, max 1280px — layout fluido com clamp()
Abordagem: mobile-first obrigatório

Grid:
- Mobile (375px): 1 coluna
- Desktop (1280px): 3 ou 4 colunas
- Meio: fluido com auto-fill + minmax

Tipografia:
- Escala fluida entre --text-sm (mobile) e --text-2xl (desktop)
- Sempre clamp() com custom properties nos extremos

Espaçamentos:
- Escala fluida entre --space-sm (mobile) e --space-xl (desktop)

Componentes que mudam de layout:
- Menu: hamburguer no mobile, horizontal no desktop
- Card de anime: imagem em cima no mobile, lado a lado no desktop
- Seção "Veja também": scroll horizontal no mobile, grid no desktop
- Carrossel de afiliados: 1 item no mobile, 3 no desktop
```

---

## 11. SEO — estratégia completa

> **SEO tem importância elevada. Tudo onde é possível aplicar SEO deve conter o SEO estratégico, SEMPRE.**

### Fundação técnica (antes de publicar qualquer post)
- Sitemap XML configurado e enviado ao Search Console
- Google Analytics 4 + Search Console integrados
- Schema markup em todos os posts: `Article`, `FAQPage`, `BreadcrumbList`
- URLs amigáveis: `/anime/nome-do-anime/` e `/episodio/anime-ep-numero/`
- Meta title e description únicos por post
- H1 único por página — hierarquia semântica refletindo hierarquia visual
- `rel="sponsored"` em todos os links de afiliado

### SEO on-page (por post)
- Keyword principal no title, H1, primeira linha e slug
- Keywords secundárias distribuídas nos H2s
- Alt text otimizado em todas as imagens (obrigatório em todos os átomos de imagem)
- Mínimo 2 links internos por post (mesmo cluster)
- Âncoras descritivas: nunca "clique aqui", sempre a keyword do post de destino
- Atualização mensal dos posts mais antigos

### SEO nos componentes (regra de desenvolvimento)
- `atoms/imagem-capa.php` — sempre recebe `alt` como parâmetro obrigatório
- `atoms/breadcrumb.php` — schema `BreadcrumbList` embutido
- `atoms/icone.php` — sempre com `aria-label`
- `organisms/header.php` — `<header role="banner">` com nav semântico
- `organisms/footer.php` — links com texto descritivo, sem "clique aqui"
- `molecules/card-anime.php` — título em `<h2>` ou `<h3>` conforme hierarquia da página

### Estrutura de clusters

```
[Pillar] Guia completo de One Piece
    ├── Calendário de episódios 2025
    ├── Resumo episódio 1100
    ├── Resumo episódio 1101
    ├── Ordem certa para assistir One Piece
    ├── Onde assistir One Piece no Brasil
    └── One Piece vai ter temporada X?
```

### Keywords prioritárias (long-tail)
- "quantos episódios tem [anime]"
- "quando sai o episódio X de [anime]"
- "[anime] vai ter temporada X?"
- "ordem certa para assistir [franquia]"
- "onde assistir [anime] no Brasil"
- "calendário de animes [temporada] [ano]"

---

## 12. Fluxo de desenvolvimento

```
Figma (frames com min/max width)
    → Export de tokens → design-tokens.css
    → 2 prints por componente (375px e 1280px)
    → Documento de regras de responsividade
    → Tudo junto no prompt da IA
    → IA gera: átomo / molécula / organismo
        → arquivo .php (template part)
        → arquivo .css (com clamp() e custom properties)
        → arquivo .md (documentação)
        → entrada no changelog.md
        → storybook.html atualizado com o novo componente
    → Designer ajusta detalhes finos no CSS
    → Nunca editar PHP para mudanças visuais
    → Assets externos entram via Novos-arquivos/
        → IA processa e redireciona
        → documentação atualizada
```

---

## 13. Plugins essenciais

| Plugin | Função |
|---|---|
| Rank Math | SEO on-page e schema markup |
| WP Rocket | Cache e performance |
| ACF (Advanced Custom Fields) | Campos customizados por CPT |
| Cloudflare | CDN gratuito |

---

## 14. Custom Post Types (CPT)

| CPT | Campos principais |
|---|---|
| Anime | Título, sinopse, nota, studio, ano, gêneros, status |
| Episódio | Número, título, data de lançamento, resumo, anime pai |
| Temporada | Ano, período (inverno/primavera/verão/outono), animes |
| Review | Nota, prós, contras, veredicto, anime relacionado |

---

## 15. Performance obrigatória

- Core Web Vitals ≥ 90 (LCP < 2,5s)
- Mobile-first em todo o desenvolvimento
- Imagens em WebP com compressão
- CSS crítico inline
- Sem jQuery no frontend
- Lazy load nativo em imagens e carrosseis de afiliados
- Carrosseis com lazy load (scripts externos não bloqueiam renderização)

---

## 16. Produção de conteúdo

### Volume
- **20+ posts/semana** desde o início
- Meta: 80+ posts no mês 1, ~300 posts aos 3 meses, 600+ posts aos 6 meses

### Fluxo com IA (Groq)
1. Pesquisa de keywords 1x por semana (Ubersuggest / Semrush Free)
2. Brief estruturado com keyword principal, secundárias, H2s obrigatórios e tamanho alvo
3. Geração via Groq (llama3-70b) com prompt-template por tipo de post
4. Revisão humana: correção de dados factuais, adição de opinião, inserção de links internos
5. SEO on-page via Rank Math
6. Publicação agendada — distribuída ao longo do dia

> **Regra:** nunca publicar conteúdo 100% IA sem revisão. O diferencial humano é o que protege contra o Google Helpful Content Update.

---

## 17. Automação de publicação

### Stack

| Camada | Tecnologia |
|---|---|
| Linguagem | Python 3.10+ |
| Agendamento | `schedule` lib ou crontab local |
| Fonte de dados | Jikan API (MyAnimeList) + AniList GraphQL |
| Geração de texto | Groq API — llama3-70b-8192 |
| Publicação | WordPress REST API |
| Credenciais | Application Passwords (WP) + python-dotenv |

### Pipeline
1. **Coleta** — Jikan/AniList retorna dados de episódios, datas e sinopses
2. **Verificação de duplicatas** — consulta WP via REST antes de gerar qualquer conteúdo
3. **Geração** — Groq transforma dados em post estruturado via prompt-template
4. **Formatação** — script monta HTML com meta SEO, slug, categorias, tags e links internos
5. **Publicação** — cria post novo ou atualiza existente (ex: calendários)
6. **Log** — resultado registrado em arquivo local

### Frequência

| Rotina | Frequência |
|---|---|
| Novos episódios | A cada 6h |
| Atualização de calendários | 1x por dia |
| Posts evergreen / listas | 1x por semana |

---

## 18. Integração Jikan API (MyAnimeList)

### Duas camadas de integração

**Camada 1: Automação Python**
```
Jikan API → Python busca dados → Groq gera texto → publica no WP
```

**Camada 2: Integração dinâmica no frontend**
```
Usuário abre página do anime →
JavaScript busca Jikan API em tempo real →
Dados sempre atualizados (nota, status, eps restantes)
```

### Estratégia de importação em ondas

```
Onda 1 (semana 1):   Top 500 animes mais populares do MAL
Onda 2 (semanas 2–4): Animes airing + 2020–2025 (~2.000 páginas)
Onda 3 (mês 2–3):    Resto do catálogo relevante — nota > 7, members > 10k (~5.000 páginas)
Sincronização contínua: cron job diário — novos animes + atualização de nota/status
```

---

## 19. Hospedagem

### Hostinger (até 300k visitas/mês)

| Fase | Plano | Custo/mês |
|---|---|---|
| Mês 1–6 | Business | R$ 30–40 |
| Mês 6–18 | Cloud Startup | R$ 60–80 |
| Mês 18–300k | Cloud Pro | R$ 100–140 |

### DigitalOcean (pós 300k visitas/mês)

| Plano | Specs | Custo/mês |
|---|---|---|
| Basic | 2 vCPU, 4GB RAM | U$ 24 |
| Premium | 2 vCPU+ dedicado | U$ 40+ |

### Segurança
- URL do painel alterada
- 2FA ativo
- Cloudflare como WAF
- Backup automático diário — UpdraftPlus → Google Drive
- Permissões: 644 arquivos, 755 pastas
- `wp-config.php` fora do diretório público

---

## 20. Monetização

### Evolução

| Marco | Ação |
|---|---|
| Aprovação Adsense | 30+ posts publicados |
| 50k sessões/mês | Migrar para Mediavine (RPM 2x) |
| 150k+ visitas/mês | Negociar patrocínios diretos |
| Autoridade consolidada | Newsletter + produtos digitais |

---

## 21. Riscos e mitigações

| Risco | Mitigação |
|---|---|
| Google Sandbox (meses 1–3) | Continuar produzindo sem medir resultado antes do mês 3 |
| Helpful Content Update | Revisão humana obrigatória em todo conteúdo gerado por IA |
| Adblock alto (~50%) | Afiliados como receita paralela + banner educativo |
| Copyright de imagens | Apenas imagens licenciadas ou arte própria — WebP + compressão |
| Links de afiliado sem tag | `rel="sponsored"` obrigatório em 100% dos links |
| Core Web Vitals ruim | Lazy load em carrosseis, sem jQuery, CSS crítico inline |
| Componente sem documentação | `.windsurfrules` bloqueia criação sem .md correspondente |

---

## 22. Expansão futura (pós 300k visitas)

- Subnichos com RPM maior: games, tech geek, HQ
- Canal no YouTube integrado ao blog
- Newsletter própria (ConvertKit ou Brevo)
- E-books e guias digitais
- Migração para Mediavine ou Raptive
- Patrocínios diretos com lojas de merch e plataformas de streaming
