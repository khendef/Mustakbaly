# Script to find Laragon PHP and add to PATH
Write-Host "Searching for Laragon PHP..." -ForegroundColor Cyan

# Common Laragon installation paths
$searchPaths = @(
    "C:\laragon",
    "D:\laragon",
    "$env:USERPROFILE\laragon",
    "$env:LOCALAPPDATA\laragon",
    "$env:ProgramFiles\laragon",
    "$env:ProgramFiles(x86)\laragon"
)

$laragonPath = $null
$phpPath = $null

# Search for Laragon directory
foreach ($path in $searchPaths) {
    if (Test-Path $path) {
        $phpDir = Join-Path $path "bin\php"
        if (Test-Path $phpDir) {
            $laragonPath = $path
            Write-Host "Found Laragon at: $laragonPath" -ForegroundColor Green
            
            # Find PHP version directories
            $phpVersions = Get-ChildItem $phpDir -Directory | Where-Object { Test-Path (Join-Path $_.FullName "php.exe") }
            if ($phpVersions) {
                $latestPhp = $phpVersions | Sort-Object Name -Descending | Select-Object -First 1
                $phpPath = $latestPhp.FullName
                Write-Host "Found PHP at: $phpPath" -ForegroundColor Green
                break
            }
        }
    }
}

# If not found, search more broadly
if (-not $phpPath) {
    Write-Host "Searching more broadly..." -ForegroundColor Yellow
    $drives = Get-PSDrive -PSProvider FileSystem | Where-Object { $_.Root -match '^[CD]:\\$' }
    foreach ($drive in $drives) {
        $searchPath = Join-Path $drive.Root "laragon\bin\php"
        if (Test-Path $searchPath) {
            $phpVersions = Get-ChildItem $searchPath -Directory -ErrorAction SilentlyContinue | Where-Object { Test-Path (Join-Path $_.FullName "php.exe") }
            if ($phpVersions) {
                $latestPhp = $phpVersions | Sort-Object Name -Descending | Select-Object -First 1
                $phpPath = $latestPhp.FullName
                Write-Host "Found PHP at: $phpPath" -ForegroundColor Green
                break
            }
        }
    }
}

if ($phpPath) {
    Write-Host "`nPHP Path: $phpPath" -ForegroundColor Green
    Write-Host "PHP Version:" -ForegroundColor Cyan
    & "$phpPath\php.exe" -v
    
    # Add to User PATH (doesn't require admin)
    Write-Host "`nAdding to User PATH..." -ForegroundColor Cyan
    $currentUserPath = [Environment]::GetEnvironmentVariable("Path", "User")
    
    if ($currentUserPath -notlike "*$phpPath*") {
        if ([string]::IsNullOrEmpty($currentUserPath)) {
            $newPath = $phpPath
        } else {
            $newPath = $currentUserPath + ";" + $phpPath
        }
        [Environment]::SetEnvironmentVariable("Path", $newPath, "User")
        Write-Host "Successfully added PHP to User PATH!" -ForegroundColor Green
        
        # Also update current session PATH
        $env:PATH += ";$phpPath"
        Write-Host "Updated current session PATH." -ForegroundColor Green
        
        Write-Host "`nPlease restart your PowerShell/terminal for changes to take effect in new sessions." -ForegroundColor Yellow
        
        # Try to add to System PATH (requires admin)
        Write-Host "`nAttempting to add to System PATH (requires admin)..." -ForegroundColor Cyan
        try {
            $currentSystemPath = [Environment]::GetEnvironmentVariable("Path", "Machine")
            if ($currentSystemPath -notlike "*$phpPath*") {
                $newSystemPath = $currentSystemPath + ";" + $phpPath
                [Environment]::SetEnvironmentVariable("Path", $newSystemPath, "Machine")
                Write-Host "Successfully added PHP to System PATH!" -ForegroundColor Green
            } else {
                Write-Host "PHP path already exists in System PATH." -ForegroundColor Yellow
            }
        } catch {
            Write-Host "Could not add to System PATH (admin required). User PATH is sufficient." -ForegroundColor Yellow
            Write-Host "To add to System PATH, run this script as Administrator." -ForegroundColor Gray
        }
    } else {
        Write-Host "PHP path already exists in User PATH." -ForegroundColor Yellow
    }
} else {
    Write-Host "`nLaragon PHP not found automatically." -ForegroundColor Red
    Write-Host "Please provide the full path to your Laragon PHP directory." -ForegroundColor Yellow
    Write-Host "Example: C:\laragon\bin\php\php-8.2.12" -ForegroundColor Gray
}

