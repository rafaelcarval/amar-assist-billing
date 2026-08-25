# Amar Assist Billing

Sistema simplificado para gestão de clientes, contratos e cobranças, desenvolvido como desafio técnico para a **Amar Assist**.

A aplicação foi construída com Laravel 9 e Vue.js 3, utilizando MySQL para persistência, Redis para cache, sessões e filas, Laravel Horizon para monitoramento dos workers e Laravel Echo com Soketi para comunicação em tempo real.

---

## Funcionalidades

### Clientes

- Cadastro e consulta de clientes
- Suporte a CPF e CNPJ
- Validação de CPF/CNPJ
- Normalização do documento antes da persistência
- Pesquisa por:
  - Nome
  - CPF/CNPJ
  - Situação
- Ativação e desativação de clientes
- Cliente que possui qualquer contrato não pode ser desativado

### Contratos

- Contratos PF e PJ
- Relacionamento com cliente
- Ciclo de cobrança configurável entre os dias 1 e 31
- Ajuste automático do vencimento conforme a quantidade de dias do mês

Exemplos:

```text
Ciclo 31 + Abril
→ vencimento em 30/04

Ciclo 31 + Fevereiro de 2026
→ vencimento em 28/02

Ciclo 31 + Fevereiro de 2028
→ vencimento em 29/02
```

### Cobranças

Métodos de pagamento suportados:

- PIX
- Boleto
- Cartão

Cada cobrança possui:

- Valor original
- Valor da multa
- Valor total
- Data de vencimento
- Situação
- Dados específicos do método de pagamento

Situações:

```text
OPEN
PAID
```

Uma cobrança vencida é determinada dinamicamente quando:

```text
status = OPEN
AND
due_date < data atual
```

O atraso não é persistido como um terceiro status.

### Multa por atraso

Após o vencimento é aplicada multa simples de:

```text
1% por dia de atraso
```

Exemplo:

```text
Valor original: R$ 1.000,00
Dias de atraso: 5

Multa:
5% = R$ 50,00

Total:
R$ 1.050,00
```

Os cálculos monetários são executados utilizando centavos inteiros para evitar problemas de precisão com ponto flutuante.

### Ordenação das cobranças

A tela de cobranças prioriza:

```text
1. Cobranças abertas e vencidas
2. Cobranças abertas e ainda não vencidas
3. Cobranças pagas
```

---

# Tecnologias

## Backend

- PHP 8.1
- Laravel 9
- Laravel Sanctum
- Laravel Horizon
- Laravel Echo
- PHPUnit
- Scribe / OpenAPI

## Frontend

- Vue.js 3
- Vue Router
- Pinia
- Axios
- Bootstrap 5
- Vite

## Infraestrutura

- Docker
- Docker Compose
- Nginx
- PHP-FPM
- MySQL 8
- Redis 7
- Soketi
- Node.js 20

---

# Arquitetura

A aplicação utiliza uma arquitetura monolítica, separando frontend e API dentro do mesmo projeto Laravel.

```text
                         Browser
                            │
                            │ HTTP
                            ▼
                         Nginx
                            │
               ┌────────────┴─────────────┐
               │                          │
               ▼                          ▼
          Laravel API                   Vue 3
               │
      ┌────────┼─────────┐
      │        │         │
      ▼        ▼         ▼
    MySQL    Redis     Horizon
                         │
                         ▼
                       Soketi
                         │
                         │ WebSocket
                         ▼
                    Laravel Echo
                         │
                         ▼
                       Vue 3
```

---

# Requisitos

Antes de executar o projeto é necessário possuir:

- Git
- Docker Desktop
- Docker Compose

Não é necessário instalar PHP, Composer, MySQL, Redis ou Node.js diretamente na máquina.

Eles serão executados pelos containers Docker.

---

# Instalação

## 1. Clonar o repositório

```bash
git clone https://github.com/rafaelcarval/amar-assist-billing
```

Entre no diretório:

```bash
cd amar-assist-billing
```
---

## 2. Criar o arquivo de ambiente

### Linux / macOS

```bash
cp .env.example .env
```

### Windows PowerShell

```powershell
Copy-Item .env.example .env
```

O projeto já possui valores de desenvolvimento preparados no `.env.example`.

---

## 3. Construir os containers

```bash
docker compose build
```

---

## 4. Instalar as dependências PHP

Execute o Composer através do container da aplicação:

```bash
docker compose run --rm app composer install
```

---

## 5. Instalar as dependências JavaScript

```bash
docker compose run --rm node npm install
```

---

## 6. Subir todos os serviços

```bash
docker compose up -d
```

Isso iniciará os containers utilizados pela aplicação, incluindo:

```text
Nginx
PHP-FPM
MySQL
Redis
Horizon
Scheduler
Node/Vite
Soketi
```

Verifique:

```bash
docker compose ps
```

Os serviços devem estar em execução.

---

## 7. Gerar a chave da aplicação

```bash
docker compose exec app php artisan key:generate
```

---

## 8. Limpar caches iniciais

```bash
docker compose exec app php artisan optimize:clear
```

---

## 9. Executar migrations

```bash
docker compose exec app php artisan migrate
```

---

## 10. Criar os dados demonstrativos

```bash
docker compose exec app php artisan db:seed
```

Também é possível executar migration e seed juntos:

```bash
docker compose exec app php artisan migrate --seed
```

---

## 11. Gerar a documentação da API

```bash
docker compose exec app php artisan scribe:generate
```

---

## 12. Verificar o Horizon

```bash
docker compose exec app php artisan horizon:status
```

Resultado esperado:

```text
Horizon is running.
```

---

## 13. Verificar a aplicação

Abra:

```text
http://localhost:8080/app
```

---

# Credenciais de desenvolvimento

Um usuário administrativo é criado pelo seeder exclusivamente para ambiente local.

```text
E-mail:
admin@amar.test

Senha:
Amar@123456
```

Essas credenciais são apenas para demonstração local.

---

# URLs do projeto

## Aplicação

```text
http://localhost:8080/app
```

## Login

```text
http://localhost:8080/app/login
```

## Clientes

```text
http://localhost:8080/app/customers
```

## Cobranças

```text
http://localhost:8080/app/charges
```

## Documentação da API

```text
http://localhost:8080/docs/
```

## Laravel Horizon

```text
http://localhost:8080/horizon
```

## Health Check

```text
http://localhost:8080/health
```

## Vite

```text
http://localhost:5173
```

## Soketi WebSocket

```text
ws://localhost:6001
```

---

# Autenticação

A aplicação utiliza **Laravel Sanctum** no modo SPA.

A autenticação é baseada em:

```text
Session
+
Cookie
+
CSRF
```

Não são armazenados JWTs ou tokens de autenticação no `localStorage`.

O fluxo é:

```text
Vue
 │
 │ GET /sanctum/csrf-cookie
 ▼
Laravel
 │
 │ XSRF-TOKEN
 ▼
Browser
 │
 │ POST /login
 ▼
Laravel Session
 │
 │ Session Cookie
 ▼
Browser
 │
 │ GET /api/*
 ▼
auth:sanctum
```

### Login

```http
POST /login
```

### Usuário autenticado

```http
GET /api/user
```

### Logout

```http
POST /logout
```

As rotas relacionadas a clientes, contratos e cobranças são protegidas por:

```php
auth:sanctum
```

---

# Segurança

Foram adotadas as seguintes práticas:

- Laravel Sanctum
- Autenticação baseada em sessão
- Proteção CSRF
- Regeneração da sessão após login
- Invalidação da sessão no logout
- Regeneração do token CSRF no logout
- Rate limiting no endpoint de login
- Rotas da API protegidas
- Private Channel para eventos WebSocket
- Validação através de Form Requests
- CPF/CNPJ validado e normalizado
- Valores monetários sem uso de `float`
- Dados completos de cartão não são armazenados

---

# Segurança dos dados de cartão

A aplicação não persiste:

```text
Número completo do cartão
CVV
```

São armazenados somente:

```text
Token
Bandeira
Últimos quatro dígitos
Mês de validade
Ano de validade
```

Exemplo:

```text
Visa **** 4242
```

Isso reduz a exposição de dados sensíveis e aproxima a implementação do fluxo utilizado por gateways de pagamento.

---

# Redis e filas

Redis é utilizado para recursos como:

- Filas
- Horizon
- Sessões
- Cache

A conexão padrão de fila é:

```text
redis
```

As filas utilizadas são:

```text
default
broadcasts
```

---

# Laravel Horizon

Laravel Horizon monitora os jobs processados através do Redis.

Dashboard:

```text
http://localhost:8080/horizon
```

Para verificar o estado:

```bash
docker compose exec app php artisan horizon:status
```

Para reiniciar os workers depois de uma alteração:

```bash
docker compose exec app php artisan horizon:terminate
```

O container reiniciará o processo do Horizon automaticamente.

---

# Realtime

Eventos relacionados às cobranças podem ser transmitidos em tempo real.

O fluxo utilizado é:

```text
Charge
   │
   ▼
ChargeGenerated
   │
   ▼
Redis Queue
   │
   ▼
Laravel Horizon
   │
   ▼
Pusher Broadcaster
   │
   ▼
Soketi
   │
   │ WebSocket
   ▼
Laravel Echo
   │
   ▼
Vue.js
```

O servidor WebSocket utilizado é o **Soketi**, compatível com o protocolo Pusher.

Isso permite executar realtime localmente sem depender de uma conta externa no Pusher.

---

# Documentação da API

A documentação é gerada utilizando **Scribe**.

Para gerar novamente:

```bash
docker compose exec app php artisan scribe:generate
```

Acesse:

```text
http://localhost:8080/docs/
```

A documentação também disponibiliza especificação OpenAPI e coleção para testes de API, conforme configuração do Scribe.

---

# Principais endpoints

## Autenticação

```text
GET    /sanctum/csrf-cookie
POST   /login
POST   /logout
GET    /api/user
```

## Clientes

```text
GET    /api/customers
POST   /api/customers
GET    /api/customers/{customer}
PUT    /api/customers/{customer}
PATCH  /api/customers/{customer}/status
```

## Contratos

```text
GET    /api/customers/{customer}/contracts
POST   /api/customers/{customer}/contracts
```

## Cobranças

```text
GET    /api/charges
POST   /api/contracts/{contract}/charges
PATCH  /api/charges/{charge}/pay
```

---

# Filtros de clientes

O endpoint:

```http
GET /api/customers
```

aceita os seguintes filtros:

```text
name
document
status
per_page
```

Exemplos:

```text
/api/customers?name=João
```

```text
/api/customers?document=529982
```

```text
/api/customers?status=ACTIVE
```

Também é possível combinar filtros:

```text
/api/customers?name=João&status=ACTIVE
```

---

# Regras de negócio

## Cliente com contrato não pode ser desativado

Antes da desativação é verificada a existência de qualquer contrato associado ao cliente.

```text
Cliente
   │
   ├── não possui contratos
   │       ↓
   │   desativação permitida
   │
   └── possui contrato
           ↓
      desativação bloqueada
```

A regra considera qualquer contrato existente.

---

## Ciclo de cobrança

O ciclo pode variar entre:

```text
1 e 31
```

Quando o dia configurado não existe no mês de referência, é utilizado o último dia disponível.

```text
31/04
→ 30/04
```

```text
31/02/2026
→ 28/02/2026
```

```text
31/02/2028
→ 29/02/2028
```

---

## Cálculo da multa

O cálculo utilizado é:

```text
multa = valor original × 1% × dias em atraso
```

Não há juros compostos.

Exemplo:

```text
Valor:
R$ 500,00

Atraso:
3 dias

Multa:
R$ 15,00

Total:
R$ 515,00
```

---

# Valores monetários

No banco são utilizados campos:

```sql
DECIMAL(15,2)
```

Para cálculos internos, valores monetários são convertidos para centavos inteiros.

Exemplo:

```text
R$ 10,99
↓
1099 centavos
```

Isso evita comportamentos como:

```text
0.1 + 0.2 != 0.3
```

que podem ocorrer com números de ponto flutuante.

---

# Testes

O projeto possui testes unitários e de integração utilizando PHPUnit.

Execute todos:

```bash
docker compose exec app php artisan test
```

---

## Apenas testes unitários

```bash
docker compose exec app php artisan test tests/Unit
```

---

## Testes Feature

```bash
docker compose exec app php artisan test tests/Feature
```

---

## Testes de autenticação

```bash
docker compose exec app php artisan test tests/Feature/Auth
```

---

# Banco de testes

Os testes utilizam um banco MySQL separado:

```text
amar_billing_test
```

Isso mantém os testes isolados do banco usado durante o desenvolvimento.

O ambiente de testes é configurado através de:

```text
.env.testing
```

---

# Frontend

Durante o desenvolvimento o Vite é executado pelo container Node.

Caso seja necessário reiniciá-lo:

```bash
docker compose restart node
```

Logs:

```bash
docker compose logs -f node
```

---

# Build de produção do frontend

Execute:

```bash
docker compose exec node npm run build
```

O build deve finalizar sem erros:

```text
vite building for production...
✓ built
```

---

# Logs

## Todos os containers

```bash
docker compose logs
```

## Laravel

```bash
docker compose logs -f app
```

## Nginx

```bash
docker compose logs -f nginx
```

## Horizon

```bash
docker compose logs -f horizon
```

## Soketi

```bash
docker compose logs -f soketi
```

## Node / Vite

```bash
docker compose logs -f node
```

---

# Comandos úteis

## Ver containers

```bash
docker compose ps
```

## Entrar no container PHP

```bash
docker compose exec app sh
```

## Artisan

```bash
docker compose exec app php artisan
```

## Tinker

```bash
docker compose exec app php artisan tinker
```

## Limpar caches

```bash
docker compose exec app php artisan optimize:clear
```

## Executar migrations

```bash
docker compose exec app php artisan migrate
```

## Recriar banco local

```bash
docker compose exec app php artisan migrate:fresh --seed
```

> O comando acima apaga todos os dados do banco configurado no ambiente atual.

## Seeder

```bash
docker compose exec app php artisan db:seed
```

## Estado do Horizon

```bash
docker compose exec app php artisan horizon:status
```

## Reiniciar workers do Horizon

```bash
docker compose exec app php artisan horizon:terminate
```

## Gerar documentação

```bash
docker compose exec app php artisan scribe:generate
```

## Build Vue

```bash
docker compose exec node npm run build
```

---

# Parar o projeto

```bash
docker compose down
```

Para iniciar novamente:

```bash
docker compose up -d
```

---

# Remover containers e volumes

Caso seja necessário recriar completamente o ambiente:

```bash
docker compose down -v
```

Depois:

```bash
docker compose up -d
```

E execute novamente:

```bash
docker compose exec app php artisan migrate --seed
```

> `docker compose down -v` remove também os volumes persistentes, incluindo os dados do MySQL e Redis.

---

# Estrutura principal

```text
app/
├── Data/
├── Enums/
├── Events/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Rules/
└── Services/

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── js/
│   ├── composables/
│   ├── lib/
│   ├── router/
│   ├── stores/
│   └── views/
└── views/

routes/
├── api.php
├── channels.php
└── web.php

tests/
├── Feature/
└── Unit/

docker/
├── mysql/
└── nginx/

.github/
└── workflows/
```

---

# CI

O projeto utiliza GitHub Actions para validar automaticamente:

```text
Composer
↓
Migrations
↓
PHPUnit
↓
NPM
↓
Vite Build
```

A pipeline é executada em:

- Push para `main`
- Pull Request para `main`

Arquivo:

```text
.github/workflows/ci.yml
```

---

# Health Check

A aplicação possui endpoint simples de verificação:

```text
GET /health
```

URL:

```text
http://localhost:8080/health
```

Ele pode ser utilizado para verificar se a aplicação HTTP está respondendo.

---

# Decisões técnicas

## Monólito Laravel + Vue

Para o escopo deste desafio foi escolhida uma arquitetura monolítica.

Isso reduz a complexidade operacional e mantém:

```text
API
Frontend
Autenticação
Filas
Realtime
```

dentro da mesma aplicação.

A separação em microserviços não traria benefício proporcional para este domínio.

---

## Service Layer

Regras de negócio relevantes foram extraídas dos Controllers para Services.

Exemplos:

```text
BillingCycleService
ChargeCalculator
ChargeService
CustomerService
```

Os Controllers ficam responsáveis principalmente pela camada HTTP.

---

## Form Requests

A validação das requisições é realizada através de Form Requests, evitando regras espalhadas pelos Controllers.

---

## API Resources

As respostas da API utilizam Laravel API Resources, permitindo controlar explicitamente os dados expostos ao frontend.

---

## Enums

Estados e tipos do domínio utilizam Enums PHP.

Exemplos:

```text
CustomerStatus
ContractType
ChargeStatus
PaymentMethod
```

Isso reduz strings mágicas e melhora a legibilidade das regras.

---

## Banco de dados

Foram definidos:

- Foreign Keys
- Índices
- Restrição de exclusão onde necessária
- `DECIMAL(15,2)` para valores monetários
- Relacionamento 1:1 para detalhes do pagamento

---

## Realtime

O realtime foi implementado utilizando eventos Laravel e WebSockets.

O frontend não realiza polling periódico da API.

Quando um evento de cobrança é transmitido:

```text
Laravel
→ Redis
→ Horizon
→ Soketi
→ Echo
→ Vue
```

---

# Observação sobre Laravel 9

Laravel 9 foi utilizado por ser um requisito explícito do desafio técnico.

Por se tratar atualmente de uma versão fora do ciclo regular de suporte de segurança, em um novo projeto destinado à produção seria recomendada a adoção de uma versão do Laravel atualmente suportada.

A utilização do Laravel 9 neste projeto é, portanto, uma decisão de compatibilidade com o requisito do desafio e não uma recomendação para um novo sistema de produção.

---

# Checklist rápido após instalação

Depois de executar o processo de instalação, valide:

```bash
docker compose ps
```

Depois:

```bash
docker compose exec app php artisan test
```

Depois:

```bash
docker compose exec node npm run build
```

Depois:

```bash
docker compose exec app php artisan horizon:status
```

Depois acesse:

```text
Aplicação:
http://localhost:8080/app

Documentação:
http://localhost:8080/docs/

Horizon:
http://localhost:8080/horizon
```

Se esses pontos estiverem funcionando, o ambiente está pronto.

---

# Autor

**Rafael Frota Carvalho**
