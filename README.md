# Sistema de Gerenciamento de Tarefas

Este é um sistema de gerenciamento de tarefas integrado a uma aplicação Laravel para monitorar e executar jobs agendados. O sistema permite visualizar, iniciar manualmente e controlar jobs.

## Funcionalidades

- Painel de controle com visão geral dos jobs
- Visualização e gerenciamento de tarefas agendadas
- Monitoramento de jobs pendentes e falhos
- Execução manual de tarefas
- Alternância de status de tarefas (habilitar/desabilitar)

## Requisitos do Sistema

- PHP 8.0 ou superior
- Composer
- Laravel 8.x ou superior
- MySQL 5.7 ou superior
- Node.js e NPM (para os assets frontend)

## Instalação

Siga os passos abaixo para instalar e configurar o sistema em sua máquina local:

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/seu-repositorio.git
cd seu-repositorio
```

### 2. Instale as dependências

```bash
composer install
npm install
npm run dev
```

### 3. Configure o ambiente

Copie o arquivo de ambiente e configure-o com suas informações:

```bash
cp .env.example .env
php artisan key:generate
```

Edite o arquivo `.env` com suas configurações de banco de dados:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```

### 4. Configure o banco de dados

```bash
php artisan migrate
```

### 5. Configure o job scheduler

Para que as tarefas agendadas funcionem, você precisa configurar o scheduler do Laravel. Adicione a seguinte entrada ao seu crontab:

```bash
* * * * * cd /caminho/para/seu/projeto && php artisan schedule:run >> /dev/null 2>&1
```

Para desenvolvimento local, você pode executar:

```bash
php artisan schedule:work
```

### 6. Configure a fila de jobs

Se estiver usando o driver de fila padrão (sync), não há configuração adicional necessária. Para ambientes de produção, recomenda-se usar o driver Redis ou Database.

Para executar o worker de fila:

```bash
php artisan queue:work
```

### 7. Inicie o servidor

```bash
php artisan serve
```

O aplicativo estará disponível em `http://localhost:8000`

## Uso do Sistema

### Acessando o Painel de Administração

1. Acesse `http://localhost:8000/admin`
2. Faça login com suas credenciais de administrador

### Gerenciando Tarefas

1. No painel de administração, clique em "Tasks" no menu lateral
2. Visualize todas as tarefas agendadas no sistema
3. Use os botões de ação para:
   - Executar uma tarefa imediatamente (botão Play)
   - Pausar/Retomar uma tarefa (botão Pause)

### Monitorando Jobs

O sistema mostra automaticamente:
- Número de jobs pendentes
- Número de jobs falhos
- Batches ativos
- Próxima execução agendada

## Arquitetura

O sistema é baseado na arquitetura MVC do Laravel:

- **Controllers**: `App\Http\Controllers\Admin\TaskController` e `App\Http\Controllers\Admin\DashboardController`
- **Views**: Localizadas em `resources/views/admin/tasks.blade.php`
- **Routes**: Definidas em `routes/web.php`

Os jobs agendados são configurados no `App\Console\Kernel.php` e podem ser personalizados conforme a necessidade.

## Personalizando

### Adicionando Novas Tarefas

Para adicionar novas tarefas, edite o arquivo `App\Http\Controllers\Admin\TaskController.php` e adicione novos itens ao array `$availableTasks`:

```php
public $availableTasks = [
    'product:fetch' => [
        'name' => 'Fetch Products',
        'description' => 'Fetch all products from external API',
        'schedule' => 'Every 10 minutes'
    ],
    'sua:nova-tarefa' => [
        'name' => 'Nome da Nova Tarefa',
        'description' => 'Descrição da sua nova tarefa',
        'schedule' => 'Programação (ex: Diariamente)'
    ]
];
```

Depois, crie um comando Artisan correspondente:

```bash
php artisan make:command SuaNovaTarefa --command=sua:nova-tarefa
```

E edite o arquivo gerado em `app/Console/Commands/SuaNovaTarefa.php`.

## Solução de Problemas

### Jobs não estão executando

- Verifique se o scheduler do Laravel está configurado corretamente
- Verifique os logs em `storage/logs/laravel.log`
- Certifique-se de que o worker da fila está em execução

### Erros de Interface

- Execute `npm run dev` para recompilar os assets
- Limpe o cache do navegador

### Erros 500

- Verifique os logs do Laravel em `storage/logs/laravel.log`
- Verifique as permissões de arquivos e pastas
- Certifique-se de que todas as dependências estão instaladas

## Contribuindo

Contribuições são bem-vindas! Para contribuir:

1. Fork o repositório
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas alterações (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo LICENSE para detalhes.
