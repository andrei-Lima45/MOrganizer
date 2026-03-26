# MOrganizer

Sistema web de gerenciamento de tarefas com cadastro e login de usuários, organização por status e interface em estilo quadro de tarefas.

## Objetivo

Permitir que o usuário organize tarefas pessoais de forma simples e eficiente:

- Criação, edição e exclusão de tarefas
- Filtros e busca por texto
- Alteração de status (A fazer, Em progresso, Concluída)
- Drag and drop entre colunas

## Tecnologias utilizadas

- PHP
- MySQL / MariaDB
- JavaScript
- HTML
- CSS

## Demonstração online

Versão disponível para testes:

- http://morganizer.infinityfreeapp.com/

## Como executar localmente

### Requisitos

- XAMPP ou outro ambiente com Apache e MySQL/MariaDB
- Navegador
- phpMyAdmin

### Passos

1. Clone o repositório:

```bash
git clone https://github.com/andrei-Lima45/MOrganizer.git
```

2. Coloque a pasta do projeto dentro do diretório `htdocs`.
3. Inicie o Apache e o MySQL no XAMPP.
4. Crie um banco de dados chamado `morganizer` no phpMyAdmin.
5. Importe o arquivo `database.sql`.
6. Verifique as configurações do arquivo `config/db.php`.
7. Acesse no navegador:

- `http://localhost:8080/MOrganizer`

> Observação: a porta pode variar conforme a configuração do seu ambiente.
> Em alguns casos, o acesso pode ser feito por `http://localhost/MOrganizer`.

### Configuração de exemplo (em `config/db.php`)

```php
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3307';
$db   = getenv('DB_NAME') ?: 'morganizer';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
```

## Estrutura do projeto

```
MOrganizer/
├── api/
├── assets/
├── config/
├── css/
├── js/
├── cadastro.php
├── dashboard.php
├── database.sql
├── editTasks.php
├── index.php
├── login.php
├── logout.php
├── tasks.php
└── README.md
```

## Funcionalidades

- Cadastro de usuários
- Login e logout
- Criação de tarefas
- Edição de tarefas
- Exclusão de tarefas
- Alteração de status
- Drag and drop entre colunas
- Filtro por status
- Busca por texto
- Tema claro e escuro
- Exibição de prazo com data e hora

## Conta de teste

- Email: `teste@teste.com`
- Senha: `123456`

## Guia simples de uso

### 1. Primeiro acesso

1. Abra o navegador
2. Acesse o sistema
3. Caso não tenha conta, clique em **Cadastro**
4. Preencha nome, email e senha
5. Faça login

### 2. Tela inicial

Na tela principal, o usuário pode:

- Abrir a área de tarefas
- Criar uma nova tarefa
- Alternar entre tema claro e escuro
- Encerrar a sessão

### 3. Criar tarefa

1. Abra a área de tarefas
2. Digite o título da tarefa
3. (Opcional) Informe descrição, data e hora
4. Clique em **Adicionar tarefa**

### 4. Editar tarefa

1. Clique no botão de editar
2. Altere os campos
3. Salve

### 5. Mover tarefas

As tarefas podem ficar em:

- A fazer
- Em progresso
- Concluída

Pode mover:

- Pelos botões
- Por drag and drop

### 6. Pesquisar e filtrar

- Use a busca por texto
- Use o filtro por status

### 7. Remover tarefas

- Clique no botão de lixeira
- Ou apague todas

## Observações

- O título é obrigatório
- Recarregue a página se travar
- Exclusão não pode ser desfeita

## Projeto

Projeto acadêmico

## Autor

- Andrei Lima