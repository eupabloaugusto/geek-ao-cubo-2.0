# Arquitetura Jikan-Driven e Shadow Cache (Stale-While-Revalidate)

## 1. O Problema
Historicamente, sistemas que dependem de APIs externas (como a Jikan API) costumam adotar duas posturas problemáticas:
1. **Requisições Diretas (Live Fetch):** O front-end pede os dados à Jikan toda vez que um visitante entra. Resultado: Lentidão severa e esgotamento imediato do limite de taxa (Rate Limit de 3 req/segundo), derrubando o site em picos de acesso.
2. **Espelhamento (Database Mirroring):** Robôs importam todos os dados para o banco local (WordPress/ACF). Resultado: O banco de dados incha absurdamente com campos não utilizados, a sincronização é difícil, e a informação (ex: episódios novos) fica desatualizada rapidamente.

## 2. A Solução: Arquitetura Jikan-Driven
O Geek ao Cubo resolve esse impasse adotando a Jikan como seu **Banco de Dados Oficial em Tempo Real**.
O WordPress atua apenas como um "esqueleto de roteamento". O Custom Post Type `anime` armazena apenas **um único dado no banco local**: o `anime_id_mal`. Todo o resto da informação visual da página nasce do Cache.

## 3. A Engenharia do Shadow Cache (Ciclo de Backup)
Para garantir 100% de *uptime* (site sempre no ar) e 0% de impacto na API durante picos de dezenas de milhares de usuários, o Geek ao Cubo utiliza um ciclo de **Pre-Warming de Cache com Backup (Shadow Cache)** via WP-Cron.

### Como o Ciclo Funciona:
1. **Isolamento do Usuário:** O visitante do site **nunca** faz requisições à Jikan. Ele apenas tem permissão de ler o *Cache A* (Memória Transitória oficial) salvo no servidor local.
2. **WP-Cron Invisível:** De hora em hora (ou conforme agendado), o servidor do WordPress desperta silenciosamente no *back-end* e começa a varrer a lista de animes.
3. **Construção do Cache B:** Para cada anime, o servidor pede as atualizações recentes à Jikan (esperando o *delay* de 1 segundo obrigatório entre as chamadas). O servidor monta esses dados frescos em um novo pacote temporário chamado *Cache B*.
4. **Proteção contra Quedas (Fallback):**
   - Se a Jikan estiver fora do ar, devolver Erro 500 ou der Rate Limit 429, a construção do *Cache B* falha. O servidor aborta a missão e destrói o rascunho do *Cache B*. **O Cache A continua no ar servindo os usuários normalmente**. Ninguém nota a queda.
   - Se a Jikan responder com sucesso absoluto (HTTP 200), o servidor pega o *Cache B* validado e **sobrescreve instantaneamente o Cache A**.
5. **Resultado:** O Cache A agora está renovado. O ciclo reinicia.

## 4. Estrutura de Código
- **`inc/class-jikan-api.php`**: O coração do sistema. Contém os métodos que interagem com o WordPress Transients API (ex: `get_transient`, `set_transient`) e a lógica de validação do código HTTP 200 antes da substituição.
- **WP-Cron (`jikan_cache_warming_event`)**: O hook agendado que percorre os posts do CPT `anime` acionando as rotinas de atualização da classe acima.
- **Front-end (Templates)**: Substituição de `get_field()` por chamadas ao método público de leitura da classe Jikan API, extraindo propriedades diretamente do array retornado pelo Cache Oficial.

---
*Documento vivo de arquitetura. Atualizar caso o tempo do ciclo do Cron ou a política de Rate Limit da Jikan sejam alterados.*
