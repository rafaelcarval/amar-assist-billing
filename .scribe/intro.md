# Introduction

API REST para gerenciamento de clientes, contratos e cobranças.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8080</code>
</aside>

Esta documentação descreve a API do sistema Amar Assist Billing, desenvolvido como desafio técnico.

A aplicação permite gerenciar clientes e contratos, gerar e consultar cobranças, aplicar automaticamente multa diária de 1% após o vencimento e registrar pagamentos.

A API utiliza Laravel Sanctum para autenticação da SPA através de sessão e cookies seguros.

### Principais recursos

- Gerenciamento e pesquisa de clientes;
- Contratos PF e PJ;
- Ciclo de cobrança com ajuste automático ao número de dias do mês;
- Cobranças via PIX, boleto e cartão;
- Cálculo automático de multa por atraso;
- Controle de cobranças abertas e pagas;
- Processamento assíncrono com Redis e Laravel Horizon;
- Atualizações em tempo real com Laravel Echo e WebSockets.

### Ambiente local

Base URL:

`http://localhost:8080`

A interface web está disponível em:

`http://localhost:8080/app`

