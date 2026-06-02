# Sistema de Documentação — Geek ao Cubo

Bem-vindo ao sistema de documentação do tema **Geek ao Cubo v2.0**. Nosso tema adota o **Atomic Design** como metodologia estrutural e o **i18n** com suporte completo a traduções.

---

## 🧬 Estrutura do Atomic Design

1. **Átomos (`atoms/`):** Elementos mínimos e indivisíveis da interface (ex: botões, ícones, inputs).
2. **Moléculas (`molecules/`):** Combinações de dois ou mais átomos que formam uma unidade funcional reutilizável (ex: cards de anime, formulários de busca, fallbacks/placeholders).
3. **Organismos (`organisms/`):** Seções complexas da página que agrupam moléculas e átomos (ex: cabeçalho, rodapé, grades editoriais, sidebar).
4. **Templates (`templates/` ou CPT templates na raiz):** Estruturas de página independentes que orquestram a distribuição dos componentes.

---

## 🗺️ Organização da Documentação (`docs/`)

Cada componente desenvolvido ou modificado deve possuir seu respectivo arquivo descritivo em Markdown:

```
docs/
├── README.md               ← Este arquivo (visão geral)
├── changelog.md            ← Histórico de alterações do tema
├── atoms/                  ← Documentação técnica dos átomos
├── molecules/              ← Documentação técnica das moléculas
│   ├── home-placeholder-carousel.md
│   └── home-placeholder-episodes.md
└── organisms/              ← Documentação técnica dos organismos
```

---

## 🛠️ Convenções Importantes

- **Sem CSS global acoplado:** Cada átomo, molécula ou organismo possui seu respectivo arquivo `.css` e `.js` (quando interativo) ao lado do arquivo `.php`. Eles são enfileirados sob demanda pela função `mm_render_component()`.
- **Text Domain:** O text domain do tema é estritamente **`geek-ao-cubo`**. Todos os novos componentes e traduções devem utilizar apenas este text domain.
- **Variáveis CSS:** Nossos estilos são baseados estritamente em tokens de design declarados no arquivo `design-tokens.css`. Evite declarar cores estáticas e fontes diretamente nos componentes.
