# Controle Operacional de Eventos

Sistema interno para gestão operacional de eventos, com foco em custos, consumos, manutenção de geradores e acompanhamento de indicadores no dashboard.

## Principais funcionalidades

- Cadastro e edição de eventos operacionais
- Planejamento de eventos (previsão de custos)
- Dashboard com KPIs e gráficos por período/categoria
- Controle de consumo:
  - Diesel
  - Energia (Light / kWh)
  - Água CEDAE
  - Água de poço (monitoramento por m³)
- Alertas automáticos:
  - Geradores próximos/fora do limite de manutenção
  - Consumo de água CEDAE acima de 6 m³ por evento
  - Consumo de água de poço acima de 6 m³ por evento
- Histórico de uso e manutenção de geradores (G1/G2)
- Monitoramento de ruído por evento (Tribuna C, Pista de Cavalo, Corredor JB + média)
- Módulo de visitas (cadastro, edição, visualização e exclusão)
- Calendário operacional e lista detalhada de eventos
- Gestão de usuários, perfil, notificações e logs administrativos
- Assinatura mensal do sistema com cobrança Pix via Asaas, aviso de pendência e bloqueio por inadimplência

## Stack

- Backend: PHP 8.2+ / CodeIgniter 4
- Banco: MySQL (driver `MySQLi`)
- Frontend: Bootstrap, jQuery, DataTables, FullCalendar
- Build de assets: Webpack (Node.js)

## Requisitos

- Docker Desktop para Windows (recomendado para ambiente local no Windows)
- WSL 2 habilitado no Docker Desktop

Para instalação manual sem Docker:

- PHP 8.2+
- Composer
- MySQL/MariaDB
- Node.js + npm

## Instalação

### Windows com Docker

Este é o caminho recomendado no Windows. O Docker sobe a aplicação com PHP 8.2, MySQL 8 e um container opcional para os assets do frontend. Assim não é necessário instalar XAMPP, PHP, Composer, MySQL ou Node diretamente no Windows para rodar o sistema.

1. Instale o Docker Desktop:

- Baixe em `https://www.docker.com/products/docker-desktop/`
- Durante a instalação, mantenha o backend WSL 2 habilitado
- Abra o Docker Desktop e aguarde ele ficar em execução

Você pode rodar os comandos pelo PowerShell, Windows Terminal, Git Bash ou pelo terminal do WSL. O Docker Desktop usa WSL 2 por baixo, mas não é obrigatório abrir o WSL manualmente para subir o projeto.

2. Abra o projeto no terminal:

```bash
cd caminho/do/projeto/audiovisual
```

Se estiver usando WSL, prefira manter o projeto dentro do filesystem do Linux, por exemplo em `~/projetos/audiovisual`, para melhor performance.

3. Suba a aplicação e o banco:

```bash
docker compose up --build
```

Na primeira execução, o Docker baixa as imagens e instala as dependências PHP dentro de um volume. Isso pode demorar alguns minutos.

4. Em outro terminal, execute as migrations:

```bash
docker compose exec app php spark migrate
```

5. Execute o seeder de usuários iniciais:

```bash
docker compose exec app php spark db:seed UsersFromSqlSeeder
```

6. Acesse a aplicação:

```text
http://localhost:8080/login
```

O MySQL fica disponível localmente em:

```text
host: localhost
porta: 3306
database: audiovisual
usuario: audiovisual
senha: audiovisual
root password: root
```

Dentro do Docker, a aplicação acessa o banco pelo hostname `db`. O container usa `docker/app.env` como `.env` interno, então você não precisa alterar seu `.env` local para rodar com Docker.

Para parar os containers:

```bash
docker compose down
```

Para parar e apagar também o banco local criado pelo Docker:

```bash
docker compose down -v
```

### Restaurar backup `.sql` no Docker

Com os containers rodando, copie o arquivo `.sql` para dentro do container do banco:

```bash
docker cp caminho/do/backup.sql audiovisual-db-1:/tmp/backup.sql
```

Se o backup contém apenas as tabelas/dados do banco `audiovisual`, importe assim:

```bash
docker compose exec db sh -c "mysql -uroot -proot audiovisual < /tmp/backup.sql"
```

Se o backup já contém comandos como `CREATE DATABASE` ou `USE audiovisual`, importe sem informar o nome do banco:

```bash
docker compose exec db sh -c "mysql -uroot -proot < /tmp/backup.sql"
```

Para restaurar um backup completo do zero, apagando o banco local atual antes:

```bash
docker compose down -v
docker compose up -d db
docker cp caminho/do/backup.sql audiovisual-db-1:/tmp/backup.sql
docker compose exec db sh -c "mysql -uroot -proot audiovisual < /tmp/backup.sql"
docker compose up -d
```

### Assets no Docker

Para rodar o Webpack em modo watch:

```bash
docker compose --profile assets up assets
```

Para gerar o build de produção uma vez:

```bash
docker compose run --rm assets npm run build
```

### Windows com XAMPP e PHP 8.2

O sistema pode rodar sem Docker no XAMPP, desde que o XAMPP esteja usando PHP 8.2 ou superior e o Apache aponte para a pasta `public/` do projeto.

1. Confirme a versão do PHP usada no terminal:

```bat
C:\xampp\php\php.exe -v
```

O resultado precisa indicar PHP 8.2.x ou superior. Se `php -v` mostrar outra versão, ajuste o `PATH` do Windows para priorizar `C:\xampp\php`.

2. Habilite as extensões no `C:\xampp\php\php.ini`.

Remova o `;` do início destas linhas, se estiverem comentadas:

```ini
extension=curl
extension=fileinfo
extension=intl
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=zip
```

Depois reinicie o Apache pelo painel do XAMPP.

Para confirmar pelo terminal se a extensão `intl` carregou:

```bat
C:\xampp\php\php.exe -m | findstr intl
```

Se não aparecer `intl`, o Apache ainda vai exibir erro como `Class "Locale" not found`.

3. Instale o Composer usando o PHP do XAMPP e, no projeto, rode:

```bat
composer install
```

4. Crie o banco `audiovisual` no phpMyAdmin ou no MySQL do XAMPP.

5. Copie o ambiente de exemplo:

```bat
copy .env.example .env
```

6. Configure o `.env` para XAMPP. Exemplo comum:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/audiovisual/'

database.default.hostname = localhost
database.default.database = audiovisual
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Se o MySQL do XAMPP tiver senha para `root`, preencha `database.default.password`.

7. Configure o Apache para usar `public/` como raiz do site.

Opção recomendada: criar um VirtualHost em `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName audiovisual.local
    DocumentRoot "C:/xampp/htdocs/audiovisual/public"

    <Directory "C:/xampp/htdocs/audiovisual/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Adicione também no arquivo `C:\Windows\System32\drivers\etc\hosts`:

```text
127.0.0.1 audiovisual.local
```

Nesse caso, use no `.env`:

```ini
app.baseURL = 'http://audiovisual.local/'
```

8. Garanta que o rewrite do Apache está ativo.

No `C:\xampp\apache\conf\httpd.conf`, confira se esta linha está sem `#`:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

9. Rode migrations e seeder:

```bat
C:\xampp\php\php.exe spark migrate
C:\xampp\php\php.exe spark db:seed UsersFromSqlSeeder
```

10. Acesse:

```text
http://audiovisual.local/login
```

Se preferir não configurar VirtualHost, também funciona iniciar pelo servidor embutido:

```bat
C:\xampp\php\php.exe spark serve
```

Acesse `http://localhost:8080/login`.

#### Erro `Class "Locale" not found` no XAMPP

Esse erro indica que o PHP está sem a extensão `intl`. No XAMPP:

1. Abra `C:\xampp\php\php.ini`.
2. Procure por `;extension=intl`.
3. Remova o `;`, deixando:

```ini
extension=intl
```

4. Reinicie o Apache pelo painel do XAMPP.
5. Confirme no terminal:

```bat
C:\xampp\php\php.exe -m | findstr intl
```

Se ainda não aparecer, confirme se o Apache está usando o mesmo PHP do caminho `C:\xampp\php`.

### Manual via terminal PHP

1. Instale dependências PHP:

```bash
composer install
```

2. Instale dependências frontend:

```bash
npm install
```

3. Crie seu arquivo de ambiente:

```bash
cp .env.example .env
```

4. Configure no `.env`:

- `CI_ENVIRONMENT`
- `database.default.*`
- `TELEGRAM_*` (se usar notificações)
- `TELEGRAM_MUTED_EMAILS` (e-mails cujas ações não notificam o Telegram, separados por vírgula)
- `SUPERUSER_EMAIL`
- `DEFAULT_USER_PASSWORD`
- `SYSTEM_SUBSCRIPTION_*` e `ASAAS_*` (se usar assinatura mensal)

5. Execute migrações:

```bash
php spark migrate
```

6. Execute o seeder de usuários iniciais:

```bash
php spark db:seed UsersFromSqlSeeder
```

Esse seeder cria/atualiza:

- Rafael Soje (`superuser`)
- Guilherme Batista (`admin`)

Se você precisar sincronizar manualmente com SQL legado, use os scripts em:

- `app/Database/Migrations/sql/`
- Exemplo recente: `app/Database/Migrations/sql/2026-03-12-000009_create_visitas.sql`
- Dica: execute os arquivos SQL em ordem cronológica do prefixo (`YYYY-MM-DD-...`) para manter consistência com as migrations PHP.

7. Inicie a aplicação:

```bash
php spark serve
```

Acesse: `http://localhost:8080`

## Assinatura mensal do sistema

O sistema possui um módulo para cobrar a mensalidade de manutenção/VPS do administrador. Quando ativo, ele cria uma cobrança mensal, exibe aviso de pendência e bloqueia as funcionalidades após o período de tolerância. A tela `/assinatura` continua liberada para consulta e pagamento.

Configuração básica no `.env`:

```ini
SYSTEM_SUBSCRIPTION_ENABLED = true
SYSTEM_SUBSCRIPTION_AMOUNT = 65
SYSTEM_SUBSCRIPTION_WARNING_DAY = 5
SYSTEM_SUBSCRIPTION_DUE_DAY = 10
SYSTEM_SUBSCRIPTION_GRACE_DAYS = 2

ASAAS_BASE_URL = "https://api.asaas.com/v3"
ASAAS_API_KEY = "sua_chave_asaas"
ASAAS_CUSTOMER_ID = "cus_..."
ASAAS_WEBHOOK_TOKEN = "um_token_forte"
```

No Asaas, configure o webhook de cobranças para:

```text
https://seu-dominio.com/webhooks/asaas
```

Quando o Asaas enviar eventos como `PAYMENT_RECEIVED` ou `PAYMENT_OVERDUE`, o sistema atualiza a mensalidade automaticamente.

Depois da primeira configuração, o superusuário pode alterar ativação, valor, dia inicial de alerta, dia de vencimento e dias de tolerância pela tela `/assinatura`, sem editar o `.env`.

Opcionalmente, agende a sincronização diária no cron do Linux:

```cron
0 7 * * * cd /caminho/do/projeto/audiovisual && php spark subscription:sync >> writable/logs/subscription-cron.log 2>&1
```

Esse comando cria a cobrança mensal quando necessário e tenta sincronizar o status com o Asaas.

## Build de assets

Desenvolvimento (watch):

```bash
npm run dev
```

Produção:

```bash
npm run build
```

## Estrutura principal

- `app/Controllers/OperacionalController.php`: dashboard operacional, cadastro, planejamento e geradores
- `app/Controllers/VisitasController.php`: módulo de visitas (CRUD)
- `app/Controllers/AdminController.php`: usuários, notificações e logs administrativos
- `app/Controllers/Home.php`: endpoints compartilhados (perfil, avatar, calendário) e helpers comuns
- `app/Services/OperacionalService.php`: agregações, séries e métricas
- `app/Views/`: telas de dashboard, cadastro, listas e calendários
- `app/Database/Migrations/`: migrações da aplicação
- `app/Database/Seeds/UsersFromSqlSeeder.php`: seed de usuários padrão
- `app/Database/Migrations/sql/`: scripts SQL auxiliares

## Observações

- O sistema depende de autenticação (`auth`) e papéis (`admin`, `superuser`) para algumas rotas.
- Em ambiente de produção, configure o servidor apontando para a pasta `public/`.

## Licença

Projeto sob licença MIT.
