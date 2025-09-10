#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
print_message() {
    echo -e "${2}${1}${NC}"
}

print_header() {
    echo ""
    print_message "🚀 CONFIGURAÇÃO AUTOMÁTICA DO AMBIENTE" "$PURPLE"
    print_message "=====================================" "$PURPLE"
    echo ""
}

print_step() {
    print_message "📋 $1" "$BLUE"
}

print_success() {
    print_message "✅ $1" "$GREEN"
}

print_warning() {
    print_message "⚠️  $1" "$YELLOW"
}

print_error() {
    print_message "❌ $1" "$RED"
}

# Função para verificar se comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Função para aguardar serviço ficar disponível
wait_for_service() {
    local url=$1
    local service_name=$2
    local max_attempts=30
    local attempt=1
    
    print_step "Aguardando $service_name ficar disponível..."
    
    while [ $attempt -le $max_attempts ]; do
        if curl -s "$url" > /dev/null 2>&1; then
            print_success "$service_name está disponível!"
            return 0
        fi
        
        echo -n "."
        sleep 2
        attempt=$((attempt + 1))
    done
    
    print_warning "$service_name não respondeu após $max_attempts tentativas"
    return 1
}

# Função para verificar se porta está em uso
check_port() {
    local port=$1
    local service=$2
    
    if command_exists netstat; then
        if netstat -tuln 2>/dev/null | grep -q ":$port "; then
            print_warning "Porta $port está em uso. Verifique se $service não está rodando."
            return 1
        fi
    elif command_exists ss; then
        if ss -tuln 2>/dev/null | grep -q ":$port "; then
            print_warning "Porta $port está em uso. Verifique se $service não está rodando."
            return 1
        fi
    fi
    
    return 0
}

# Função para criar arquivos .env se não existirem
create_env_files() {
    print_step "Verificando arquivos de ambiente..."
    
    # Backend .env
    if [ ! -f "backend/.env" ]; then
        if [ -f "backend/env.example" ]; then
            cp backend/env.example backend/.env
            print_success "Arquivo backend/.env criado a partir do exemplo"
        else
            print_warning "Arquivo backend/env.example não encontrado"
        fi
    else
        print_success "Arquivo backend/.env já existe"
    fi
    
    # Frontend .env
    if [ ! -f "frontend/.env" ]; then
        if [ -f "frontend/env.example" ]; then
            cp frontend/env.example frontend/.env
            print_success "Arquivo frontend/.env criado a partir do exemplo"
        else
            print_warning "Arquivo frontend/env.example não encontrado"
        fi
    else
        print_success "Arquivo frontend/.env já existe"
    fi
}

# Função para verificar e corrigir problemas conhecidos
check_and_fix_known_issues() {
    print_step "Verificando problemas conhecidos..."
    
    # Verificar se composer.lock existe
    if [ ! -f "backend/composer.lock" ]; then
        print_warning "composer.lock não encontrado. Gerando automaticamente..."
        generate_composer_lock
    fi
    
    # Verificar se composer.json tem predis
    if ! grep -q "predis/predis" backend/composer.json; then
        print_warning "Dependência predis/predis não encontrada. Adicionando..."
        fix_composer_issues
    fi
    
    # Verificar se env.example tem configuração Redis correta
    if ! grep -q "REDIS_CLIENT=predis" backend/env.example; then
        print_warning "Configuração Redis incorreta. Corrigindo..."
        fix_redis_issues
    fi
    
    # Verificar se composer.lock está desatualizado
    if [ -f "backend/composer.lock" ] && [ "backend/composer.json" -nt "backend/composer.lock" ]; then
        print_warning "composer.lock está desatualizado. Regenerando..."
        generate_composer_lock
    fi
    
    print_success "Verificação de problemas concluída"
}

# Função para verificar dependências
check_dependencies() {
    print_step "Verificando dependências..."
    
    # Verificar Docker
    if ! command_exists docker; then
        print_error "Docker não está instalado. Instale o Docker Desktop primeiro."
        exit 1
    fi
    
    # Verificar Docker Compose
    if ! command_exists docker-compose && ! docker compose version >/dev/null 2>&1; then
        print_error "Docker Compose não está instalado."
        exit 1
    fi
    
    # Verificar se Docker está rodando
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker não está rodando. Inicie o Docker Desktop primeiro."
        exit 1
    fi
    
    print_success "Todas as dependências estão instaladas"
}

# Função para verificar portas
check_ports() {
    print_step "Verificando portas disponíveis..."
    
    local ports=(80 3000 5432 5050 6379)
    local services=("Nginx" "Vue.js" "PostgreSQL" "pgAdmin" "Redis")
    
    for i in "${!ports[@]}"; do
        if ! check_port "${ports[$i]}" "${services[$i]}"; then
            print_warning "Porta ${ports[$i]} (${services[$i]}) está em uso"
        fi
    done
}

# Função para limpar ambiente anterior
cleanup_environment() {
    print_step "Limpando ambiente anterior..."
    
    # Parar containers existentes
    docker-compose down --remove-orphans 2>/dev/null || true
    
    # Remover volumes órfãos (opcional)
    docker volume prune -f 2>/dev/null || true
    
    print_success "Ambiente anterior limpo"
}

# Função para corrigir problemas de composer
fix_composer_issues() {
    print_step "Corrigendo problemas de dependências do Composer..."
    
    # Verificar se composer.lock existe
    if [ ! -f "backend/composer.lock" ]; then
        print_message "composer.lock não encontrado. Gerando automaticamente..." "$YELLOW"
        generate_composer_lock
        return
    fi
    
    # Verificar se composer.lock está desatualizado
    if [ "backend/composer.json" -nt "backend/composer.lock" ]; then
        print_message "composer.lock está desatualizado. Regenerando..." "$YELLOW"
        rm backend/composer.lock
        generate_composer_lock
        return
    fi
    
    # Verificar se composer.json tem predis
    if grep -q "predis/predis" backend/composer.json; then
        print_message "Dependência predis/predis encontrada no composer.json" "$GREEN"
    else
        print_message "Adicionando predis/predis ao composer.json..." "$YELLOW"
        # Adicionar predis se não existir
        sed -i 's/"laravel\/tinker": "^2.10.1"/"laravel\/tinker": "^2.10.1",\n        "predis\/predis": "^2.2"/' backend/composer.json
        # Regenerar composer.lock após adicionar dependência
        generate_composer_lock
    fi
    
    print_success "Problemas de Composer corrigidos"
}

# Função para gerar composer.lock
generate_composer_lock() {
    print_step "Gerando composer.lock..."
    
    # Verificar se composer está disponível
    if ! command_exists composer; then
        print_warning "Composer não está disponível localmente. Será gerado no container."
        return
    fi
    
    # Gerar composer.lock localmente
    cd backend
    
    # Primeiro tentar composer install
    if composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs; then
        print_success "composer.lock gerado com sucesso via composer install"
        cd ..
        return
    fi
    
    # Se falhar, tentar composer update
    print_warning "composer install falhou. Tentando composer update..."
    if composer update --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs; then
        print_success "composer.lock gerado com sucesso via composer update"
        cd ..
        return
    fi
    
    # Se ainda falhar, tentar apenas composer install sem flags
    print_warning "composer update falhou. Tentando composer install básico..."
    if composer install --ignore-platform-reqs; then
        print_success "composer.lock gerado com sucesso via composer install básico"
        cd ..
        return
    fi
    
    # Se tudo falhar
    print_warning "Falha ao gerar composer.lock localmente. Será gerado no container."
    cd ..
}

# Função para corrigir problemas de Redis
fix_redis_issues() {
    print_step "Corrigindo problemas de Redis..."
    
    # Verificar se env.example tem configuração Redis correta
    if grep -q "REDIS_CLIENT=predis" backend/env.example; then
        print_message "Configuração Redis já está correta" "$GREEN"
    else
        print_message "Corrigindo configuração Redis..." "$YELLOW"
        # Substituir phpredis por predis
        sed -i 's/REDIS_CLIENT=phpredis/REDIS_CLIENT=predis/' backend/env.example
    fi
    
    print_success "Problemas de Redis corrigidos"
}

# Função para construir e iniciar containers
build_and_start() {
    print_step "Construindo e iniciando containers..."
    
    # Tentar construir containers
    if docker-compose up --build -d; then
        print_success "Containers construídos e iniciados"
    else
        print_warning "Falha na primeira tentativa de build. Tentando correções automáticas..."
        
        # Verificar se é erro de composer.lock ou dependências
        if docker-compose logs backend 2>/dev/null | grep -q "lock file is not up to date\|Class.*not found\|predis"; then
            print_step "Detectado problema com dependências do Composer. Aplicando correção..."
            fix_composer_issues
        fi
        
        # Verificar se é erro de extensão Redis
        if docker-compose logs backend 2>/dev/null | grep -q "redis"; then
            print_step "Detectado problema com Redis. Aplicando correção..."
            fix_redis_issues
        fi
        
        # Tentar novamente após correções
        print_step "Tentando build novamente após correções..."
        if docker-compose up --build -d; then
            print_success "Containers construídos e iniciados após correções"
        else
            print_error "Falha ao construir/iniciar containers mesmo após correções"
            print_warning "Verifique os logs: docker-compose logs backend"
            exit 1
        fi
    fi
}

# Função para aguardar serviços ficarem prontos
wait_for_services() {
    print_step "Aguardando serviços ficarem prontos..."
    
    # Aguardar PostgreSQL
    local max_attempts=60
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose exec -T postgres pg_isready -U laravel_user >/dev/null 2>&1; then
            print_success "PostgreSQL está pronto"
            break
        fi
        
        echo -n "."
        sleep 2
        attempt=$((attempt + 1))
        
        if [ $attempt -gt $max_attempts ]; then
            print_error "PostgreSQL não ficou pronto a tempo"
            exit 1
        fi
    done
    
    # Aguardar um pouco mais para garantir que todos os serviços estejam prontos
    sleep 10
}

# Função para verificar e instalar Predis
ensure_predis_installed() {
    print_step "Verificando instalação do Predis..."
    
    # Aguardar o container backend ficar pronto
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose exec -T backend php --version >/dev/null 2>&1; then
            print_success "Container backend está pronto"
            break
        fi
        
        echo -n "."
        sleep 2
        attempt=$((attempt + 1))
        
        if [ $attempt -gt $max_attempts ]; then
            print_warning "Container backend não ficou pronto a tempo"
            return 1
        fi
    done
    
    # Verificar se Predis está instalado
    if ! docker-compose exec -T backend composer show predis/predis >/dev/null 2>&1; then
        print_warning "Predis não encontrado. Instalando..."
        if docker-compose exec -T backend composer require predis/predis; then
            print_success "Predis instalado com sucesso"
        else
            print_error "Falha ao instalar Predis"
            return 1
        fi
    else
        print_success "Predis já está instalado"
    fi
    
    return 0
}

# Função para configurar Laravel
configure_laravel() {
    print_step "Configurando Laravel..."
    
    # Gerar chave da aplicação
    if docker-compose exec -T backend php artisan key:generate --force; then
        print_success "Chave da aplicação gerada"
    else
        print_warning "Falha ao gerar chave da aplicação"
    fi
    
    # Limpar cache
    docker-compose exec -T backend php artisan config:clear 2>/dev/null || true
    docker-compose exec -T backend php artisan cache:clear 2>/dev/null || true
    docker-compose exec -T backend php artisan route:clear 2>/dev/null || true
    
    print_success "Laravel configurado"
}

# Função para executar migrations e seeders
setup_database() {
    print_step "Configurando banco de dados..."
    
    # Executar migrations
    if docker-compose exec -T backend php artisan migrate:fresh --force; then
        print_success "Migrations executadas"
    else
        print_error "Falha ao executar migrations"
        exit 1
    fi
    
    # Executar seeders
    if docker-compose exec -T backend php artisan db:seed --force; then
        print_success "Seeders executados"
    else
        print_warning "Falha ao executar seeders"
    fi
}

# Função para instalar dependências do frontend
install_frontend_deps() {
    print_step "Instalando dependências do frontend..."
    
    if docker-compose exec -T frontend npm install; then
        print_success "Dependências do frontend instaladas"
    else
        print_warning "Falha ao instalar dependências do frontend"
    fi
}

# Função para verificar status dos containers
check_containers_status() {
    print_step "Verificando status dos containers..."
    
    echo ""
    docker-compose ps
    echo ""
    
    # Verificar se todos os containers estão rodando
    local containers=("backend" "frontend" "nginx" "postgres" "pgadmin" "redis")
    local all_running=true
    
    for container in "${containers[@]}"; do
        if ! docker-compose ps "$container" | grep -q "Up"; then
            print_warning "Container $container não está rodando"
            all_running=false
        fi
    done
    
    if [ "$all_running" = true ]; then
        print_success "Todos os containers estão rodando"
    else
        print_warning "Alguns containers podem não estar rodando corretamente"
    fi
}

# Função para testar conectividade
test_connectivity() {
    print_step "Testando conectividade dos serviços..."
    
    # Aguardar um pouco mais para garantir que os serviços estejam prontos
    sleep 5
    
    # Testar API Laravel
    echo -n "   API Laravel: "
    if curl -s -f http://localhost/api >/dev/null 2>&1; then
        print_success "API está respondendo"
    else
        print_warning "API não está respondendo (pode ser normal se não houver rota /api)"
    fi
    
    # Testar Frontend
    echo -n "   Frontend Vue.js: "
    if curl -s -f http://localhost >/dev/null 2>&1; then
        print_success "Frontend está respondendo"
    else
        print_warning "Frontend não está respondendo"
    fi
    
    # Testar pgAdmin
    echo -n "   pgAdmin: "
    if curl -s -f http://localhost:5050 >/dev/null 2>&1; then
        print_success "pgAdmin está respondendo"
    else
        print_warning "pgAdmin não está respondendo"
    fi
}

# Função para exibir informações finais
show_final_info() {
    echo ""
    print_message "🎉 CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!" "$GREEN"
    echo ""
    print_message "🌐 URLs de acesso:" "$CYAN"
    echo "   Frontend (Vue.js): http://localhost"
    echo "   Backend (Laravel API): http://localhost/api"
    echo "   pgAdmin: http://localhost:5050"
    echo ""
    print_message "📝 Credenciais pgAdmin:" "$CYAN"
    echo "   Email: admin@admin.com"
    echo "   Senha: admin"
    echo ""
    print_message "📊 Dados de teste criados:" "$CYAN"
    echo "   - 3 usuários"
    echo "   - 5 produtos"
    echo "   - Relacionamentos configurados"
    echo ""
    print_message "🔧 Comandos úteis:" "$CYAN"
    echo "   Ver logs: docker-compose logs -f"
    echo "   Parar ambiente: docker-compose down"
    echo "   Reconstruir: docker-compose up --build -d"
    echo "   Status: docker-compose ps"
    echo ""
    print_message "📋 Para desenvolvimento:" "$CYAN"
    echo "   Backend: docker-compose exec backend bash"
    echo "   Frontend: docker-compose exec frontend sh"
    echo "   Database: docker-compose exec postgres psql -U laravel_user -d laravel_app"
    echo ""
    print_message "🚀 O ambiente está pronto para uso!" "$GREEN"
}

# Função principal
main() {
    print_header
    
    # Verificações iniciais
    check_dependencies
    check_ports
    create_env_files
    check_and_fix_known_issues
    
    # Configuração do ambiente
    cleanup_environment
    build_and_start
    wait_for_services
    
    # Verificar e instalar Predis
    ensure_predis_installed
    
    # Configuração das aplicações
    configure_laravel
    setup_database
    install_frontend_deps
    
    # Verificações finais
    check_containers_status
    test_connectivity
    show_final_info
}

# Executar função principal
main "$@"
