# Rodando com Docker no Windows

Este projeto roda com PHP `8.2`, mas o Docker deixa o ambiente previsivel e evita conflito com XAMPP.

## Requisitos

- Docker Desktop para Windows
- WSL 2 habilitado no Docker Desktop
- Repositorio em uma pasta do WSL ou em uma pasta do Windows compartilhada com o Docker

## Primeiro uso

```bash
docker compose up --build
```

Acesse:

```text
http://localhost:8080
```

Em outro terminal, rode as migrations e o seeder:

```bash
docker compose exec app php spark migrate
docker compose exec app php spark db:seed UsersFromSqlSeeder
```

## Assets frontend

Para rodar o Webpack em modo watch:

```bash
docker compose --profile assets up assets
```

Para gerar build de producao uma vez:

```bash
docker compose run --rm assets npm run build
```

## Banco de dados

O MySQL fica disponivel para ferramentas locais em:

```text
host: localhost
porta: 3306
database: audiovisual
usuario: audiovisual
senha: audiovisual
root: root
```

Dentro do Docker, a aplicacao acessa o banco pelo hostname `db`.

O container da aplicacao usa `docker/app.env` montado como `.env` interno. Assim voce nao precisa alterar o `.env` local da maquina para rodar com Docker.

## Comandos uteis

```bash
docker compose exec app php spark migrate
docker compose exec app php spark db:seed UsersFromSqlSeeder
docker compose exec app composer install
docker compose down
docker compose down -v
```

Use `docker compose down -v` apenas se quiser apagar tambem o banco local criado pelo Docker.
