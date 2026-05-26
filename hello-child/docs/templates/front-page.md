# Template: Página Inicial (Home)

**Tipo:** Template  
**Arquivo:** `front-page.php`  
**CSS:** `front-page.css`  
**Criado em:** 2026-05-26  
**Última atualização:** 2026-05-26  

## Descrição
O template de Página Inicial (Home) unifica as principais seções dinâmicas do portal Modo Maratona / Geek ao Cubo. Ele organiza em uma grade responsiva o carrossel de destaques nobres, a esteira horizontal de novos episódios em exibição, a grade com os artigos de notícias recentes e a barra lateral (sidebar) padrão.

## Organismos e Moléculas Utilizadas
- `organisms/header.php` — Cabeçalho global do portal
- `organisms/sidebar.php` — Barra lateral de busca, destaques de temporada e Adsense
- `organisms/secao-carrossel-destaque.php` *(Sprint 1, Task 1.2)*
- `organisms/secao-noticias-recentes.php` *(Sprint 1, Task 1.3)*
- `organisms/secao-novos-episodios.php` *(Sprint 1, Task 1.4)*
- `organisms/footer.php` — Rodapé global

## Variáveis CSS Utilizadas
- `--container-max` — Largura máxima da página (1280px)
- `--space-400`, `--space-500`, `--space-600`, `--space-800` — Escala de espaçamento
- `--neutral-800`, `--neutral-900` — Paleta de fundo escuro
- `--brand-500` — Cor de destaque laranja da marca

## Responsividade
- **Mobile (375px)**: Layout empilhado em uma única coluna vertical. O banner de destaque se ajusta proporcionalmente e a sidebar é empilhada no rodapé da página inicial.
- **Desktop (1280px)**: Grade de duas colunas (`2.2fr 1fr`) onde o conteúdo principal ocupa a esquerda e a sidebar ocupa a direita com margens e Proximity Spacing adequados.

## Exemplo de Estrutura HTML
```html
<div class="home-page">
    <div class="home-page__hero-section">
        <!-- Secao Carrossel Destaque -->
    </div>
    <div class="home-page__layout">
        <main class="home-page__main" id="main-content">
            <!-- Novos Episodios -->
            <!-- Noticias Recentes -->
        </main>
        <div class="home-page__sidebar">
            <!-- Sidebar Widget Stack -->
        </div>
    </div>
</div>
```
