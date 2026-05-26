@echo off
:: ===========================================================
:: Geek ao Cubo — Parador Seguro do Daemon Scheduler Local
:: Lê o PID do lock file e encerra o processo com segurança.
:: ===========================================================

title Geek ao Cubo — Stop Scheduler

cd /d "%~dp0"

set "LOCK_FILE=%~dp0scheduler.lock"

echo.
echo  ====================================================
echo   GEEK AO CUBO — PARANDO O PIPELINE SCHEDULER
echo  ====================================================
echo.

:: Verifica se o lock file existe
if not exist "%LOCK_FILE%" (
    echo  [AVISO] Nenhum lock file encontrado em:
    echo    %LOCK_FILE%
    echo.
    echo  O Scheduler pode nao estar em execucao.
    pause
    exit /b 0
)

:: Lê o PID do lock file
set /p SCHED_PID=<"%LOCK_FILE%"

if "%SCHED_PID%"=="" (
    echo  [ERRO] Lock file vazio. Removendo e saindo.
    del "%LOCK_FILE%"
    pause
    exit /b 1
)

echo  [INFO] PID do Scheduler: %SCHED_PID%
echo  [INFO] Encerrando processo...
echo.

:: Tenta encerrar o processo pelo PID
taskkill /PID %SCHED_PID% /F 2>nul
if errorlevel 1 (
    echo  [AVISO] Processo %SCHED_PID% nao encontrado (pode ja ter sido encerrado).
) else (
    echo  [OK] Processo %SCHED_PID% encerrado com sucesso.
)

:: Remove o lock file residual se ainda existir
if exist "%LOCK_FILE%" (
    del "%LOCK_FILE%"
    echo  [OK] Lock file removido.
)

echo.
echo  Scheduler parado. Ate logo!
echo.
pause
