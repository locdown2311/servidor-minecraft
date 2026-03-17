# Painel de Controle Estilo Multicraft

Transformar o painel atual do MCHost em um painel completo ao estilo Multicraft, com funcionalidades avançadas de gerenciamento de servidor Minecraft.

## O que já existe

- ✅ Modelos: [Server](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/app/Models/Server.php#10-103), [Plan](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/app/Models/Plan.php#9-46), `Payment`, `User`
- ✅ Provisionamento Docker via API
- ✅ Ações básicas: criar/iniciar/parar/reiniciar/excluir
- ✅ Console com logs (somente leitura)
- ✅ Info de FTP
- ✅ Admin panel com stats, lista de servidores/usuários/planos
- ✅ Landing page com planos/preços

## O que falta (inspirado no Multicraft)

O Multicraft é famoso por ter:
1. **Console interativo** – enviar comandos para o servidor em tempo real
2. **Gerenciamento de Plugins/Mods** – instalar, ativar, desativar
3. **Gerenciamento de Jogadores** – lista de jogadores online, kick, ban, whitelist, op
4. **Server Properties** – editar `server.properties` pela interface
5. **Agendamento de Tarefas** – agendar restart, backup, comandos
6. **Backups** – criar e restaurar backups do mundo
7. **Gerenciamento de arquivos** – file manager web (não só FTP)
8. **Monitoramento** – CPU, RAM, TPS, players em real-time
9. **Sidebar de navegação** – painel com menu lateral para navegar entre features

> [!IMPORTANT]
> Este é um projeto grande. Proponho dividir em **fases incrementais**, implementando as funcionalidades mais impactantes primeiro. Cada fase pode ser entregue separadamente.

## Proposta: Fase 1 – Core Panel Upgrade

Focar nas funcionalidades que dão o visual e feeling de um painel Multicraft:

### 1. Layout com Sidebar + Tabs (Reestruturação visual)
### 2. Console interativo (enviar comandos)
### 3. Server Properties Editor (editar configurações)
### 4. Player Management (jogadores online, op/deop, ban/kick, whitelist)
### 5. Monitoramento de recursos (CPU/RAM/TPS)

---

## Proposed Changes

### Componente: Layout do Painel do Servidor

Reestruturar [servers/show.blade.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/resources/views/servers/show.blade.php) de uma página única para um painel com **sidebar** e **tabs/seções** semelhante ao Multicraft.

#### [MODIFY] [show.blade.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/resources/views/servers/show.blade.php)
- Transformar em layout com sidebar lateral (nav do servidor) + área de conteúdo
- Tabs: Overview, Console, Configurações, Jogadores, Backups
- Design dark/gamer com cards de status

---

### Componente: Console Interativo

Enviar comandos RCON para o container Minecraft em execução.

#### [MODIFY] [ServerProvisioningService.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/app/Services/ServerProvisioningService.php)
- Adicionar método `sendCommand($server, $command)` usando Docker exec API (`/containers/{id}/exec`)
- Adicionar método `getPlayerList($server)` que envia `list` e parseia resposta
- Adicionar método `getResourceUsage($server)` usando Docker stats API

#### [MODIFY] [ServerController.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/app/Http/Controllers/ServerController.php)
- Adicionar método `console()` – GET página do console
- Adicionar método `sendCommand()` – POST enviar comando via AJAX
- Adicionar método `logs()` – GET retorna logs via AJAX (polling)
- Adicionar método `players()` – GET lista de jogadores
- Adicionar método `settings()` – GET/POST server.properties
- Adicionar método `resources()` – GET uso de CPU/RAM

#### [MODIFY] [web.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/routes/web.php)
- Adicionar rotas para console, comando, jogadores, settings, resources

---

### Componente: Server Properties Editor

Editar `server.properties` do servidor Minecraft via Docker exec.

#### [MODIFY] [ServerProvisioningService.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/app/Services/ServerProvisioningService.php)
- Adicionar `getServerProperties($server)` – ler arquivo via Docker exec
- Adicionar `saveServerProperties($server, $properties)` – salvar arquivo via Docker exec

---

### Componente: Player Management

Listar jogadores online e executar comandos de moderação.

#### [MODIFY] [ServerProvisioningService.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/app/Services/ServerProvisioningService.php)
- Adicionar `kickPlayer()`, `banPlayer()`, `opPlayer()`, `whitelistPlayer()`

---

### Componente: Monitoramento de Recursos

Usar Docker Stats API para obter uso em tempo real.

#### [MODIFY] [ServerProvisioningService.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/app/Services/ServerProvisioningService.php)
- Adicionar `getContainerStats($server)` – retorna CPU%, RAM usado/total

---

### Componente: Views do Painel

#### [NEW] [console.blade.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/resources/views/servers/console.blade.php)
- Terminal dark com logs em scroll, input para comandos, AJAX polling

#### [NEW] [settings.blade.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/resources/views/servers/settings.blade.php)
- Formulário para editar server.properties com inputs tipados

#### [NEW] [players.blade.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/resources/views/servers/players.blade.php)
- Lista de jogadores online com ações (kick, ban, op, whitelist)

#### [NEW] [_sidebar.blade.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/resources/views/servers/_sidebar.blade.php)
- Componente de sidebar reutilizável para todas as páginas do servidor

#### [MODIFY] [show.blade.php](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/resources/views/servers/show.blade.php)
- Reestruturar com sidebar + overview do servidor

---

### Componente: CSS

#### [MODIFY] [app.css](file:///c:/Users/igor2/Desktop/site/servidor-minecraft/resources/css/app.css)
- Adicionar estilos para: sidebar, console terminal, server panel layout, player list, settings form, resource cards, tabs
- Design dark gaming-style inspirado no Multicraft

---

## Verification Plan

### Manual Verification
1. Rodar `npm run dev` e `php artisan serve` e acessar o painel do servidor
2. Verificar visualmente:
   - Sidebar de navegação do servidor aparece com links para Overview, Console, Configurações, Jogadores
   - Overview mostra status, info de conexão, FTP, plano
   - Console mostra logs e campo para enviar comandos
   - Settings mostra formulário com server.properties
   - Players mostra lista de jogadores (vazia se servidor offline)
   - Cards de monitoramento mostram CPU/RAM
3. Testar envio de comando no console (requer servidor rodando)
4. Testar edição de server.properties (requer servidor rodando)

> [!NOTE]
> As funcionalidades de interação com Docker (console, properties, players) só funcionam com um servidor Minecraft real rodando. Na ausência, o painel mostrará mensagens amigáveis indicando que o servidor precisa estar online.
