# Web Scraping Platform

## 🌐 Visão Geral do Projeto

Este projeto é uma plataforma de web scraping desenvolvida em Laravel, projetada para coletar, gerenciar e visualizar produtos de diferentes categorias de forma automatizada e eficiente.

### 🔍 Fonte de Dados: WebScraper.io

O projeto utiliza o site https://webscraper.io/test-sites/e-commerce/allinone, uma página de demonstração especificamente criada para testes e prática de web scraping. Este site simula um ambiente de e-commerce completo com características típicas de lojas online reais.

#### Características Principais do Site de Demonstração:
* 🏷️ Produtos organizados em categorias (computadores, telefones, tablets, monitores)
* 📸 Listagens de produtos com imagens, preços e descrições detalhadas
* 🧭 Estrutura de navegação com menus e submenus
* 📄 Elementos de paginação
* 🔬 Detalhes de produtos para análise

#### Propósito do Projeto
O objetivo deste projeto é demonstrar técnicas de web scraping em um ambiente seguro e legal, utilizando um site de teste projetado especificamente para desenvolvedores. Foi desenvolvido como um desafio técnico para avaliar habilidades de scraping em diferentes níveis de complexidade (Júnior, Pleno e Sênior).

## ✨ Principais Funcionalidades

### 🕷️ Scraping Automatizado
- Coleta de produtos de múltiplas categorias
- Agendamento automático de scraping
- Suporte a diferentes fontes de dados

### 📊 Painel Administrativo
- Visualização de produtos coletados
- Gerenciamento de logs de scraping
- Monitoramento de status do sistema

### 🔍 Recursos Principais
- Coleta de produtos em tempo real
- Categorização automática
- Cache inteligente
- Registro detalhado de operações

## 🛠️ Requisitos do Sistema

- PHP 8.1+
- Composer
- PostgreSQL 16
- Node.js (para frontend) (removido)
- Git

## 🚀 Instalação Passo a Passo

### 1. Clonar o Repositório
```bash
git clone https://github.com/seu-usuario/web-scraping-platform.git
cd web-scraping-platform
```

### 2. Configurar Ambiente
```bash
# Copiar arquivo de configuração
cp .env.example .env

# Configurar banco de dados PostgreSQL no .env
# Edite as configurações:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=seu_banco_de_dados
# DB_USERNAME=seu_usuario
# DB_PASSWORD=sua_senha

# Instalar dependências do PHP
composer install

# Instalar dependências do frontend
npm install

# Gerar chave da aplicação
php artisan key:generate
```

### 3. Configurar Banco de Dados PostgreSQL
```bash
# Criar banco de dados (se ainda não existir)
# Você pode usar o psql ou qualquer client PostgreSQL
createdb seu_banco_de_dados

# Rodar migrações
php artisan migrate

# Popular dados iniciais
php artisan db:seed
```

### Dependências Específicas do PostgreSQL
Certifique-se de ter instalado:
```bash
# Extensões PHP para PostgreSQL
sudo apt-get install php-pgsql        # Para sistemas baseados em Debian/Ubuntu
# ou
sudo yum install php-pgsql             # Para CentOS/RHEL

# Extensão Postgres para Laravel
composer require doctrine/dbal
```

### 4. Configurar Scraping
```bash
# Criar comando de scraping
php artisan make:command ScrapeProdutos

# Configurar agendamento no Kernel.php
# $schedule->command('product:fetch')->everyTwoMinutes();
```

### 5. Rodar a Aplicação
```bash
# Iniciar servidor local
php artisan serve

# Compilar assets
npm run dev

# Para produção
npm run build
```

## 🕰️ Agendamento de Scraping

### Método 1: Laravel Schedule (Recomendado)
```bash
# Rodar schedule a cada 2 minutos
php artisan schedule:run
```

### Método 2: Windows Batch Script (Simulando CronJob)
```batch
@echo off
:loop
php artisan schedule:run
timeout /t 120 /nobreak
goto loop
```

## 🔒 Configurações de Segurança

- Utilize autenticação de admin (Tela login)
- Proteja rotas sensíveis

## 📋 Comandos Úteis

```bash
# Limpar caches
php artisan config:clear
php artisan cache:clear

# Rodar testes
php artisan test

# Verificar status do schedule
php artisan schedule:list
```

## 🐛 Solução de Problemas

- Verifique logs em `storage/logs/laravel.log`
- Confirme configurações do `.env`
- Garanta permissões de diretório

## 🤝 Contribuição

1. Faça um Fork do projeto
2. Crie sua Feature Branch (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona NovaFuncionalidade'`)
4. Push para a Branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um Pull Request

## 📄 Licença

Distribuído sob a licença MIT. Veja `LICENSE` para mais informações.

## 📞 Contato

Marco Oliveira - marco.oliveira.s10@gmail.com

Link do Projeto: [https://github.com/marco-oliveira-s10/web-scraping-challenge]
