#Requires -Version 5.1
<#
.SYNOPSIS
    Geek ao Cubo — Registra o Pipeline Scheduler como tarefa do Windows Task Scheduler.

.DESCRIPTION
    Cria uma tarefa agendada no Windows que inicia o daemon scheduler.py
    automaticamente quando o usuário fizer login no sistema.
    Execute este script como Administrador uma única vez para configurar.

.USAGE
    powershell -ExecutionPolicy Bypass -File setup_windows_task.ps1

    Para remover a tarefa:
    powershell -ExecutionPolicy Bypass -File setup_windows_task.ps1 -Remove

.PARAMETER Remove
    Se presente, remove a tarefa agendada existente em vez de criá-la.
#>

param(
    [switch]$Remove
)

# ---------------------------------------------------------------------------
# Configuração
# ---------------------------------------------------------------------------
$TaskName    = "GeekAoCubo_PipelineScheduler"
$TaskDesc    = "Geek ao Cubo — Daemon de Automação do Pipeline Editorial (modomaratona.com)"
$ScriptDir   = Split-Path -Parent $MyInvocation.MyCommand.Path
$BatchScript = Join-Path $ScriptDir "run_scheduler.bat"

# ---------------------------------------------------------------------------
# Verifica privilégios de Administrador
# ---------------------------------------------------------------------------
$IsAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole(
    [Security.Principal.WindowsBuiltInRole]::Administrator
)

if (-not $IsAdmin) {
    Write-Host ""
    Write-Host "  =============================================" -ForegroundColor Yellow
    Write-Host "   AVISO: Execute este script como Administrador" -ForegroundColor Yellow
    Write-Host "  =============================================" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  Clique com botão direito no PowerShell > 'Executar como administrador'"
    Write-Host "  e rode novamente:" -ForegroundColor White
    Write-Host "    powershell -ExecutionPolicy Bypass -File `"$($MyInvocation.MyCommand.Path)`"" -ForegroundColor Cyan
    Write-Host ""
    Read-Host "  Pressione Enter para sair"
    exit 1
}

# ---------------------------------------------------------------------------
# Modo Remoção
# ---------------------------------------------------------------------------
if ($Remove) {
    Write-Host ""
    Write-Host "  Removendo tarefa '$TaskName' do Agendador de Tarefas..." -ForegroundColor Cyan
    try {
        Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false -ErrorAction Stop
        Write-Host "  [OK] Tarefa removida com sucesso." -ForegroundColor Green
    } catch {
        Write-Host "  [AVISO] Tarefa '$TaskName' não encontrada ou erro ao remover: $_" -ForegroundColor Yellow
    }
    Write-Host ""
    Read-Host "  Pressione Enter para sair"
    exit 0
}

# ---------------------------------------------------------------------------
# Verifica se o batch script existe
# ---------------------------------------------------------------------------
if (-not (Test-Path $BatchScript)) {
    Write-Host ""
    Write-Host "  [ERRO] run_scheduler.bat não encontrado em:" -ForegroundColor Red
    Write-Host "    $BatchScript" -ForegroundColor Red
    Write-Host "  Certifique-se de que o arquivo existe antes de executar este script." -ForegroundColor White
    Write-Host ""
    Read-Host "  Pressione Enter para sair"
    exit 1
}

# ---------------------------------------------------------------------------
# Criação da Tarefa Agendada
# ---------------------------------------------------------------------------
Write-Host ""
Write-Host "  =====================================================" -ForegroundColor Cyan
Write-Host "   GEEK AO CUBO — CONFIGURANDO WINDOWS TASK SCHEDULER" -ForegroundColor Cyan
Write-Host "  =====================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Tarefa:  $TaskName"
Write-Host "  Script:  $BatchScript"
Write-Host "  Gatilho: Login do usuário atual"
Write-Host ""

# Remove tarefa anterior com mesmo nome (se existir)
$existing = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
if ($existing) {
    Write-Host "  [INFO] Tarefa existente encontrada. Removendo para recriar..." -ForegroundColor Yellow
    Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
}

# Define o gatilho: ao fazer logon
$Trigger = New-ScheduledTaskTrigger -AtLogOn

# Define a ação: cmd /c para executar o .bat em background (janela minimizada)
$Action = New-ScheduledTaskAction `
    -Execute "cmd.exe" `
    -Argument "/c `"$BatchScript`"" `
    -WorkingDirectory $ScriptDir

# Configurações de execução
$Settings = New-ScheduledTaskSettingsSet `
    -ExecutionTimeLimit (New-TimeSpan -Hours 0) `
    -RestartCount 3 `
    -RestartInterval (New-TimeSpan -Minutes 5) `
    -StartWhenAvailable `
    -RunOnlyIfNetworkAvailable:$false `
    -MultipleInstances IgnoreNew

# Principal (usuário atual)
$Principal = New-ScheduledTaskPrincipal `
    -UserId ([System.Security.Principal.WindowsIdentity]::GetCurrent().Name) `
    -LogonType Interactive `
    -RunLevel Highest

# Registra a tarefa
try {
    Register-ScheduledTask `
        -TaskName $TaskName `
        -Description $TaskDesc `
        -Trigger $Trigger `
        -Action $Action `
        -Settings $Settings `
        -Principal $Principal `
        -Force `
        -ErrorAction Stop

    Write-Host "  [OK] Tarefa '$TaskName' registrada com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "  O Scheduler iniciará automaticamente no próximo login." -ForegroundColor White
    Write-Host ""
    Write-Host "  Para iniciar imediatamente sem reiniciar, use:" -ForegroundColor Yellow
    Write-Host "    Start-ScheduledTask -TaskName '$TaskName'" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  Para remover a tarefa futuramente:" -ForegroundColor Yellow
    Write-Host "    powershell -ExecutionPolicy Bypass -File `"$($MyInvocation.MyCommand.Path)`" -Remove" -ForegroundColor Cyan

} catch {
    Write-Host "  [ERRO] Falha ao registrar a tarefa: $_" -ForegroundColor Red
    Write-Host "  Verifique se possui permissões de Administrador." -ForegroundColor Yellow
}

Write-Host ""
Read-Host "  Pressione Enter para sair"
