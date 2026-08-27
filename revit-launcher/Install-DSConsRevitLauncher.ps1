[CmdletBinding(SupportsShouldProcess)]
param(
    [Parameter(Mandatory = $false)]
    [ValidatePattern('^https?://')]
    [string]$ApiBaseUrl = 'https://YOUR-DSCONS-DOMAIN',
    [switch]$Uninstall
)

$ErrorActionPreference = 'Stop'
$launcherName = 'DSCons.Revit.Launcher'
$vendorRoot = Join-Path $env:LOCALAPPDATA 'DSCons\RevitLauncher'
$payloadRoot = Join-Path $PSScriptRoot 'payload'

# Versions sharing a payload are compatibility candidates until their own
# in-Revit runtime check is recorded; this installer never calls them certified.
$sourceByRevitVersion = @{
    '2019' = '2019'; '2020' = '2019'
    '2021' = '2021'; '2022' = '2021'
    '2023' = '2023'; '2024' = '2023'
    '2025' = '2025'; '2026' = '2025'
    '2027' = '2027'
}

function Get-RevitAddinPath([string]$version) {
    Join-Path $env:APPDATA "Autodesk\Revit\Addins\$version\$launcherName.addin"
}

function Write-RevitManifest([string]$path, [string]$assemblyPath) {
    $xml = @"
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<RevitAddIns>
  <AddIn Type="Application">
    <Name>DSCons Revit Launcher</Name>
    <Assembly>$([System.Security.SecurityElement]::Escape($assemblyPath))</Assembly>
    <AddInId>8B4F7C0D-7970-4A5F-9B8B-8B2A1D4D1352</AddInId>
    <FullClassName>DSCons.Revit.Launcher.App</FullClassName>
    <VendorId>DSCONS</VendorId>
    <VendorDescription>DSCons Viet Nam</VendorDescription>
  </AddIn>
</RevitAddIns>
"@
    Set-Content -LiteralPath $path -Value $xml -Encoding UTF8
}

$changed = @()
foreach ($version in 2019..2027 | ForEach-Object { $_.ToString() }) {
    $revitExe = Join-Path $env:ProgramFiles "Autodesk\Revit $version\Revit.exe"
    $manifestPath = Get-RevitAddinPath $version

    if ($Uninstall) {
        if (Test-Path -LiteralPath $manifestPath -and $PSCmdlet.ShouldProcess($manifestPath, 'Remove DSCons Revit add-in manifest')) {
            Remove-Item -LiteralPath $manifestPath -Force
            $changed += "Removed manifest for Revit $version"
        }
        continue
    }
    if (-not (Test-Path -LiteralPath $revitExe)) { continue }

    $sourceVersion = $sourceByRevitVersion[$version]
    $sourcePath = Join-Path $payloadRoot "revit-$sourceVersion"
    $sourceDll = Join-Path $sourcePath "$launcherName.dll"
    if (-not (Test-Path -LiteralPath $sourceDll)) {
        Write-Warning "Skip Revit $($version): payload $sourceVersion is missing."
        continue
    }

    $targetPath = Join-Path $vendorRoot "versions\$version"
    $targetDll = Join-Path $targetPath "$launcherName.dll"
    if ($PSCmdlet.ShouldProcess("Revit $version", 'Install DSCons Revit Launcher')) {
        New-Item -ItemType Directory -Path $targetPath -Force | Out-Null
        Copy-Item -Path (Join-Path $sourcePath '*') -Destination $targetPath -Recurse -Force
        Set-Content -LiteralPath (Join-Path $targetPath "$launcherName.config") -Value "DSCONS_API_BASE_URL=$ApiBaseUrl" -Encoding UTF8
        New-Item -ItemType Directory -Path (Split-Path $manifestPath -Parent) -Force | Out-Null
        Write-RevitManifest $manifestPath $targetDll
        $changed += "Installed for Revit $version (payload $sourceVersion)"
    }
}

if ($changed.Count -eq 0) {
    Write-Warning 'No installed Revit version was changed. Run this from the extracted DSCons package on a machine with Revit installed.'
} else {
    $changed | ForEach-Object { Write-Host $_ }
    if (-not $Uninstall) { Write-Host 'Open Revit, then find the DSCons tab. First use: click Đăng nhập DSCons.' }
}
