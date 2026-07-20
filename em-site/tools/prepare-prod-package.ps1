param(
    [string]$SourceWp = "c:\xampp\htdocs\web-am\dev.tad\MyWebsites\ellenemasri.com\www\em-site\wp",
    [string]$OutputDir = "c:\xampp\htdocs\web-am\dev.tad\MyWebsites\ellenemasri.com\www\em-site\documentation\suivi\release-packages",
    [switch]$IncludeAllPlugins
)

$ErrorActionPreference = 'Stop'

if (!(Test-Path $SourceWp)) {
    throw "SourceWp introuvable: $SourceWp"
}

if (!(Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Path $OutputDir | Out-Null
}

$ts = Get-Date -Format "yyyyMMdd_HHmmss"
$workDir = Join-Path $env:TEMP ("em-site-prod-package-" + $ts)
$bundleRoot = Join-Path $workDir "wp"
$zipPath = Join-Path $OutputDir ("em-site-prod-package-" + $ts + ".zip")
$manifestPath = Join-Path $OutputDir ("em-site-prod-package-" + $ts + "-manifest.txt")

if (Test-Path $workDir) {
    Remove-Item -Recurse -Force $workDir
}
New-Item -ItemType Directory -Path $bundleRoot | Out-Null

# Core WP directories
Copy-Item -Recurse -Force (Join-Path $SourceWp "wp-admin") (Join-Path $bundleRoot "wp-admin")
Copy-Item -Recurse -Force (Join-Path $SourceWp "wp-includes") (Join-Path $bundleRoot "wp-includes")

# Core root files except environment-specific files
$excludeRootFiles = @('wp-config.php', '.htaccess', 'web.config')
Get-ChildItem -Path $SourceWp -File | Where-Object { $excludeRootFiles -notcontains $_.Name } | ForEach-Object {
    Copy-Item -Force $_.FullName (Join-Path $bundleRoot $_.Name)
}

# Theme FRONT + BACK
$themeSource = Join-Path $SourceWp "wp-content\themes\em-site"
$themeTarget = Join-Path $bundleRoot "wp-content\themes\em-site"
New-Item -ItemType Directory -Path (Split-Path $themeTarget -Parent) -Force | Out-Null
Copy-Item -Recurse -Force $themeSource $themeTarget

# Optional: include all plugins
if ($IncludeAllPlugins) {
    $pluginsSource = Join-Path $SourceWp "wp-content\plugins"
    if (Test-Path $pluginsSource) {
        $pluginsTarget = Join-Path $bundleRoot "wp-content\plugins"
        New-Item -ItemType Directory -Path (Split-Path $pluginsTarget -Parent) -Force | Out-Null
        Copy-Item -Recurse -Force $pluginsSource $pluginsTarget
    }
}

# Optional: include mu-plugins if present
$muPluginsSource = Join-Path $SourceWp "wp-content\mu-plugins"
if (Test-Path $muPluginsSource) {
    $muPluginsTarget = Join-Path $bundleRoot "wp-content\mu-plugins"
    New-Item -ItemType Directory -Path (Split-Path $muPluginsTarget -Parent) -Force | Out-Null
    Copy-Item -Recurse -Force $muPluginsSource $muPluginsTarget
}

Compress-Archive -Path (Join-Path $bundleRoot "*") -DestinationPath $zipPath -Force

$manifest = @()
$manifest += "Package: $zipPath"
$manifest += "CreatedAt: " + (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
$manifest += "SourceWp: $SourceWp"
$manifest += "Included: wp-admin"
$manifest += "Included: wp-includes"
$manifest += "Included: root files (except wp-config.php, .htaccess, web.config)"
$manifest += "Included: wp-content/themes/em-site"
$manifest += "Included plugins: " + ($(if ($IncludeAllPlugins) { 'ALL' } else { 'NO (theme only + mu-plugins if any)' }))
$manifest += "Included uploads: NO"
$manifest += "Included wp-config.php: NO"
[System.IO.File]::WriteAllLines($manifestPath, $manifest, (New-Object System.Text.UTF8Encoding($false)))

Write-Output "ZIP=$zipPath"
Write-Output "MANIFEST=$manifestPath"
