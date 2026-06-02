# Diretriz de Preservação de Estrutura Visual e Design

Este manual estabelece regras técnicas rígidas de conduta para engenheiros e inteligências artificiais no desenvolvimento e manutenção do tema **Geek ao Cubo**. O objetivo é blindar a assinatura de classes HTML BEM e tags homologadas no **Storybook** contra alterações cegas, mantendo a integridade total do design system.

---

## 🚨 1. Regra de Acoplamento Estrito (PHP ↔ CSS)

No nosso ecossistema de **Atomic Design**, cada componente possui uma relação indissociável de **1:1** entre o arquivo de marcação PHP e o arquivo de estilos CSS:

- `atoms/meu-componente.php` ↔ `atoms/meu-componente.css`
- `molecules/outro.php` ↔ `molecules/outro.css`

> [!WARNING]
> **NUNCA altere classes, IDs ou a hierarquia do DOM em um arquivo PHP sem ajustar simultaneamente e testar o respectivo arquivo CSS.**
> Alterações isoladas na assinatura de classes quebram instantaneamente o visual renderizado e a conformidade no Storybook.

---

## 🏷️ 2. Padrão de Nomenclatura BEM (Block, Element, Modifier)

Adotamos a especificação estrita de classes BEM para evitar colisões globais de estilo e garantir legibilidade semântica:

```css
/* Block */
.card-noticia { }

/* Element (dois sublinhados) */
.card-noticia__title { }
.card-noticia__meta { }

/* Modifier (dois hífens) */
.card-noticia--grid { }
.card-noticia--list { }
.card-noticia--hero { }
```

### Regras de Ouro:
- **Zero Classes Utilitárias:** Não use classes utilitárias no markup (ex.: `flex`, `pt-4`, `text-center`) no estilo Tailwind. Toda a diagramação deve vir das regras CSS mapeadas para classes BEM dedicadas.
- **Zero Estilos Inline:** É expressamente proibido o uso do atributo `style=""` no HTML para formatação visual secundária. Todo o design é controlado via CSS.

---

## 🎨 3. Uso Obrigatório de Design Tokens

Nenhum valor fixo (hardcoded) de cores, fontes, tamanhos, espaçamentos ou bordas pode ser introduzido diretamente nos arquivos CSS dos componentes.

- **Tokens de Cores:** Use apenas as variáveis semânticas declaradas em `design-tokens.css` (ex.: `var(--color-primary)`, `var(--neutral-800)`).
- **Escalas Fluidas:** Utilize `rem` em vez de `px` para tamanhos e margens, e aplique layouts responsivos baseados em `clamp()` para transições fluidas.

---

## 📐 4. Blindagem do Storybook (`storybook.html`)

O arquivo `storybook.html` na raiz do tema é a nossa **vitrine viva e homologada** de componentes.

- Qualquer alteração na marcação estrutural de um componente (ex.: `atoms/btn-primary.php`) deve ser refletida na sua respectiva visualização no `storybook.html`.
- O Storybook funciona como teste de regressão estático: se a alteração quebrar o visual na vitrine, a implementação **não está pronta para commit**.

---

## 📝 5. Changelog e Documentação Contínua

Cada modificação em componentes ou templates requer:
1. Atualização do respectivo manual `.md` sob a pasta `docs/`.
2. Registro detalhado da alteração com a data no arquivo `docs/changelog.md`.
