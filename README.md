# Audiovisual - Controle Operacional de Eventos

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
- Calendário operacional e lista detalhada de eventos
- Gestão de usuários, perfil, notificações e logs administrativos

## Stack

- Backend: PHP 8.3+ / CodeIgniter 4
- Banco: MySQL (driver `MySQLi`)
- Frontend: Bootstrap, jQuery, DataTables, FullCalendar
- Build de assets: Webpack (Node.js)

## Requisitos

- PHP 8.3+
- Composer
- MySQL/MariaDB
- Node.js + npm

## Instalação

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
- `SUPERUSER_EMAIL`
- `DEFAULT_USER_PASSWORD`

5. Execute migrações (quando aplicável):

```bash
php spark migrate
```

6. Inicie a aplicação:

```bash
php spark serve
```

Acesse: `http://localhost:8080`

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

- `app/Controllers/Home.php`: fluxo principal (dashboard, cadastro, alertas, geradores)
- `app/Services/OperacionalService.php`: agregações, séries e métricas
- `app/Views/`: telas de dashboard, cadastro, listas e calendários
- `app/Database/Migrations/`: migrações da aplicação
- `app/Database/Migrations/sql/`: scripts SQL auxiliares

## Observações

- O sistema depende de autenticação (`auth`) e papéis (`admin`, `superuser`) para algumas rotas.
- Em ambiente de produção, configure o servidor apontando para a pasta `public/`.

## Licença

Projeto sob licença MIT.
