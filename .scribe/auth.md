# Autenticação

A aplicação utiliza Laravel Sanctum no modo SPA com autenticação baseada em sessão.

O fluxo de autenticação é:

1. Solicitar o cookie CSRF através de `GET /sanctum/csrf-cookie`;
2. Realizar o login através de `POST /login`;
3. Utilizar a sessão autenticada para acessar os endpoints protegidos;
4. Encerrar a sessão através de `POST /logout`.

Os endpoints de clientes, contratos e cobranças exigem autenticação.
