[CmdletBinding()]
param(
    [string]$OutputDirectory = ''
)

$ErrorActionPreference = 'Stop'
if ([string]::IsNullOrWhiteSpace($OutputDirectory)) {
    $OutputDirectory = Join-Path $PSScriptRoot 'dist'
}
$project = Join-Path $PSScriptRoot 'DSCons.Revit.Launcher.csproj'
$installerProject = Join-Path $PSScriptRoot '..\revit-installer\DSCons.Revit.Installer.csproj'
$installerPayload = Join-Path $PSScriptRoot '..\revit-installer\Payload'
$packageName = 'DSCons-Revit-Launcher-MVP'
$packageRoot = Join-Path $OutputDirectory $packageName
$payloadRoot = Join-Path $packageRoot 'payload'

$profiles = @(
    @{ Version = '2019'; TargetFramework = 'net47' },
    @{ Version = '2021'; TargetFramework = 'net48' },
    @{ Version = '2023'; TargetFramework = 'net48' },
    @{ Version = '2025'; TargetFramework = 'net8.0-windows' },
    @{ Version = '2027'; TargetFramework = 'net10.0-windows' }
)

if (Test-Path -LiteralPath $packageRoot) { Remove-Item -LiteralPath $packageRoot -Recurse -Force }
New-Item -ItemType Directory -Path $payloadRoot -Force | Out-Null

foreach ($profile in $profiles) {
    $version = $profile.Version
    $revitRoot = Join-Path $env:ProgramFiles "Autodesk\Revit $version"
    $api = Join-Path $revitRoot 'RevitAPI.dll'
    $apiUi = Join-Path $revitRoot 'RevitAPIUI.dll'
    if (-not (Test-Path -LiteralPath $api) -or -not (Test-Path -LiteralPath $apiUi)) {
        throw "Revit $version API assemblies are unavailable at $revitRoot."
    }
    $destination = Join-Path $payloadRoot "revit-$version"
    $arguments = @(
        'build', $project, '-c', 'Release', '-o', $destination,
        "-p:RevitVersion=$version",
        "-p:RevitTargetFramework=$($profile.TargetFramework)",
        "-p:RevitApiPath=$api",
        "-p:RevitApiUiPath=$apiUi",
        '-clp:ErrorsOnly'
    )
    & dotnet @arguments
    if ($LASTEXITCODE -ne 0) { throw "Build failed for Revit $version." }
}

Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'Install-DSConsRevitLauncher.ps1') -Destination $packageRoot -Force
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'README.md') -Destination $packageRoot -Force

# Build a self-contained EXE so learners do not need PowerShell, .NET, or a
# separate payload folder. Payload is embedded and extracted per Windows user.
if (Test-Path -LiteralPath $installerPayload) { Remove-Item -LiteralPath $installerPayload -Recurse -Force }
Copy-Item -LiteralPath $payloadRoot -Destination $installerPayload -Recurse -Force
$setupPublish = Join-Path $packageRoot 'setup'
$setupArguments = @(
    'publish', $installerProject, '-c', 'Release', '-r', 'win-x64',
    '--self-contained', 'true',
    '-p:PublishSingleFile=true',
    '-p:IncludeNativeLibrariesForSelfExtract=true',
    '-o', $setupPublish,
    '-clp:ErrorsOnly'
)
& dotnet @setupArguments
if ($LASTEXITCODE -ne 0) { throw 'Could not build the self-contained DSCons setup EXE.' }
$setupExe = Join-Path $setupPublish 'DSCons-Revit-Launcher-Setup.exe'
Copy-Item -LiteralPath $setupExe -Destination (Join-Path $OutputDirectory 'DSCons-Revit-Launcher-Setup.exe') -Force
Copy-Item -LiteralPath $setupExe -Destination $packageRoot -Force

$zipPath = Join-Path $OutputDirectory "$packageName.zip"
if (Test-Path -LiteralPath $zipPath) { Remove-Item -LiteralPath $zipPath -Force }
Compress-Archive -LiteralPath $packageRoot -DestinationPath $zipPath -Force
Write-Host "Package created: $zipPath"
