<h1> Financefy, oque é?</h1> 

> Status do Projeto: :warning: em desenvolvimento

###
 <h2> 🚧 EM CONSTRUÇÃO 🚧 </h2>

# API Documentation

## 📋 Índice
- [Autenticação](#autenticação)
- [Transações](#transações)
- [Categorias](#categorias)

---

## 🔐 Autenticação

### Registro de Usuário
**POST** `/auth/register`

Cria uma nova conta de usuário.

**Body:**
```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

**Resposta de Sucesso (201):**
```json
{
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxx"
}
```

---

### Login
**POST** `/auth/login`

Autentica um usuário existente.

**Body:**
```json
{
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Resposta de Sucesso (200):**
```json
{
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxx"
}
```

---

### Logout
**POST** `/auth/logout`

Revoga o token de autenticação atual.

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "message": "Logout realizado com sucesso"
}
```

---

## 💰 Transações

> ⚠️ **Todas as rotas de transações requerem autenticação via Sanctum**

### Listar Transações
**GET** `/transactions`

Retorna todas as transações do usuário autenticado.

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "data": [
    {
      "id": 1,
      "description": "Salário",
      "amount": 5000.00,
      "type": "income",
      "category_id": 1,
      "date": "2024-01-15",
      "created_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "description": "Aluguel",
      "amount": 1500.00,
      "type": "expense",
      "category_id": 2,
      "date": "2024-01-20",
      "created_at": "2024-01-20T14:20:00.000000Z"
    }
  ]
}
```

---

### Criar Transação
**POST** `/transactions`

Cria uma nova transação.

**Headers:**
```
Authorization: Bearer {token}
```

**Body:**
```json
{
  "description": "Compra no supermercado",
  "amount": 250.50,
  "type": "expense",
  "category_id": 3,
  "date": "2024-01-25"
}
```

**Resposta de Sucesso (201):**
```json
{
  "data": {
    "id": 3,
    "description": "Compra no supermercado",
    "amount": 250.50,
    "type": "expense",
    "category_id": 3,
    "date": "2024-01-25",
    "created_at": "2024-01-25T16:45:00.000000Z"
  }
}
```

---

### Atualizar Transação
**PUT** `/transactions/{id}`

Atualiza uma transação existente.

**Headers:**
```
Authorization: Bearer {token}
```

**Body:**
```json
{
  "description": "Compra no supermercado - Atualizado",
  "amount": 300.00,
  "type": "expense",
  "category_id": 3,
  "date": "2024-01-25"
}
```

**Resposta de Sucesso (200):**
```json
{
  "data": {
    "id": 3,
    "description": "Compra no supermercado - Atualizado",
    "amount": 300.00,
    "type": "expense",
    "category_id": 3,
    "date": "2024-01-25",
    "updated_at": "2024-01-26T10:15:00.000000Z"
  }
}
```

---

### Deletar Transação
**DELETE** `/transactions/{id}`

Remove uma transação.

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "message": "Transação deletada com sucesso"
}
```

---

## 🏷️ Categorias

> ⚠️ **Requer autenticação via Sanctum**

### Listar Categorias
**GET** `/categories`

Retorna todas as categorias disponíveis.

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Salário",
      "type": "income"
    },
    {
      "id": 2,
      "name": "Moradia",
      "type": "expense"
    },
    {
      "id": 3,
      "name": "Alimentação",
      "type": "expense"
    }
  ]
}
```

---

## 📝 Notas

### Tipos de Transação
- `income`: Receita/Entrada
- `expense`: Despesa/Saída

### Autenticação
Todas as rotas protegidas requerem o header `Authorization` com o token Bearer obtido no login ou registro:

```
Authorization: Bearer {seu_token_aqui}
```

