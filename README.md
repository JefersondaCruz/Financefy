<div align="center">

# Financefy

![Status](https://img.shields.io/badge/status-em%20desenvolvimento-f59e0b)
![Laravel](https://img.shields.io/badge/backend-Laravel%2012-ff2d20)
![Vue](https://img.shields.io/badge/frontend-Vue%203-42b883)
![TypeScript](https://img.shields.io/badge/language-TypeScript-3178c6)
![PostgreSQL](https://img.shields.io/badge/database-PostgreSQL%2016-336791)
![Redis](https://img.shields.io/badge/cache-Redis%207-dc382d)
![Docker](https://img.shields.io/badge/dev%20env-Docker-2496ed)

Controle financeiro com frontend em Vue 3, API em Laravel 12, autenticação via Sanctum e recursos de IA/WhatsApp para análise e interação com dados financeiros.

</div>

## Sumário

- [Visão geral](#visão-geral)
- [Tecnologias utilizadas](#tecnologias-utilizadas)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Como rodar do zero com Docker](#como-rodar-do-zero-com-docker)
- [Comandos úteis](#comandos-úteis)
- [Variáveis de ambiente](#variáveis-de-ambiente)
- [Documentação da API](#documentação-da-api)
- [Frontend](#frontend)
- [Regras de negócio importantes](#regras-de-negócio-importantes)

## Visão geral

O projeto está dividido em duas partes ativas:

- `application`: API Laravel responsável por autenticação, usuários, categorias, transações, IA financeira e chatbot via waba
- `frontend`: aplicação Vue 3 que consome a API e entrega a interface web.


## Tecnologias utilizadas

### Backend

- PHP 8.2
- Laravel 12
- Laravel Sanctum
- PostgreSQL 16
- Redis 7
- OpenAI PHP Client

### Frontend

- Vue 3
- TypeScript
- Vite
- Pinia
- Vue Router
- Axios
- Tailwind CSS 4
- Chart.js

### Infraestrutura local

- Docker
- Docker Compose

## Estrutura do projeto

```text
Financefy/
├── application/        # Backend Laravel
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── .env.example
│   └── dockerfile
├── frontend/           # Frontend Vue 3 + Vite
│   ├── src/
│   ├── .env.example
│   └── dockerfile
├── docker-compose.yml
└── README.md
```

## Como rodar do zero com Docker

### Pré-requisitos

- Docker instalado
- Docker Compose disponível

### 1. Preparar os arquivos de ambiente

Backend:

```bash
cp application/.env.example application/.env
```

Frontend:

```bash
cp frontend/.env.example frontend/.env
```

### 2. Ajustar o `.env` do backend para Docker

No arquivo `application/.env`, garanta pelo menos estes valores:

```env
APP_NAME=Financefy
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

POSTGRES_USER=laravel
POSTGRES_PASSWORD=laravel
POSTGRES_DB=laravel

DB_CONNECTION=pgsql
DB_HOST=postgres_db
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=laravel

REDIS_CLIENT=predis
REDIS_HOST=redis
REDIS_PORT=6379

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

OPENAI_API_KEY=
OPENAI_CHATBOT_MODEL=gpt-4o-mini
OPENAI_BASE_URL=

WABA_VERIFY_TOKEN=
WABA_ACCESS_TOKEN=
WABA_PHONE_NUMBER_ID=
WABA_API_URL=https://graph.facebook.com/v23.0
```

### 3. Ajustar o `.env` do frontend

No arquivo `frontend/.env`:

```env
VITE_API_BASE_URL="http://localhost:8000/api"
```

### 4. Subir os containers

Para subir backend, frontend, PostgreSQL e Redis:

```bash
docker compose --profile all up --build -d
```

### 5. Gerar a chave da aplicação Laravel

```bash
docker compose exec application php artisan key:generate
```

### 6. Rodar as migrations

```bash
docker compose exec application php artisan migrate
```

### 7. Acessar a aplicação

- Frontend: `http://localhost:5173`
- Backend: `http://localhost:8000`
- API base: `http://localhost:8000/api`
- PostgreSQL: `localhost:5432`
- Redis: `localhost:6379`

### Fluxo recomendado de primeira execução

```bash
cp application/.env.example application/.env
cp frontend/.env.example frontend/.env
docker compose --profile all up --build -d
docker compose exec application php artisan key:generate
docker compose exec application php artisan migrate
```

## Comandos úteis

Subir apenas backend + banco + redis:

```bash
docker compose --profile app up --build -d
```

Subir backend + frontend + banco + redis:

```bash
docker compose --profile all up --build -d
```

Ver logs do backend:

```bash
docker compose logs -f application
```

Ver logs do frontend:

```bash
docker compose logs -f vue
```

Entrar no container do backend:

```bash
docker compose exec application sh
```

Rodar migrations novamente:

```bash
docker compose exec application php artisan migrate
```

Derrubar os containers:

```bash
docker compose down
```

Derrubar containers e limpar volumes do PostgreSQL:

```bash
docker compose down -v
```

## Variáveis de ambiente

### Backend

Principais variáveis esperadas pela API:

- `APP_URL`: URL base do backend.
- `DB_*`: conexão com PostgreSQL.
- `REDIS_*`: conexão usada pelo Redis.
- `OPENAI_API_KEY`: habilita o endpoint de IA financeira.
- `OPENAI_CHATBOT_MODEL`: modelo usado pelo chatbot.
- `OPENAI_BASE_URL`: opcional, útil para compatibilidade com gateways.
- `WABA_*`: integração com WhatsApp Business API.
- `CHATBOT_MEMORY_TTL`: TTL do histórico em Redis.
- `CHATBOT_MAX_MESSAGES`: quantidade máxima de mensagens mantidas no contexto.

### Frontend

- `VITE_API_BASE_URL`: base da API consumida pelo frontend.

## Documentação da API

Base URL local:

```text
http://localhost:8000/api
```

Autenticação protegida:

```http
Authorization: Bearer <token>
```

### Resumo das rotas

| Método | Rota | Auth | Descrição |
| --- | --- | --- | --- |
| POST | `/auth/register` | Não | Cadastra usuário |
| POST | `/auth/login` | Não | Realiza login |
| POST | `/auth/logout` | Não definida na rota, mas depende de usuário autenticado para funcionar corretamente | Revoga tokens |
| GET | `/user` | Sim | Retorna usuário autenticado |
| PUT | `/user/profile` | Sim | Atualiza nome, email e telefone |
| PUT | `/user/password` | Sim | Atualiza senha |
| DELETE | `/user` | Sim | Exclui a conta autenticada |
| GET | `/transactions` | Sim | Lista transações com filtros |
| GET | `/transactions/summary` | Sim | Retorna resumo financeiro |
| GET | `/transactions/export` | Sim | Exporta CSV |
| POST | `/transactions` | Sim | Cria transação |
| PUT | `/transactions/{id}` | Sim | Atualiza transação |
| DELETE | `/transactions/{id}` | Sim | Remove transação |
| GET | `/categories` | Sim | Lista categorias globais + do usuário |
| POST | `/categories` | Sim | Cria categoria do usuário |
| PUT | `/categories/{id}` | Sim | Atualiza categoria do usuário |
| DELETE | `/categories/{id}` | Sim | Exclui categoria do usuário |
| POST | `/ai/analyze` | Sim | Envia mensagem para a IA |
| GET | `/ai/history` | Sim | Histórico da conversa |
| DELETE | `/ai/conversation` | Sim | Limpa histórico/contexto da IA |
| GET | `/webhook/waba` | Não | Validação do webhook da Meta |
| POST | `/webhook/waba` | Não | Recebe mensagens do WhatsApp |

### Autenticação

#### `POST /auth/register`

Cria um usuário.

Body:

```json
{
  "name": "Joao Silva",
  "email": "joao@email.com",
  "password": "123456",
  "phone": "11999999999"
}
```

Regras:

- `name`: obrigatório, string, máximo 100 caracteres
- `email`: obrigatório, único
- `password`: obrigatório, string, entre 6 e 20 caracteres
- `phone`: obrigatório, único

Resposta:

```json
{
  "data": {
    "id": 1,
    "name": "Joao Silva",
    "email": "joao@email.com",
    "phone": "11999999999",
    "created_at": "2026-06-07T12:00:00.000000Z"
  }
}
```

#### `POST /auth/login`

Body:

```json
{
  "email": "joao@email.com",
  "password": "123456"
}
```

Resposta atual do backend:

```json
{
  "data": {
    "id": 1,
    "name": "Joao Silva",
    "email": "joao@email.com",
    "company": null,
    "token": "1|token-gerado-pelo-sanctum"
  }
}
```

#### `POST /auth/logout`

Invalida todos os tokens do usuário autenticado.

Resposta:

```json
{
  "message": "Logout realizado com sucesso."
}
```

### Usuário

#### `GET /user`

Retorna o usuário autenticado.

#### `PUT /user/profile`

Body parcial permitido:

```json
{
  "name": "Joao Silva",
  "email": "joao@email.com",
  "phone": "11999999999"
}
```

#### `PUT /user/password`

Body:

```json
{
  "current_password": "12345678",
  "password": "novaSenha123",
  "password_confirmation": "novaSenha123"
}
```

#### `DELETE /user`

Body:

```json
{
  "password": "12345678"
}
```

### Categorias

#### `GET /categories`

Retorna:

- categorias globais (`user_id = null`)
- categorias criadas pelo usuário autenticado

Exemplo:

```json
[
  {
    "id": 1,
    "name": "Salario",
    "type": "income",
    "user_id": null
  }
]
```

#### `POST /categories`

Body:

```json
{
  "name": "Mercado",
  "type": "expense"
}
```

#### `PUT /categories/{id}`

Body parcial permitido:

```json
{
  "name": "Supermercado",
  "type": "expense"
}
```

#### `DELETE /categories/{id}`

Somente categorias do próprio usuário podem ser alteradas ou removidas.

### Transações

#### Filtros disponíveis em `GET /transactions`, `GET /transactions/summary` e `GET /transactions/export`

| Parâmetro | Tipo | Obrigatório | Observação |
| --- | --- | --- | --- |
| `start_date` | `date` | Sim | Início do período |
| `end_date` | `date` | Sim | Fim do período |
| `page` | `integer` | Não | Paginação |
| `per_page` | `integer` | Não | Mínimo `1` |
| `search` | `string` | Não | Busca por descrição |
| `type` | `income \| expense` | Não | Tipo derivado da categoria |
| `category_id` | `integer` | Não | Filtra por categoria |
| `payment_method` | `credit_card \| pix \| money \| others` | Não | Método de pagamento |
| `recurring` | `yes \| no` | Não | Filtra recorrência |

#### `GET /transactions`

Exemplo:

```text
/transactions?start_date=2026-06-01&end_date=2026-06-30&per_page=15&search=mercado&type=expense
```

Resposta paginada:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 10,
      "description": "Supermercado",
      "amount": 220.9,
      "transaction_date": "2026-06-05",
      "payment_method": "pix",
      "category_id": 3,
      "category": {
        "id": 3,
        "name": "Mercado",
        "type": "expense",
        "user_id": 1
      },
      "is_recurring": false,
      "recurrence_type": null
    }
  ],
  "per_page": 15,
  "total": 1,
  "last_page": 1
}
```

#### `POST /transactions`

Body:

```json
{
  "category_id": 3,
  "description": "Supermercado",
  "amount": 220.9,
  "transaction_date": "2026-06-05",
  "payment_method": "pix",
  "is_recurring": false,
  "recurrence_type": null
}
```

Campos aceitos:

- `category_id`: opcional
- `category`: opcional, usado para tentar casar pelo nome quando `category_id` não for enviado
- `description`: obrigatório
- `amount`: obrigatório, numérico e maior que zero
- `transaction_date`: obrigatório, não pode ser futura
- `payment_method`: obrigatório
- `is_recurring`: opcional
- `recurrence_type`: opcional

#### `PUT /transactions/{id}`

Aceita atualização parcial dos mesmos campos acima.

#### `DELETE /transactions/{id}`

Remove a transação.

#### `GET /transactions/summary`

Retorna resumo do período:

```json
{
  "transaction_count": 8,
  "total_income": 5000,
  "total_expenses": 1820.9,
  "net_balance": 3179.1,
  "monthly_average": 852.61,
  "recurring_count": 2,
  "category_ranking": [
    {
      "name": "Moradia",
      "total": 1200
    }
  ]
}
```

#### `GET /transactions/export`

Baixa um arquivo CSV com colunas:

- `Descricao`
- `Categoria`
- `Tipo`
- `Pagamento`
- `Data`
- `Valor`
- `Recorrente`
- `Recorrencia`

### IA financeira

#### `GET /ai/history`

Retorna o histórico persistido em `ai_messages`.

#### `POST /ai/analyze`

Body:

```json
{
  "message": "Analise meus gastos dos ultimos meses"
}
```

Regras:

- `message`: obrigatório, string, máximo 1000 caracteres

Resposta:

```json
{
  "reply": "Sua maior concentracao de despesas esta em moradia..."
}
```

Observações:

- a IA usa contexto financeiro dos últimos 3 meses
- o histórico curto de contexto também é mantido em Redis

#### `DELETE /ai/conversation`

Limpa o histórico e o contexto em cache da conversa.

### Webhook WhatsApp

#### `GET /webhook/waba`

Usado pela Meta para validar o webhook com:

- `hub_mode`
- `hub_verify_token`
- `hub_challenge`

#### `POST /webhook/waba`

Recebe mensagens do WhatsApp Business, busca o usuário pelo telefone e responde usando o fluxo de chatbot.

## Frontend

O frontend é uma SPA em Vue 3 com autenticação baseada em token salvo em `localStorage` e enviado no header `Authorization: Bearer <token>`.

### Rotas da aplicação web

| Rota | Descrição |
| --- | --- |
| `/login` | Login |
| `/register` | Cadastro |
| `/dashboard` | Visão geral financeira |
| `/transactions` | Listagem, filtros, CRUD e exportação CSV |
| `/categories` | CRUD de categorias |
| `/profile` | Perfil, troca de senha e exclusão de conta |
| `/ai` | Chat com IA financeira |
| `/under-construction` | Tela auxiliar |

### O que a interface já cobre

- autenticação
- dashboard com KPIs, gráficos e ranking de categorias
- cadastro, edição, exclusão e duplicação de transações
- exportação de transações em CSV
- cadastro e gestão de categorias
- atualização de perfil e senha
- exclusão da conta autenticada
- conversa com IA financeira

## Regras de negócio importantes

- O tipo da transação não é enviado diretamente; ele é inferido pela categoria associada.
- Categorias podem ser globais (`user_id = null`) ou particulares do usuário.
- Apenas categorias do próprio usuário podem ser editadas ou removidas.
- Transações podem existir sem categoria vinculada.
- O telefone do usuário é relevante para o fluxo de WhatsApp e aparece bloqueado para edição no frontend.
- O endpoint de IA depende de `OPENAI_API_KEY` configurada.
- O webhook do WhatsApp depende de `WABA_VERIFY_TOKEN`, `WABA_ACCESS_TOKEN` e `WABA_PHONE_NUMBER_ID`.

