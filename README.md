# 🛡️ PHP Audit & MFA System — Secure Manager & Forensics

![PHP](https://img.shields.io/badge/PHP-v8.2+-777BB4?logo=php&logoColor=white)
![Security](https://img.shields.io/badge/Security-MFA%20%2F%20TOTP-green?logo=google-authenticator&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-OOP%20%2F%20MVC-blue?logo=c&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-yellow)

Um sistema de **gerenciamento de usuários** refatorado de um código legado (procedural) para uma arquitetura **Orientada a Objetos (OOP)** profissional.

O foco deste projeto vai além do CRUD básico: é um estudo prático de **Engenharia de Software Segura** e **Cibersegurança**, implementando trilhas de auditoria forense, autenticação de dois fatores (MFA) e proteção de dados em arquivos JSON (Flat-file storage).

> **Destaque:** O sistema não utiliza banco de dados SQL. Toda a persistência é feita em arquivos JSON protegidos, simulando um cenário de *NoSQL* ou armazenamento leve.

---

## 🚀 Stack Tecnológica

O projeto utiliza práticas modernas de desenvolvimento PHP (PSR-4) e bibliotecas de segurança.

| Componente | Tecnologia | Função |
| :--- | :--- | :--- |
| **Core / Backend** | **PHP 8+ (OOP)** | Lógica de negócios encapsulada em Classes (`UserManager`, `AuthManager`). |
| **Database** | **JSON (Flat-file)** | Armazenamento de dados (`users.json`) e credenciais (`auth.json`). |
| **Autenticação** | **Google2FA (PragmaRX)** | Implementação de **MFA/TOTP** para login administrativo seguro. |
| **Identificação** | **Ramsey UUID** | Geração de IDs únicos universais (prevenção de *ID Enumeration*). |
| **Validação** | **Rakit Validation** | Sanitização e validação rigorosa de inputs no backend. |
| **Dependências** | **Composer** | Gerenciamento de pacotes e Autoload (PSR-4). |

---

## 💻 Funcionalidades de Segurança

As funcionalidades foram desenhadas pensando em princípios de **Secure by Design**:

- 🛡️ **Autenticação MFA:** Login administrativo protegido por senha (hash) + Token TOTP (Google Authenticator).
- 🕵️ **Logs de Auditoria Forense:** Registro detalhado de atividades (`audit.log`) contendo **IP**, **User-Agent**, **Timestamp** e Ação realizada. O AuditLogger foi integrado ao AuthManager para registrar todas as tentativas de login (SUCESSO/FALHA), IP e User-Agent, facilitando a identificação de ataques de força bruta ou enumeração de usuários.
- 🆔 **UUIDs Seguros:** Substituição de IDs sequenciais (`1, 2, 3`) por UUIDs v4 (`e4ea...`) para evitar enumeração de usuários.
- 🕵️ **Proteção contra CSRF:** Estrutura pronta para a implementação de Tokens Anti-CSRF em formulários
- 🔒 **Proteção de Dados:** Bloqueio de acesso direto à pasta `/data` via `.htaccess`.
- 🔐 **Variáveis de Ambiente:** Credenciais sensíveis gerenciadas via `.env` (fora do código fonte).

---

## 📂 Estrutura do Projeto

```text
projeto/
├── data/           # 🔒 Arquivos JSON e Logs (Protegidos)
├── public/         # 🌍 Raiz do servidor (Frontend, Assets)
├── src/            # 🧠 Lógica do Backend (Classes, Services)
├── vendor/         # 📦 Dependências (Composer)
└── .env            # 🔑 Segredos (Não versionado)
```
---

## ⚙️ Instalação e Configuração Local
🧰 1. Pré-requisitos
Certifique-se de ter instalado:

- PHP v8.1+
- Composer

---

## 📦 2. Clonar e Instalar
```bash
https://github.com/davidtav/php-audit-mfa-system.git
cd php-audit-mfa-system

composer install
```

---

## 🔑 3. Configurar o Ambiente
Crie o arquivo de variáveis de ambiente baseando-se no exemplo:
```bash
cp .env.example .env
```
 Edite o arquivo .env e defina a senha inicial do administrador.
---

## 🚀 4. Setup Inicial (MFA)
1. Inicie o servidor PHP apontando para a pasta pública:
```bash
php -S localhost:8000 -t public
```
2. Acesse o script de configuração única no navegador: 👉 http://localhost:8000/setup.php
3. Escaneie o QR Code com seu aplicativo autenticador (O QR Code é gerado via JavaScript (Client-Side) para garantir que o segredo MFA nunca seja enviado a um servidor externo.).
4. ⚠️ IMPORTANTE: Após configurar, apague o arquivo public/setup.php ou certifique-se de que ele está bloqueado, pois ele reseta as credenciais.

---
## 🖥️ 5. Acessar o Dashboard
Acesse a área administrativa e faça login com seu usuário, senha e o código do app:
```bash
 http://localhost:8000/login.php
```
---

## 👨‍💻 Autor

**[David Mclaurel](https://www.linkedin.com/in/david-mclaurel/)**  Estudante de Análise e Desenvolvimento de Sistemas | Foco em Cibersegurança e Desenvolvimento Web.


---

## 📝 Licença

Este projeto está sob a licença [MIT](./LICENSE).
