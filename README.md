# Sistema de Gerenciamento de Viagens Corporativas

Este projeto é uma aplicação full stack para gerenciamento de pedidos de viagem corporativa, desenvolvida com Laravel (backend) e Vue.js (frontend).

## Demonstração
Acesse a uma demonstração do sistema em [http://onfly.pedrovilaca.dev](http://onfly.pedrovilaca.dev).

Esta demonstração é uma versão de desenvolvimento do sistema, com dados fictícios e não deve ser utilizada para fins de produção.

Utilize as credenciais abaixo para login:

### Usuários pré-cadastrados

| Nome | Email | Senha | Função |
|------|-------|-------|--------|
| Carlos Silva | joao@teste.com | password | Solicitante |
| Mariana Costa | mariana@teste.com | password | Solicitante |
| Roberto Mendes | roberto@teste.com | password | Aprovador |

## Funcionalidades

### Backend (Laravel)
- Criação, consulta e listagem de pedidos de viagem
- Atualização de status (solicitado, aprovado, cancelado)
- Filtragem por período, destino e status
- Notificações de aprovação ou cancelamento
- Autenticação JWT
- Testes unitários

### Frontend (Vue.js)
- Dashboard para visualização das solicitações
- Formulários para criação e atualização de solicitação
- Autenticação de usuário
- Feedback visual para operações

## Pré-requisitos

- Docker e Git instalados

## Começando

### Clone o repositório

```bash
git clone https://github.com/seu-usuario/corporate-travel.git
cd corporate-travel
```

### Construa e inicie os containers

```bash
docker compose build --no-cache
docker compose up -d
```

Este comando vai iniciar todos os serviços necessários:
- Backend Laravel (PHP, Nginx)
- Frontend Vue.js
- Banco de dados MySQL
- Redis para cache
- Mailhog para teste de emails

### Acesso às aplicações

- **Frontend**: [http://localhost:3000](http://localhost:3000)
- **Mailhog** (emails de teste para receber notificações): [http://localhost:8025](http://localhost:8025)

## Executando testes

Para executar os testes do backend:

```bash
docker compose exec app php artisan test
```

## Estrutura do Projeto

- `/backend` - Aplicação Laravel
- `/frontend` - Aplicação Vue.js
- `/docker` - Configurações do Docker

## Funcionalidades detalhadas

1. **Gerenciamento de Pedidos**
    - Criação de pedidos com destino, datas e propósito
    - Cada usuário vê apenas seus próprios pedidos (exceto aprovadores)
    - Filtragem por status, período e destino

2. **Sistema de Aprovação**
    - Usuários aprovadores podem aprovar/rejeitar pedidos
    - Solicitantes recebem notificações por email
    - Proteção contra alteração de status por usuários não autorizados

3. **Autenticação e Segurança**
    - Sistema completo de login/logout
    - Proteção das rotas da API com JWT
    - Validação de permissões por função de usuário

## Tecnologias Utilizadas

- **Backend**:
    - Laravel
    - MySQL
    - Redis
    - PHPUnit para testes
    - Mailhog

- **Frontend**:
    - Vue.js 3
    - Tailwind CSS
    - Axios

- **DevOps**:
    - Docker
    - Nginx
