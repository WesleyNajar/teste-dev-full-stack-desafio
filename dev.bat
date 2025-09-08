@echo off
setlocal enabledelayedexpansion

REM Script de desenvolvimento para Windows
if "%1"=="" goto help
if "%1"=="help" goto help
if "%1"=="logs" goto logs
if "%1"=="restart" goto restart
if "%1"=="shell" goto shell
if "%1"=="status" goto status
if "%1"=="clean" goto clean
if "%1"=="build" goto build
if "%1"=="migrate" goto migrate
if "%1"=="seed" goto seed
if "%1"=="test" goto test
goto unknown

:help
echo.
echo 🛠️  FERRAMENTAS DE DESENVOLVIMENTO
echo =================================
echo.
echo Comandos disponíveis:
echo.
echo   logs          - Ver logs de todos os containers
echo   logs [service] - Ver logs de um serviço específico
echo   restart       - Reiniciar todos os containers
echo   restart [service] - Reiniciar um serviço específico
echo   shell backend - Acessar shell do backend
echo   shell frontend - Acessar shell do frontend
echo   shell db      - Acessar shell do banco de dados
echo   status        - Ver status dos containers
echo   clean         - Limpar containers e volumes
echo   build         - Reconstruir containers
echo   migrate       - Executar migrations
echo   seed          - Executar seeders
echo   test          - Executar testes
echo   help          - Mostrar esta ajuda
echo.
goto end

:logs
if "%2"=="" (
    docker-compose logs -f
) else (
    docker-compose logs -f %2
)
goto end

:restart
if "%2"=="" (
    docker-compose restart
) else (
    docker-compose restart %2
)
goto end

:shell
if "%2"=="backend" (
    docker-compose exec backend bash
) else if "%2"=="frontend" (
    docker-compose exec frontend sh
) else if "%2"=="db" (
    docker-compose exec postgres psql -U laravel_user -d laravel_app
) else (
    echo Uso: %0 shell [backend^|frontend^|db]
)
goto end

:status
docker-compose ps
goto end

:clean
echo 🧹 Limpando containers e volumes...
docker-compose down --volumes --remove-orphans
docker system prune -f
echo ✅ Limpeza concluída!
goto end

:build
echo 🔨 Reconstruindo containers...
docker-compose up --build -d
echo ✅ Containers reconstruídos!
goto end

:migrate
echo 🗄️ Executando migrations...
docker-compose exec backend php artisan migrate
echo ✅ Migrations executadas!
goto end

:seed
echo 🌱 Executando seeders...
docker-compose exec backend php artisan db:seed
echo ✅ Seeders executados!
goto end

:test
echo 🧪 Executando testes...
docker-compose exec backend php artisan test
goto end

:unknown
echo ❌ Comando não reconhecido: %1
echo.
goto help

:end
