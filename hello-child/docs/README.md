# Documentação do Projeto — modomaratona.com

Bem-vindo ao guia oficial de desenvolvimento do tema filho `hello-child` para o **modomaratona.com**. Este projeto utiliza os princípios de **Atomic Design**, **Responsividade Fluida (clamp)**, **CSS baseada 100% em Tokens de Design** e **SEO Estratégico**.

---

## 1. Arquitetura de Componentes (Atomic Design)

Toda a interface do usuário (UI) é organizada na seguinte estrutura de diretórios:

- **`/atoms`** (Átomos): Elementos indivisíveis e puros de interface (ex: botões, badges, tags, ícones, imagens com lazy-load).
- **`/molecules`** (Moléculas): Combinação de dois ou mais átomos que formam uma unidade funcional (ex: card de anime, formulário de busca).
- **`/organisms`** (Organismos): Seções completas e complexas formadas por átomos e moléculas (ex: header, footer, grid de posts, sidebar).
- **`/templates`** (Templates): Estruturas ou layouts de páginas por Custom Post Type (CPT), prontos para receber conteúdo real via WordPress (ex: `single-anime.php`).

### Regra de Ouro do Atomic Design:
> **Nenhum componente deve ser criado de forma avulsa ou conter estilos "hardcoded".** Todos os átomos, moléculas e organismos devem herdar e usar estritamente as propriedades declaradas em `hello-child/design-tokens.css`.

---

## 2. Padrão de Nomenclatura CSS (BEM)

Adotamos a metodologia **BEM (Block, Element, Modifier)** para garantir isolamento e modularidade.

- **Bloco (`.bloco`)**: Entidade independente e significativa por si só.
  - Exemplo: `.btn-primary`, `.card-anime`
- **Elemento (`.bloco__elemento`)**: Parte de um bloco que não tem significado independente e está semanticamente vinculada ao seu bloco.
  - Exemplo: `.card-anime__title`, `.card-anime__badge`
- **Modificador (`.bloco--modificador` ou `.bloco__elemento--modificador`)**: Flag em um bloco ou elemento para alterar sua aparência ou comportamento.
  - Exemplo: `.btn-primary--large`, `.card-anime__title--featured`

---

## 3. Diretrizes de Responsividade (Layout Fluido com `clamp()`)

Não utilizamos breakpoints rígidos e media queries excessivas para mudança de tamanho de textos ou espaçamentos. O layout é **fluido**, projetado para se adaptar continuamente entre os limites de tela mínimo (**375px**) e máximo (**1280px**).

### Exemplo de uso de `clamp()`:
```css
.card-anime__title {
    /* clamp(minimo, valor-fluido, maximo) */
    font-size: clamp(var(--text-xs-size), 2.5vw, var(--text-sm-size));
    margin-bottom: clamp(var(--space-200), 1.5vw, var(--space-400));
}
```

---

## 4. Práticas de SEO Obrigatórias nos Componentes

Cada componente deve nascer otimizado para motores de busca:
1. **Marcação Semântica:** Utilização correta de `<header>`, `<footer>`, `<nav>`, `<article>`, `<section>`, `<h1>` a `<h6>`.
2. **Acessibilidade:** Elementos interativos sem texto visível (como ícones de busca ou redes sociais) devem conter o atributo `aria-label`.
3. **Imagens Otimizadas:** O átomo `atoms/imagem-capa.php` exige o parâmetro `alt` como campo obrigatório. Todas as capas devem conter alt text descritivo e relevante.
4. **Links de Afiliados:** 100% dos botões e links que apontam para programas de afiliados (Amazon, Shopee, etc.) devem obrigatoriamente conter o atributo `rel="sponsored"`.

---

## 5. Diretriz de Integridade do Design (Muito Importante)

Para evitar quebras visuais graves no tema WordPress, é terminantemente proibido realizar refatorações estruturais ou remoções de classes CSS de componentes estáveis homologados no Storybook. 

Antes de realizar qualquer modificação em arquivos PHP ou CSS de componentes, leia atentamente a [Diretriz de Integridade do Design e Estrutura Atômica](file:///c:/Users/P.%20Augusto/Documents/Geek%20ao%20Cubo/hello-child/docs/preserve-design-structure.md). Ela define as regras absolutas para manter o acoplamento estrito entre o HTML e as folhas de estilo dos componentes sem quebras.

