# Grupo de Input / Campo de Formulário (form-field)

**Tipo:** Molécula  
**Arquivo:** `molecules/form-field.php`  
**CSS:** `molecules/form-field.css`  
**Criado em:** 2026-05-23  
**Última atualização:** 2026-05-23  

## Descrição
A molécula de Grupo de Input (`form-field`) é um bloco composto reutilizável que organiza as tags de formulário de forma semântica e profissional. Ela amarra e renderiza automaticamente três átomos: o rótulo superior (`input-label`), o campo de input real (`input-busca`) e a descrição/erro inferior (`input-helper`).

## Átomos utilizados
- `atoms/input-label.php`
- `atoms/input-busca.php`
- `atoms/input-helper.php`

## Variáveis CSS utilizadas
- `--space-400` (espaço padrão inferior de 24px do formulário)
- `--error-500` / `--error-400` (cores de alerta para o input)

## Parâmetros PHP
| Parâmetro | Tipo | Padrão | Descrição |
|---|---|---|---|
| `$args['name']` | string | `''` | O nome (`name`) do input do formulário |
| `$args['id']` | string | *Slug de name* | O identificador único (`id`) associado ao label e input |
| `$args['label']` | string | `''` | Texto do rótulo superior. Se nulo, omite a exibição |
| `$args['type']` | string | `'text'` | Tipo do input: `'text'`, `'search'`, `'email'`, `'tel'`, ou `'select'` |
| `$args['placeholder']` | string | `''` | Texto interno do placeholder |
| `$args['value']` | string | `''` | Valor pré-preenchido do campo |
| `$args['required']` | boolean | `false` | Se verdadeiro, torna o campo obrigatório no HTML |
| `$args['options']` | array | `[]` | Array associativo de chaves e valores exclusivo para dropdowns (`'select'`) |
| `$args['helper_text']` | string | `''` | Texto explicativo ou mensagem de erro |
| `$args['is_error']` | boolean | `false` | Se verdadeiro, ativa a formatação de erro visual e semântica |
| `$args['class']` | string | `''` | Classes CSS adicionais para o container wrapper |

## SEO e Acessibilidade
- Vinculação perfeita entre o Rótulo e o Input via `for` e `id`.
- Se `$args['is_error']` for verdadeiro, injeta atributos `role="alert"` no átomo auxiliar para leitura imediata de leitores de tela.

## Responsividade
- O componente flui a `width: 100%`, ajustando-se a grids horizontais no desktop ou verticalizados no mobile.

## Exemplo de uso
```php
<?php 
// 1. Campo de E-mail de Contato
mm_render_component('molecules', 'form-field', [
    'name'        => 'email_contato',
    'label'       => 'Seu melhor e-mail',
    'type'        => 'email',
    'placeholder' => 'exemplo@dominio.com',
    'required'    => true,
    'helper_text' => 'Nunca compartilharemos seus dados.'
]); 

// 2. Select de Gênero de Anime com Erro de Validação
mm_render_component('molecules', 'form-field', [
    'name'        => 'genero_filtro',
    'label'       => 'Selecione o Gênero',
    'type'        => 'select',
    'placeholder' => 'Escolha uma opção...',
    'options'     => [
        'shonen'  => 'Shonen',
        'seinen'  => 'Seinen'
    ],
    'is_error'    => true,
    'helper_text' => 'Por favor, selecione um gênero válido.'
]);
?>
```
