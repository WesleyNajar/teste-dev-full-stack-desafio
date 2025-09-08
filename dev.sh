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
    print_message "🛠️  FERRAMENTAS DE DESENVOLVIMENTO" "$PURPLE"
    print_message "=================================" "$PURPLE"
    echo ""
}

show_help() {
    print_header
    echo "Comandos disponíveis:"
    echo ""
    echo "  logs          - Ver logs de todos os containers"
    echo "  logs [service] - Ver logs de um serviço específico"
    echo "  restart       - Reiniciar todos os containers"
    echo "  restart [service] - Reiniciar um serviço específico"
    echo "  shell backend - Acessar shell do backend"
    echo "  shell frontend - Acessar shell do frontend"
    echo "  shell db      - Acessar shell do banco de dados"
    echo "  status        - Ver status dos containers"
    echo "  clean         - Limpar containers e volumes"
    echo "  build         - Reconstruir containers"
    echo "  migrate       - Executar migrations"
    echo "  seed          - Executar seeders"
    echo "  test          - Executar testes"
    echo "  help          - Mostrar esta ajuda"
    echo ""
}

case "$1" in
    "logs")
        if [ -n "$2" ]; then
            docker-compose logs -f "$2"
        else
            docker-compose logs -f
        fi
        ;;
    "restart")
        if [ -n "$2" ]; then
            docker-compose restart "$2"
        else
            docker-compose restart
        fi
        ;;
    "shell")
        case "$2" in
            "backend")
                docker-compose exec backend bash
                ;;
            "frontend")
                docker-compose exec frontend sh
                ;;
            "db")
                docker-compose exec postgres psql -U laravel_user -d laravel_app
                ;;
            *)
                echo "Uso: $0 shell [backend|frontend|db]"
                ;;
        esac
        ;;
    "status")
        docker-compose ps
        ;;
    "clean")
        print_message "🧹 Limpando containers e volumes..." "$YELLOW"
        docker-compose down --volumes --remove-orphans
        docker system prune -f
        print_message "✅ Limpeza concluída!" "$GREEN"
        ;;
    "build")
        print_message "🔨 Reconstruindo containers..." "$BLUE"
        docker-compose up --build -d
        print_message "✅ Containers reconstruídos!" "$GREEN"
        ;;
    "migrate")
        print_message "🗄️ Executando migrations..." "$BLUE"
        docker-compose exec backend php artisan migrate
        print_message "✅ Migrations executadas!" "$GREEN"
        ;;
    "seed")
        print_message "🌱 Executando seeders..." "$BLUE"
        docker-compose exec backend php artisan db:seed
        print_message "✅ Seeders executados!" "$GREEN"
        ;;
    "test")
        print_message "🧪 Executando testes..." "$BLUE"
        docker-compose exec backend php artisan test
        ;;
    "help"|"")
        show_help
        ;;
    *)
        print_message "❌ Comando não reconhecido: $1" "$RED"
        echo ""
        show_help
        ;;
esac
