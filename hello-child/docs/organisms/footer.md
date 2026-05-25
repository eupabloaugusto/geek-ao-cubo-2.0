# Rodapé Semântico (footer)

**Tipo:** Organismo  
**Arquivo:** `organisms/footer.php`  
**CSS:** `organisms/footer.css`  
**Criado em:** 2026-05-24  
**Última atualização:** 2026-05-24  

## Descrição
O rodapé semântico (`footer`) é o organismo responsável pelo fechamento estrutural do layout das páginas. Consolida a identidade da marca, fornece navegação institucional/legal fluida e apresenta créditos/copyright de forma limpa e harmônica, aderindo aos princípios de design de tokens e acessibilidade.

## Variáveis CSS utilizadas
O visual é 100% controlado pelas variáveis do `design-tokens.css`:
- `--color-secondary` (cor de fundo deep dark do rodapé)
- `--color-border` (cor para a linha divisora superior e interna)
- `--color-text` (fallback de cor de texto claro para a marca)
- `--color-primary` (destaque para links em hover e linha decorativa)
- `--neutral-300` (cor secundária dos links e textos de copyright)
- `--neutral-400` (cor da descrição institucional da marca)
- `--neutral-500` (cor para créditos menores e direitos autorais)
- `--font-heading` (família tipográfica para logo em texto se SVG ausente)
- `--font-body` (família tipográfica padrão de leitura)
- `--text-md-sm-size` / `--text-md-sm-weight` (tamanho e peso do logo em texto)
- `--text-xxs-size` / `--text-xxs-height` / `--text-xxs-weight` (escala tipográfica dos links, descrições e créditos)
- `--container-max` (limite físico de largura do conteúdo do rodapé)
- `--space-200` a `--space-800` (espaçamentos internos e margens)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['logo_text']` | string | `get_bloginfo('name')` | Texto do logotipo exibido se o arquivo SVG estiver ausente. |
| `$args['copyright']` | string | Dinâmico com ano corrente | Texto de copyright para exibição na linha inferior. |
| `$args['footer_menu']` | array | Array estático de fallback | Lista de links do menu, contendo sub-arrays com chaves `label` e `url`. |

## SEO aplicado
- **Marcação Semântica:** Utilização da tag `<footer role="contentinfo">` para indicação inequívoca aos motores de busca da seção de rodapé da página.
- **Navegação Semântica:** Utilização da tag `<nav aria-label="Navegação de Rodapé">` para encapsular a lista de links.
- **Acessibilidade de Links:** Textos de links 100% descritivos (ex: "Políticas de Privacidade", "Catálogo de Animes"), evitando expressões vagas e genéricas que comprometam a indexação ou leitores de tela (ex: "clique aqui", "saiba mais").
- **Aria-label Contextual:** Link da marca/logotipo com `aria-label` estendido ("Voltar para a Página Inicial - Geek ao Cubo") para fácil orientação do usuário.

## Responsividade
- **Mobile-first:** Em telas menores (mobile/tablet), a disposição do rodapé é empilhada verticalmente com alinhamento central à esquerda, maximizando o espaço de leitura e facilitando o clique nos links que se distribuem em uma grade (grid) de duas colunas.
- **Desktop (≥ 768px):** A estrutura se expande para um formato horizontal bilíngue: à esquerda, o bloco de branding da marca; à direita, a lista de links empilhada em formato vertical alinhada à direita. A linha inferior de créditos divide-se nas extremidades do container.

## Exemplo de uso
```php
<?php 
// Renderiza o rodapé padrão com links institucionais e legais
mm_render_component( 'organisms', 'footer' ); 
?>
```
