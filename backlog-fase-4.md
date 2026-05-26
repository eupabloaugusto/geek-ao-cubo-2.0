# 🚀 Backlog de Desenvolvimento — Fase 4: Monetização de Elite, Newsletter, Expansão Geek (Mangás/Games) & Recursos de Comunidade

> **Status do Projeto:** A Fase 1 (Visual Estático), Fase 2 (Conectividade CMS & IA Local) e Sprints 1 e 2 da Fase 3 (Carga de Animes Jikan MAL API & Agendador Orquestrador Python) estão 100% concluídas e operando localmente no **LocalWP** e versionadas via **GitHub**. As tarefas de infraestrutura física, DNS, SSL e segurança em nuvem (Sprints 3 e 4 da Fase 3) permanecem em aberto e serão executadas no momento da contratação do plano Hostinger Business após a Copa do Mundo.
> 
> **Objetivo da Fase 4 (Mês 6–12):** Maximizar a monetização imune ao AdBlock através de vitrines de afiliados integradas; capturar e reter a audiência por meio de newsletter e comunidades dedicadas; expandir o catálogo editorial para nichos de maior valor comercial (Mangás, Games e Tecnologia Geek) integrando novas APIs (como RAWG); e implementar recursos interativos de engajamento baseados no navegador (favoritos, avaliações locais de usuários) para decolar as métricas de tempo de sessão e páginas vistas.

---

## 🗺️ Fluxo de Valor e Expansão (Fase 4)

```mermaid
flowchart TD
    subgraph Fontes de Conteúdo Adicionais
        JikanManga[Jikan API - Catálogo de Mangás]
        RAWG[RAWG API - Banco de Dados de Games]
    end

    subgraph Automação Python Expandida
        Pipe[Orquestrador Local Python] -->|Consome dados| JikanManga
        Pipe -->|Consome dados| RAWG
        Pipe -->|Valida e Posta| WP[WordPress Local CMS]
    end

    subgraph CPTs & Metadados (Fase 4)
        WP -->|CPT manga| ACF1[Dados ACF: Editora, Autor, Volumes]
        WP -->|CPT game| ACF2[Dados ACF: Plataforma, Desenvolvedora]
    end

    subgraph Motor de Monetização Local
        ACF1 -->|Sponsorship Links| Affiliate[Vitrinas Shopee / Amazon / ML]
        ACF2 -->|AdSense programmatic| Ads[Anúncios Dinâmicos 1/2/3 por Post]
    end

    subgraph Retenção de Leitores
        Browser[Navegador do Usuário] -->|Interação local| LocalStor[LocalStorage: Favoritos & Votos]
        Browser -->|Assinatura| News[API Newsletter: Brevo / MailerLite]
    end
```

---

## ⚠️ Regra de Ouro de Execução (Absoluta)

> [!IMPORTANT]
> **Conforme o padrão do projeto, todas as implementações desta Fase 4 devem ser desenvolvidas de forma estritamente modular (Atomic Design), mobile-first com CSS fluido (`clamp()`), unidades relativas (`rem`) e documentadas nos diretórios físicos (`docs/` e `storybook.html`) com o respectivo registro no `changelog.md` antes de qualquer entrega ser concluída.**

---

### 📊 Sprint 1: Motor de Monetização de Elite & AdBlock Immunity
> **Foco Interno/Local:** Maximizar a receita de afiliados de forma nativa e sutil para contornar o uso massivo de AdBlock (~50% do público geek) e programar o posicionamento de anúncios no tema PHP.

- [ ] **Task 1.1: Componente de Vitrine Editorial e Comparador de Produtos (Afiliados)**
  * *Descrição:* Desenvolver um conjunto de campos customizados ACF (Grupo: `Ficha de Afiliados`) para armazenar imagens de produtos, notas e links das lojas Shopee, Mercado Livre e Amazon. Criar a molécula `card-produto-afiliado` e o organismo `vitrine-comparativa` (grade horizontal fluida).
  * *SEO & Padrões:* Links de afiliados obrigatoriamente gerados com a tag `rel="sponsored nofollow"`. Design premium responsivo imune a bloqueadores de anúncios (carregado via dados estáticos do banco).
  * *Entregáveis:* ACF importado no WordPress local, `molecules/card-produto-afiliado.php` e `.css`, `organisms/vitrine-comparativa.php` e `.css`, documentações na pasta `docs/` e atualização visual no `storybook.html`.

- [ ] **Task 1.2: Banner de Engajamento e Conscientização Anti-AdBlock**
  * *Descrição:* Implementar um banner sutil de rodapé/sidebar (`aviso-adblock` de forma não intrusiva) pedindo educadamente para o usuário adicionar o blog à lista de permissões se detectar o bloqueio de scripts de ads.
  * *Padrão de Qualidade:* Não usar popups de tela inteira bloqueantes (evitar punição de SEO do Google). Permitir que o usuário feche o banner (comportamento salvo em cookie/session por 7 dias).
  * *Entregáveis:* `atoms/aviso-adblock.php`, `atoms/aviso-adblock.css`, lógica de detecção nativa em JavaScript sem bibliotecas externas, e documentação do componente.

- [ ] **Task 1.3: Injeção Programática e Inteligente de Anúncios (AdSense)**
  * *Descrição:* Criar uma lógica no `functions.php` do tema child que insira dinamicamente os containers de anúncios (`anuncio-adsense.php` atom) dentro do `the_content` dos posts.
  * *Regras de Densidade:* 1 anúncio após o 2º parágrafo em posts curtos (resumos de episódios), 2 anúncios em análises (reviews) e até 3 anúncios distribuídos uniformemente em listas e guias extensos.
  * *Entregáveis:* Função PHP helper no `functions.php`, validação local com placeholders visuais de anúncios e garantia de não quebrar tags HTML aninhadas durante a injeção.

---

### 📧 Sprint 2: Captura de Audiência e Canais de Comunidade Privados
> **Foco Interno/Local:** Blindar a receita do portal contra oscilações de algoritmos do Google criando uma base de audiência engajada sob nosso controle direto.

- [ ] **Task 2.1: Sistema de Captura de Leads (Premium Newsletter Box)**
  * *Descrição:* Desenvolver um organismo de captura de e-mails (`secao-newsletter`) contendo design de alta conversão, micro-animações no envio e helpers de validação dinâmica.
  * *Integração Técnica:* Criar um endpoint/handler PHP seguro que conecte localmente via cURL com a API REST de uma plataforma de email marketing gratuita e robusta (ex: Brevo / MailerLite) para salvar os leads diretamente em uma lista de assinantes.
  * *Entregáveis:* `organisms/secao-newsletter.php` e `.css`, scripts JS de validação sem dependências de frameworks, documentação correspondente no `docs/` e atualização do `storybook.html`.

- [ ] **Task 2.2: Widgets de Compartilhamento Social Estáticos & Canal de Transmissão**
  * *Descrição:* Desenvolver botões de compartilhamento social ultraleves (WhatsApp, Telegram, Twitter/X) usando URLs de compartilhamento puro com ícones SVG inline no rodapé dos posts. Integrar na sidebar e pós-artigo um card visual premium chamando os leitores para o canal de novidades no Telegram/Discord.
  * *Performance:* Sem carregamento de scripts pesados de terceiros, garantindo pontuação LCP rápida e segurança absoluta.
  * *Entregáveis:* `molecules/social-share.php` e `.css`, `molecules/card-comunidade.php` e `.css`, e suas respectivas documentações.

---

### 🎮 Sprint 3: Expansão Multimídia e Novos Nichos (Mangás & Games)
> **Foco Interno/Local:** Expandir o escopo editorial para nichos adjacentes de alto valor financeiro (maior RPM), mapeando novas entidades no CMS e programando o pipeline local em Python para alimentar o catálogo automaticamente.

- [ ] **Task 3.1: Cadastro das Novas Entidades de Conteúdo (CPTs e ACF de Mangás & Games)**
  * *Descrição:* Configurar no WordPress local os novos CPTs `manga` e `game` com seus respectivos grupos de campos ACF.
  * *Campos Mangá:* Volumes lançados, status de publicação, autor/ilustrador, sinopse, capa, nota MAL e editora nacional com links afiliados.
  * *Campos Games:* Desenvolvedora, data de lançamento, nota média, plataformas disponíveis e link para guias de troféus.
  * *Arquitetura:* Configurar os relacionamentos bidirecionais locais para associar livremente um Mangá ou Game a um Anime principal já existente na base.
  * *Entregáveis:* Definição dos CPTs no `functions.php`, esquemas ACF exportados em JSON na pasta `acf-json/` do tema child.

- [ ] **Task 3.2: Pipeline Python para Ingestão Automática de Mangás (Jikan API)**
  * *Descrição:* Criar um novo script Python (`automation/import_mangas.py`) herdando as regras de rate limit e logging do pipeline anterior para consultar a Jikan API no endpoint `/manga` e cadastrar no CPT `manga` as obras relacionadas aos 500+ animes já importados na base local.
  * *Entregáveis:* Script `import_mangas.py` funcional e testado localmente, logs integrados e mapeamento dos metadados mapeados nos campos ACF correspondentes.

- [ ] **Task 3.3: Pipeline Python para Ingestão de Games Relacionados (RAWG API)**
  * *Descrição:* Desenvolver o script Python `automation/import_games.py` para consultar o banco de dados aberto da RAWG API, coletar informações sobre jogos de animes existentes (ex: *Demon Slayer*, *Naruto*, *Dragon Ball*) e cadastrá-los no CPT `game` vinculando-os ao respectivo CPT `anime`.
  * *Entregáveis:* Script de importação configurado, variáveis de ambiente RAWG_API_KEY no `.env` local e logs operacionais.

- [ ] **Task 3.4: Criação dos Templates e Arquivos de CSS Customizados (Manga & Game)**
  * *Descrição:* Desenvolver os templates dinâmicos físicos `single-manga.php` e `single-game.php` juntamente com seus arquivos CSS, consumindo os novos campos do ACF e montando blocos visuais na mesma identidade visual estrita de design tokens.
  * *Entregáveis:* `single-manga.php`, `single-manga.css`, `single-game.php`, `single-game.css`, arquivos de documentação em `docs/` e telas cadastradas no `storybook.html`.

---

### 💬 Sprint 4: Recursos Interativos de Engajamento e Gamificação (Browser-Based)
> **Foco Interno/Local:** Oferecer funcionalidades que façam o usuário passar mais tempo interagindo no portal, utilizando armazenamento no navegador (LocalStorage) para evitar sobrecarga no banco de dados e dispensar logins obrigatórios na fase de lançamento inicial.

- [ ] **Task 4.1: Sistema de Favoritos e Progresso "Minha Lista" (LocalStorage)**
  * *Descrição:* Desenvolver a molécula `btn-favorito` e o comportamento em Javascript para permitir que o usuário salve animes em sua própria lista de favoritos clicando em "Adicionar à Minha Lista" na página do anime.
  * *Exibição de Dados:* Exibir uma seção dinâmica (`secao-favoritos-usuario`) no cabeçalho ou página principal do usuário listando em grid os animes salvos localmente no navegador.
  * *Entregáveis:* Átomo `btn-favorito.php` e `.css`, scripts JS locais de gerenciamento de dados do navegador, template de página curta de Favoritos, e atualização no `storybook.html`.

- [ ] **Task 4.2: Sistema de Avaliação dos Leitores (Reader Rating vs Editorial Rating)**
  * *Descrição:* Criar uma molécula interativa (`leitores-rating`) que permita ao leitor votar em notas de 1 a 10 estrelas para cada anime, salvando a nota no LocalStorage e enviando por requisição assíncrona (AJAX / REST API) a nota para um campo de metadado global do post para computar a "Média da Comunidade".
  * *Exibição de Contraste:* Exibir no card de estatísticas o comparativo visual entre a "Nota da Redação" e a "Nota dos Leitores" de forma elegante.
  * *Entregáveis:* Molécula `leitores-rating.php` e `.css`, endpoints AJAX em `functions.php` e lógica JS de votação e proteção contra votos duplicados (cooldown local).

---

## 🏆 Critérios de Aceitação de Pronto (Definition of Done)

Para que as tarefas da Fase 4 sejam consideradas prontas no ambiente de desenvolvimento local, elas devem respeitar os seguintes limites:

1. **Acessibilidade WCAG 2.2 AA:** Todos os novos componentes interativos (newsletter, favoritos, comparadores de preços) devem ter contraste mínimo de 4.5:1, foco visível estrito em navegação por teclado e tags HTML semânticas descritivas.
2. **Zero Dependência Externa no Frontend:** Nenhuma biblioteca de JavaScript pesada ou externa (ex: jQuery plugins, frameworks SPA) deve ser importada no frontend. Todo o controle de estado e LocalStorage deve ser feito em JavaScript puro (Vanilla JS).
3. **Alinhamento Estrito ao Design System:** Toda propriedade visual de cores, tamanhos, fontes e raios deve fazer referência às variáveis CSS declaradas no `design-tokens.css`.
4. **Documentação e storybook.html atualizados**: Cada novo componente deve estar documentado na pasta `docs/` e renderizado na seção correspondente do `storybook.html`.
