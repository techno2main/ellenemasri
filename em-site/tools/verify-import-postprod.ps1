param(
    [string]$SourceDb = 'em_site_bdd',
    [string]$VerifyDb = 'em_site_bdd_verify',
    [string]$SourceUser = 'uem_site_user',
    [string]$SourcePass = 'pem_site_pass',
    [string]$VerifyUser = 'root',
    [string]$VerifyPass = 'pem_site_root_pass',
    [string]$Container = 'em-site-local-db',
    [string]$SourceDump
)

$ErrorActionPreference = 'Stop'

function Invoke-Db([string]$Query, [string]$User, [string]$Pass, [string]$Db) {
    $args = @('exec', $Container, 'mariadb', "-u$User", "-p$Pass", '-N', '-B', '-e', $Query)
    $out = & docker @args
    if ($LASTEXITCODE -ne 0) {
        throw "Commande SQL échouée: $Query"
    }
    return ($out | Out-String).Trim()
}

function Get-Count([string]$Db, [string]$User, [string]$Pass, [string]$Sql) {
    return Invoke-Db -Query "USE $Db; $Sql" -User $User -Pass $Pass -Db $Db
}

if ($SourceDump -and (Test-Path $SourceDump)) {
    Write-Output "DUMP_OK $SourceDump"
}

$checks = @(
    @{ Name = 'options';  Query = "SELECT COUNT(*) FROM wpem_options WHERE option_value LIKE '%localhost:8290%' OR option_value LIKE '%127.0.0.1%' OR option_value LIKE '%localhost%';" },
    @{ Name = 'postmeta'; Query = "SELECT COUNT(*) FROM wpem_postmeta WHERE meta_value LIKE '%localhost:8290%' OR meta_value LIKE '%127.0.0.1%' OR meta_value LIKE '%localhost%';" },
    @{ Name = 'posts';    Query = "SELECT COUNT(*) FROM wpem_posts WHERE post_content LIKE '%localhost:8290%' OR post_content LIKE '%127.0.0.1%' OR post_content LIKE '%localhost%';" }
)

$results = @()
foreach ($check in $checks) {
    $count = Get-Count -Db $VerifyDb -User $VerifyUser -Pass $VerifyPass -Sql $check.Query
    $results += [pscustomobject]@{
        table = $check.Name
        count = [int]$count
    }
}

$tables = Invoke-Db -Query "USE $SourceDb; SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='$SourceDb' ORDER BY TABLE_NAME;" -User $SourceUser -Pass $SourcePass -Db $SourceDb
$sourceTableCount = @($tables -split "`n" | Where-Object { $_.Trim() }).Count
$verifyTableCount = Invoke-Db -Query "USE $VerifyDb; SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='$VerifyDb';" -User $VerifyUser -Pass $VerifyPass -Db $VerifyDb

Write-Output "TABLES_SRC=$sourceTableCount"
Write-Output "TABLES_VERIFY=$verifyTableCount"

foreach ($result in $results) {
    Write-Output "LOCALHOST_$($result.table.ToUpper())=$($result.count)"
}

if (($results | Where-Object { $_.count -gt 0 }).Count -gt 0) {
    exit 2
}

Write-Output 'VERIFY_OK'
