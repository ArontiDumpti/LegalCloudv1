$ErrorActionPreference = "Stop"
Set-Location (Split-Path -Parent $PSScriptRoot)

function Ensure-Gcloud {
    if (Get-Command gcloud -ErrorAction SilentlyContinue) {
        return
    }

    Write-Host "Instalando Google Cloud CLI (solo la primera vez)..."
    winget install -e --id Google.CloudSDK --accept-package-agreements --accept-source-agreements
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path", "Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path", "User")

    if (-not (Get-Command gcloud -ErrorAction SilentlyContinue)) {
        throw "Cierra PowerShell, abre uno nuevo y vuelve a ejecutar: powershell -File scripts\publicar-cloudrun.ps1"
    }
}

Ensure-Gcloud

gcloud config set builds/timeout 1200 | Out-Null

Write-Host ""
Write-Host "1) Se va a abrir el navegador para entrar con tu cuenta de Google."
Write-Host "2) Elige o crea un proyecto (ejemplo: legalcloud-demo)."
Write-Host "3) Si pide facturacion, activala: el mockup puede quedar en 0 USD."
Write-Host ""

gcloud auth login
gcloud init --skip-diagnostics

$proyecto = (gcloud config get-value project)
if (-not $proyecto) {
    throw "No hay proyecto de Google Cloud seleccionado."
}

gcloud services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com --project $proyecto

Write-Host "Publicando LegalCloud en Cloud Run (region barata us-central1)..."
gcloud run deploy legalcloud `
    --source . `
    --region us-central1 `
    --allow-unauthenticated `
    --min-instances 0 `
    --max-instances 1 `
    --memory 512Mi `
    --cpu 1 `
    --project $proyecto

Write-Host ""
Write-Host "Listo. El enlace HTTPS aparece arriba (algo como https://legalcloud-xxxxx-uc.a.run.app)."
Write-Host "Entra con consultor@legalcloud.com / password"
