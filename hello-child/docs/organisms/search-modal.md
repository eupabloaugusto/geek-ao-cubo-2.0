# Modal de Busca (search-modal)

**Tipo:** Organismo  
**Arquivo:** `organisms/search-modal.php`  
**CSS:** `organisms/search-modal.css`  
**JS:** `organisms/search-modal.js`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-26  

## Descrição
O Modal de Busca é um painel em tela cheia com efeito premium de desfoque de fundo (`backdrop-filter: blur(16px)`) e opacidade. Ele é projetado para acessibilidade WCAG e ativado por qualquer elemento que tenha a classe `.js-open-search-modal` no cabeçalho ou no portal. Ele hospeda a molécula padrão de busca (`form-busca`) e exibe sugestões de tags clicáveis para o usuário.

## Componentes Utilizados
- **Átomos:**
  - `atoms/logo.php` (logotipo oficial embutido)
  - `atoms/input-busca.php` (através de `form-busca`)
  - `atoms/btn-primary.php` (através de `form-busca`)
- **Moléculas:**
  - `molecules/form-busca.php` (formulário padrão de busca)

## Variáveis CSS Utilizadas
- `--color-primary`
- `--neutral-900`
- `--neutral-400`
- `--space-600`
- `--space-800`

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['placeholder']` | string | `'Digite sua pesquisa...'` | Texto de dica dentro do campo de busca |
| `$args['sugestoes_titulo']` | string | `'Sugestões de Busca'` | Título exibido acima das tags rápidas |
| `$args['sugestoes_tags']` | array | (tags padrões) | Lista de links rápidos sugeridos |

## SEO e Acessibilidade (A11y)
- **Trava de Foco (Focus Trap):** Implementada via Javascript para assegurar que a navegação por teclado (`Tab` e `Shift+Tab`) não escape do modal.
- **Teclas Rápidas:** Suporte ao fechamento automático do modal pressionando a tecla `Escape`.
- **Foco Automático:** Foca automaticamente no campo de pesquisa (`.input-field`) em 100ms após o modal abrir.
- **Delegação de Eventos:** Escuta global no `document` para suportar cabeçalhos renderizados dinamicamente pelo Elementor.
- **Atributos semânticos:** `role="dialog"`, `aria-modal="true"`, `aria-hidden="true"`, `tabindex="-1"`.

## Exemplo de uso
No WordPress, o modal é injetado automaticamente de forma global no rodapé da página através de um hook em `wp_footer` no arquivo `functions.php`:
```php
mm_render_component( 'organisms', 'search-modal' );
```
