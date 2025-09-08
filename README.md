# 🚀 Teste Técnico - Desenvolvedor Full Stack

## 📋 Descrição
Sistema completo de gerenciamento de usuários e produtos desenvolvido com **Vue.js 3** no frontend e **Laravel 12** no backend. O projeto implementa operações CRUD completas, e uma interface moderna e responsiva.

### ✨ Funcionalidades
- **Gestão de Usuários**: Cadastro, edição, visualização e exclusão
- **Gestão de Produtos**: CRUD completo com edicao e preços
- **Interface Responsiva**: Design moderno com Vuetify
- **API RESTful**: Endpoints bem estruturados com Laravel
- **Banco de Dados**: PostgreSQL com relacionamentos
- **Containerização**: Ambiente Docker completo
## 🛠️ Stack Tecnológica

### Frontend
- **Vue.js 3** - Framework JavaScript reativo
- **Vuetify 3** - Framework de componentes Material Design
- **Vue Router** - Roteamento SPA
- **Pinia** - Gerenciamento de estado
- **Axios** - Cliente HTTP
- **TypeScript** - Tipagem estática

### Backend
- **Laravel 12** - Framework PHP robusto
- **PHP 8.2** - Linguagem de programação
- **PostgreSQL 15** - Banco de dados relacional
- **Redis 7** - Cache e sessões
- **Nginx** - Servidor web e proxy reverso

### DevOps
- **Docker & Docker Compose** - Containerização
- **pgAdmin** - Interface web para PostgreSQL

## 🚀 Início Rápido

### 📋 Pré-requisitos
Certifique-se de ter instalado:
- **Docker Desktop** (versão 20.10+)
- **Docker Compose** (versão 2.0+)
- **Git** (para clonar o repositório)
- **Git Bash** (Windows - recomendado) ou **Terminal** (Linux/Mac)

### 🔧 Instalação em 3 Passos

#### 1️⃣ Clone o Repositório
```bash
git clone https://github.com/WesleyNajar/teste-dev-full-stack-desafio.git
cd teste-dev-full-stack-desafio
```

#### 2️⃣ Execute o Setup Automático

**Linux/Mac/WSL:**
```bash
# Torne o script executável
chmod +x setup.sh

# Execute o setup completo
./setup.sh
```

**Windows (Git Bash - Recomendado):**
```bash
# Abra o Git Bash na pasta do projeto
# Clique com botão direito na pasta → "Git Bash Here"

# Execute o setup completo
./setup.sh
```

**Windows (PowerShell - Alternativa):**
```powershell
# Use o script .bat em vez do .sh
.\dev.bat build
```

> ⚡ **O script fará tudo automaticamente**: construção dos containers, configuração do Laravel, execução das migrations, seeders e instalação das dependências.

#### 3️⃣ Acesse o Sistema
Após a execução bem-sucedida do script, acesse:

| Serviço | URL | Credenciais |
|---------|-----|-------------|
| **🌐 Frontend (Vue.js)** | http://localhost | - |
| **🔧 Backend API** | http://localhost/api | - |
| **🗄️ pgAdmin** | http://localhost:5050 | Email: `admin@admin.com`<br>Senha: `admin` |

### 🎯 Dados de Teste
O sistema já vem com dados de exemplo:
- **3 usuários** pré-cadastrados
- **5 produtos** de exemplo
- **Relacionamentos** configurados

## 📁 Estrutura do Projeto

```
teste-dev-full-stack-desafio/
├── 📄 docker-compose.yml          # Configuração principal do Docker
├── 🚀 setup.sh                    # Script de configuração automática
├── 📖 README.md                   # Documentação completa
├── 🔧 .gitignore                  # Arquivos ignorados pelo Git
│
├── 🖥️ backend/                    # Aplicação Laravel (API)
│   ├── 🐳 Dockerfile              # Container do backend
│   ├── 📦 composer.json           # Dependências PHP
│   ├── 🔧 env.example             # Variáveis de ambiente (exemplo)
│   ├── 📁 app/
│   │   ├── 📁 Http/Controllers/   # Controllers da API
│   │   ├── 📁 Models/             # Models (Usuario, Produto)
│   │   └── 📁 Exceptions/         # Tratamento de erros
│   ├── 📁 database/
│   │   ├── 📁 migrations/         # Migrações do banco
│   │   └── 📁 seeders/            # Dados de exemplo
│   ├── 📁 routes/
│   │   └── 🌐 api.php             # Rotas da API
│   └── 📁 config/                 # Configurações Laravel
│
├── 🎨 frontend/                   # Aplicação Vue.js (SPA)
│   ├── 🐳 Dockerfile              # Container do frontend
│   ├── 📦 package.json            # Dependências Node.js
│   ├── 🔧 env.example             # Variáveis de ambiente (exemplo)
│   ├── 📁 src/
│   │   ├── 📁 components/         # Componentes Vue reutilizáveis
│   │   ├── 📁 views/              # Páginas da aplicação
│   │   ├── 📁 router/             # Configuração de rotas
│   │   ├── 📁 stores/             # Gerenciamento de estado (Pinia)
│   │   ├── 📁 services/           # Serviços (API calls)
│   │   └── 📁 composables/        # Composables Vue 3
│   └── 📁 public/                 # Arquivos estáticos
│
└── 🐳 docker/                     # Configurações Docker
    ├── 📁 nginx/
    │   ├── 🐳 Dockerfile          # Container Nginx
    │   └── 📁 conf.d/
    │       └── 🌐 default.conf    # Configuração do proxy
    ├── 📁 php/
    │   └── ⚙️ local.ini           # Configurações PHP
    └── 📁 postgres/
        └── 📁 init/               # Scripts de inicialização
```

### 🏗️ Arquitetura do Sistema

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   🌐 Frontend   │    │   🔧 Backend    │    │   🗄️ Database   │
│   (Vue.js 3)    │◄──►│   (Laravel 12)  │◄──►│  (PostgreSQL)   │
│   Port: 3000    │    │   Port: 9000    │    │   Port: 5432    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                    ┌─────────────────┐
                    │   🌐 Nginx      │
                    │   (Proxy)       │
                    │   Port: 80      │
                    └─────────────────┘
```

## 🔧 Comandos Úteis

### 🐳 Gerenciamento de Containers
```bash
# Iniciar ambiente completo
docker-compose up -d

# Parar todos os containers
docker-compose down

# Ver logs em tempo real
docker-compose logs -f

# Reconstruir containers (após mudanças)
docker-compose up --build -d

# Ver status dos containers
docker-compose ps

# Acessar container específico
docker-compose exec backend bash
docker-compose exec frontend sh
docker-compose exec postgres psql -U laravel_user -d laravel_app
```

### 🛠️ Desenvolvimento Backend
```bash
# Instalar dependências do Laravel
docker-compose exec backend composer install

# Executar migrations
docker-compose exec backend php artisan migrate

# Executar seeders (dados de exemplo)
docker-compose exec backend php artisan db:seed

# Gerar chave da aplicação
docker-compose exec backend php artisan key:generate

# Limpar cache
docker-compose exec backend php artisan cache:clear

# Ver rotas disponíveis
docker-compose exec backend php artisan route:list
```

### 🎨 Desenvolvimento Frontend
```bash
# Instalar dependências do Vue.js
docker-compose exec frontend npm install

# Executar em modo desenvolvimento
docker-compose exec frontend npm run dev

# Build para produção
docker-compose exec frontend npm run build
```

### 🛠️ Scripts de Desenvolvimento

**Linux/Mac/WSL:**
```bash
# Usar script de desenvolvimento
./dev.sh help          # Ver todos os comandos
./dev.sh logs          # Ver logs
./dev.sh shell backend # Acessar backend
./dev.sh migrate       # Executar migrations
./dev.sh clean         # Limpar ambiente
```

**Windows:**
```cmd
# Usar script de desenvolvimento
dev.bat help          # Ver todos os comandos
dev.bat logs          # Ver logs
dev.bat shell backend # Acessar backend
dev.bat migrate       # Executar migrations
dev.bat clean         # Limpar ambiente
```

## 🌐 Portas e Serviços

| Serviço | Porta | Descrição |
|---------|-------|-----------|
| **🌐 Nginx** | 80 | Proxy reverso (Frontend/Backend) |
| **🎨 Vue.js** | 3000 | Desenvolvimento frontend |
| **🔧 Laravel** | 9000 | API backend (interno) |
| **🗄️ PostgreSQL** | 5432 | Banco de dados |
| **📊 pgAdmin** | 5050 | Interface web do banco |
| **⚡ Redis** | 6379 | Cache e sessões |

## 🔒 Configurações de Ambiente

### Backend (Laravel)
As variáveis estão no arquivo `backend/env.example`:
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=laravel_app
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_password
```

### Frontend (Vue.js)
As variáveis estão no arquivo `frontend/env.example`:
```env
VITE_APP_API_URL=http://localhost/api
VITE_APP_TITLE="Teste Full Stack"
```

## 🐛 Troubleshooting

### ❌ Problemas Comuns

#### 1. **Porta já em uso**
```bash
# Verificar portas em uso
netstat -tulpn | grep :80
netstat -tulpn | grep :3000

# Parar serviços conflitantes
sudo systemctl stop apache2  # Ubuntu/Debian
sudo systemctl stop nginx    # Se necessário
```

#### 2. **Docker não inicia**
```bash
# Verificar se Docker está rodando
docker --version
docker-compose --version

# Reiniciar Docker Desktop (Windows/Mac)
# Ou reiniciar serviço (Linux)
sudo systemctl restart docker
```

#### 3. **Banco não conecta**
```bash
# Aguardar inicialização do PostgreSQL
docker-compose logs postgres

# Verificar se container está rodando
docker-compose ps postgres

# Testar conexão manual
docker-compose exec postgres psql -U laravel_user -d laravel_app
```

#### 4. **Permissões de arquivo**
```bash
# Corrigir permissões do script
chmod +x setup.sh

# Corrigir permissões do Laravel
docker-compose exec backend chown -R www-data:www-data /var/www/storage
```

### 🔍 Verificação de Logs
```bash
# Logs de todos os serviços
docker-compose logs -f

# Logs específicos
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f postgres
docker-compose logs -f nginx
```

## 🚀 Deploy e Produção

### 📦 Build para Produção
```bash
# Build do frontend
docker-compose exec frontend npm run build

# Otimizar Laravel
docker-compose exec backend php artisan config:cache
docker-compose exec backend php artisan route:cache
docker-compose exec backend php artisan view:cache
```

### 🔄 Backup do Banco
```bash
# Backup completo
docker-compose exec postgres pg_dump -U laravel_user laravel_app > backup.sql

# Restaurar backup
docker-compose exec -T postgres psql -U laravel_user laravel_app < backup.sql
```

## 📞 Suporte e Contato

### 🆘 Em caso de problemas:
1. **Verifique os logs**: `docker-compose logs -f`
2. **Reinicie os containers**: `docker-compose restart`
3. **Reconstrua o ambiente**: `docker-compose up --build -d`
4. **Execute o setup novamente**: `./setup.sh`

### 📋 Checklist de Verificação
- [ ] Docker Desktop está rodando
- [ ] Portas 80, 3000, 5432, 5050 estão livres
- [ ] Script `setup.sh` tem permissão de execução
- [ ] Todos os containers estão rodando (`docker-compose ps`)
- [ ] Frontend acessível em http://localhost
- [ ] API respondendo em http://localhost/api
- [ ] pgAdmin acessível em http://localhost:5050

## 🎯 Informações Importantes

### 📁 Arquivos Principais
- **`setup.sh`** - Script de configuração automática (Linux/Mac/WSL)
- **`dev.sh`** - Script de desenvolvimento (Linux/Mac/WSL)
- **`dev.bat`** - Script de desenvolvimento (Windows)
- **`docker-compose.yml`** - Configuração dos containers
- **`backend/env.example`** - Variáveis de ambiente do backend
- **`frontend/env.example`** - Variáveis de ambiente do frontend

### 🔧 Configurações Automáticas
O projeto está configurado para funcionar automaticamente:
- ✅ **Arquivos .env** são criados automaticamente a partir dos exemplos
- ✅ **Dependências** são instaladas automaticamente
- ✅ **Banco de dados** é configurado com migrations e seeders
- ✅ **Permissões** são ajustadas automaticamente
- ✅ **Health checks** garantem que os serviços estejam prontos

### 🚀 Para Recrutadores
Este projeto foi desenvolvido para ser **facilmente executável** em qualquer máquina:

1. **Clone o repositório**
2. **Execute o setup**:
   - **Linux/Mac/WSL**: `./setup.sh`
   - **Windows**: Use o **Git Bash** (recomendado) e execute `./setup.sh`
3. **Acesse http://localhost**

**Tempo estimado de setup**: 3-5 minutos (dependendo da velocidade da internet)

### 💡 **Dica para Windows:**
- **Git Bash** é a melhor opção para executar scripts bash
- Clique com botão direito na pasta → "Git Bash Here"
- Execute `./setup.sh` normalmente

### 📊 Dados de Teste Incluídos
- **3 usuários** com diferentes perfis
- **5 produtos** de exemplo
- **Relacionamentos** entre usuários e produtos
- **Dados realistas** para demonstração

### 🔒 Segurança
- **Senhas padrão** são seguras para ambiente de teste
- **Credenciais** estão documentadas no README
- **Banco de dados** isolado em container
- **Rede interna** entre containers
