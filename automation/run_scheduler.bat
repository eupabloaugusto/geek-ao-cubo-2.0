@echo off
:: ===========================================================
:: Geek ao Cubo — Inicializador do Daemon Scheduler Local
:: Duplo-clique para iniciar o agendador de pipeline.
:: ===========================================================

title Geek ao Cubo — Pipeline Scheduler

:: Navega para a pasta do script
cd /d "%~dp0"

echo.
echo  ====================================================
echo   GEEK AO CUBO — PIPELINE SCHEDULER LOCAL
echo  ====================================================
echo   Iniciando daemon... (feche esta janela para parar)
echo  ====================================================
echo.

:: Ativa o ambiente virtual Python se existir
if exist "%~dp0..\venv\Scripts\activate.bat" (
    echo  [INFO] Ativando ambiente virtual Python (venv)...
    call "%~dp0..\venv\Scripts\activate.bat"
) else if exist "%~dp0venv\Scripts\activate.bat" (
    echo  [INFO] Ativando ambiente virtual Python (venv local)...
    call "%~dp0venv\Scripts\activate.bat"
) else (
    echo  [AVISO] Nenhum venv encontrado. Usando Python do sistema.
)

:: Verifica se o APScheduler está instalado
python -c "import apscheduler" 2>nul
if errorlevel 1 (
    echo.
    echo  [ERRO] APScheduler nao encontrado!
    echo  Execute: pip install apscheduler>=3.10.4
    echo.
    pause
    exit /b 1
)

:: Inicia o scheduler (processo bloqueante — mantém a janela aberta)
echo  [INFO] Iniciando scheduler.py...
echo.
python scheduler.py

:: Chegou aqui = daemon foi encerrado (Ctrl+C ou erro)
echo.
echo  [INFO] Scheduler encerrado.
pause
