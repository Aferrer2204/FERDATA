<#
Creates a release folder, copies necessary files and creates a zip, and dumps DB schema.
Usage: .\scripts\create_release.ps1 -Version 1.0.0 -OutputDir .\releases
#>
param(
    [string]$Version = '1.0.0',
    [string]$OutputDir = '.\releases'
)

Write-Host "Creating release $Version..."

$releaseName = "ferdata-$Version"
$releasePath = Join-Path $OutputDir $releaseName
if (!(Test-Path $releasePath)) { New-Item -ItemType Directory -Path $releasePath | Out-Null }

Write-Host 'Copying backend and frontend...'
robocopy .\backend $releasePath\backend /MIR | Out-Null
robocopy .\frontend $releasePath\frontend /MIR | Out-Null
robocopy .\database $releasePath\database /MIR | Out-Null
robocopy .\api $releasePath\api /MIR | Out-Null
robocopy .\docs $releasePath\docs /MIR | Out-Null

# Create DB schema dump if mysqldump exists in PATH
try {
    $dumpFile = Join-Path $releasePath 'database\schema.sql'
    & mysqldump.exe --version > $null 2>&1
    Write-Host 'Creating schema dump...'
    & mysqldump.exe -u root -p --no-data magnatesting_db > $dumpFile
} catch {
    Write-Host 'mysqldump not found or failed. Skipping DB dump.'
}

Write-Host 'Compressing release...'
$zipPath = Join-Path $OutputDir "$releaseName.zip"
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path "$releasePath\*" -DestinationPath $zipPath -Force

Write-Host "Release created: $zipPath"