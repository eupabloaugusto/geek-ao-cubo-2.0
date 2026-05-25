# Biblioteca de Inputs (input-busca)

**Tipo:** Átomo  
**Arquivo:** `atoms/input-busca.php`  
**CSS:** `atoms/input-busca.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
A Biblioteca de Inputs (`input-busca`) é um componente atômico versátil projetado para cobrir todas as necessidades de captação de dados do portal. Ele atende a três modelos distintos em um único arquivo: barra de busca com ícone de lupa, inputs de texto convencionais (comentários/contato) e seletores customizados (select) para filtros.

## Variáveis CSS utilizadas
- `--neutral-800` (cor de fundo dos campos)
- `--neutral-900` (cor de fundo ativa ao focar)
- `--color-border` (borda neutra fina)
- `--color-primary` (borda ativa no foco com brilho neon)
- `--icon-sm` (tamanho de 20px para os ícones)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['type']` | string | `'search'` | Tipo do input: `'search'` (com ícone), `'text'`, `'email'`, `'tel'` ou `'select'` |
| `$args['placeholder']` | string | `''` | Texto de instrução interna (placeholder) |
| `$args['name']` | string | `''` | Atributo `name` do formulário |
| `$args['id']` | string | *Slug de name* | Atributo `id` único do campo |
| `$args['value']` | string | `''` | Valor preenchido padrão |
| `$args['required']` | boolean | `false` | Se verdadeiro, torna o preenchimento obrigatório |
| `$args['options']` | array | `[]` | Array associativo de chaves e valores exclusivo para o tipo `'select'` |
| `$args['class']` | string | `''` | Classes CSS adicionais para o container wrapper |

## SEO aplicado
- Elementos `<select>` e `<input>` semânticos com vínculos perfeitos via `id` e `name` para correta indexação.
- Ocultamento de elementos meramente ilustrativos (como as setas e a lupa) usando `aria-hidden="true"`, prevenindo ruído para leitores de tela.
- Classes CSS BEM isoladas, evitando vazamento ou conflitos globais de estilo.

## Responsividade
- O container `.input-wrapper` se expande para `width: 100%`, adequando-se ao grid de formulários no desktop e telas de toque no mobile.
- Utilização de `appearance: none` nos seletores para neutralizar as inconsistências de design dos browsers nativos em iOS e Android, forçando a seta customizada uniforme.

## Exemplo de uso
```php
<?php 
// 1. Barra de Busca de Animes (Modelo Search)
mm_render_component('atoms', 'input-busca', [
    'type'        => 'search',
    'placeholder' => 'Buscar animes ou episódios...',
    'name'        => 's'
]); 

// 2. Input de Texto Padrão (Modelo Contato)
mm_render_component('atoms', 'input-busca', [
    'type'        => 'text',
    'placeholder' => 'Seu nome completo',
    'name'        => 'nome_contato',
    'required'    => true
]); 

// 3. Seletor de Filtros (Modelo Select)
mm_render_component('atoms', 'input-busca', [
    'type'        => 'select',
    'placeholder' => 'Filtrar por Gênero',
    'name'        => 'genero_filtro',
    'options'     => [
        'acao'    => 'Ação',
        'romance' => 'Romance',
        'shonen'  => 'Shonen',
        'isekai'  => 'Isekai'
    ]
]);
?>
```
