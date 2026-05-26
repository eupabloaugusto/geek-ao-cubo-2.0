# Diretriz de Integridade do Design e Estrutura Atômica
**Código de Conduta para Proteção de Interface e Portabilidade de Componentes**

> [!IMPORTANT]
> **ESTA DIRETRIZ É UMA REGRA ABSOLUTA DE DESENVOLVIMENTO.**
> Qualquer alteração nos componentes do tema filho (`hello-child`) realizada por desenvolvedores humanos ou assistentes de Inteligência Artificial (IA) **DEVE** seguir rigorosamente as regras estabelecidas neste documento. O descumprimento destas regras quebra a fidelidade visual e a interface premium do portal Geek ao Cubo.

---

## 1. O Problema da "Refatoração Cega" (Anti-Pattern)

No fluxo de desenvolvimento de temas WordPress baseados em **Atomic Design**, os estilos visuais (`.css`) são projetados e acoplados com **fidelidade cirúrgica** às marcações HTML (`.php` ou `storybook.html`) de cada componente (átomo, molécula ou organismo).

### O Erro Comum:
Tentar "refatorar" uma molécula ou organismo pronto para usar outros sub-componentes (ex: substituir um elemento inline por uma chamada de função `mm_render_component( 'atoms', ... )`) **sem preservar as classes CSS e tags originais**.

*   **O que acontece:** As classes de estilo associadas ao componente principal deixam de existir no HTML final.
*   **A consequência:** A folha de estilo do componente deixa de estilizar os elementos internos, quebrando totalmente a diagramação e resultando em áreas em branco, cores invisíveis, fontes desalinhadas e quebra de responsividade.

---

## 2. As Regras de Ouro da Integridade de UI

### Regra 1: Acoplamento Estrito (HTML ↔ CSS)
Toda estrutura de template part (`atoms/*.php`, `molecules/*.php`, `organisms/*.php`) está intrinsecamente ligada à sua respectiva folha de estilo (`atoms/*.css`, `molecules/*.css`, `organisms/*.css`). 
*   **NUNCA** altere a hierarquia de tags HTML, nomes de classes (BEM) ou atributos de um arquivo `.php` sem revisar e refatorar em perfeita paridade o arquivo `.css` correspondente.

### Regra 2: Preservação do Storybook
O arquivo [storybook.html](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/storybook.html) é a única fonte confiável da interface.
*   A marcação gerada pelos arquivos PHP do WordPress **DEVE** gerar uma estrutura de DOM (Document Object Model) e lista de classes CSS **100% idêntica** ao exemplo estático homologado na vitrine do Storybook.

### Regra 3: Composição Segura de Componentes
Se for necessário incluir um átomo ou molécula dentro de outro componente:
*   A classe CSS que estiliza o layout do componente pai **deve ser passada como argumento** (via `class`) para o sub-componente.
*   *Ou* o sub-componente deve ser envolvido por uma tag container que preserve a classe BEM original que o CSS do pai espera estilizar.

---

## 3. Guia de Portabilidade Segura (Exemplo Prático)

### ❌ Como NÃO Fazer (Quebra a Interface):
Substituir uma tag inline estilizada pelo CSS por uma chamada cega de componente atômico, eliminando as classes e a hierarquia que o arquivo CSS espera estilizar.

*No arquivo `card-noticia.css`:*
```css
.card-noticia__eyebrow {
    color: var(--color-primary);
    font-size: 0.6875rem;
    font-weight: 700;
}
```

*No arquivo `card-noticia.php` (Código Quebrado):*
```php
<!-- ATO 1: Cabeçalho (Badge original removido e trocado por componente genérico) -->
<div class="card-noticia__header">
    <?php 
    // Quebra o layout! O CSS do card-noticia espera estilizar '.card-noticia__eyebrow'
    // mas o badge-categoria injeta a classe '.badge-categoria', invalidando o estilo acima!
    mm_render_component( 'atoms', 'badge-categoria', [
        'categoria' => $categoria 
    ] ); 
    ?>
</div>
```

---

###  Como Fazer Corretamente (Preserva a Interface):
Garantir que a classe que o CSS espera estilizar seja mantida, seja aplicando-a diretamente no sub-componente ou mantendo a estrutura pura do design original.

#### Abordagem A (Manter a estrutura limpa e direta do design original — Recomendada):
```php
<!-- ATO 1: Cabeçalho (Preserva a marcação BEM exata que o CSS do card-noticia formata) -->
<div class="card-noticia__header">
    <span class="card-noticia__eyebrow">
        <?php echo $categoria; ?>
    </span>
    <h3 class="card-noticia__title">
        <?php echo $titulo; ?>
    </h3>
</div>
```

#### Abordagem B (Injetar a classe do pai no sub-componente):
```php
<!-- ATO 1: Cabeçalho (Passa a classe BEM do pai como parâmetro de estilização para o átomo) -->
<div class="card-noticia__header">
    <?php 
    mm_render_component( 'atoms', 'badge-categoria', [
        'categoria' => $categoria,
        'class'     => 'card-noticia__eyebrow' // Passa a classe BEM esperada pelo CSS do card
    ] ); 
    ?>
</div>
```

---

## 4. Instruções Específicas para Assistentes de IA (Coding Agents)

Se você é um agente de inteligência artificial codificando para o Geek ao Cubo, leia e memorize as seguintes instruções antes de realizar qualquer modificação:

1.  **Estude o Storybook Primeiro:** Antes de tocar em qualquer arquivo PHP de molécula ou organismo, abra o arquivo `storybook.html` e inspecione a estrutura de tags e classes exatas do componente que você está editando.
2.  **Não Tente Reinventar Componentes Estáveis:** Se um componente já existe, possui estilização própria e está funcional no Storybook, use-o exatamente como foi projetado. Não faça "refatorações de limpeza de código" baseadas em conceitos teóricos se elas alterarem a assinatura de classes HTML do componente.
3.  **Monitore Quebras Visuais:** Se após a alteração de um componente a página apresentar áreas em branco, falta de textos, desalinhamento de blocos ou perda de contraste, reverta a alteração estrutural do PHP imediatamente para a versão original estável. A estrutura HTML do Storybook é sagrada.
