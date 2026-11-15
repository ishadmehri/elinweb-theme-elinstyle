[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$Repository,
    [Parameter(Mandatory = $true)][ValidateSet('design', 'frontend', 'wordpress', 'manager')][string]$Role,
    [ValidateSet('Preview', 'Commit', 'Push')][string]$Action = 'Preview',
    [string]$Message,
    [string]$Remote = 'origin'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Invoke-RepoGit {
    param([string[]]$GitArguments)
    $output = @(& git -C $script:repoPath @GitArguments 2>&1)
    if ($LASTEXITCODE -ne 0) {
        throw "Git failed ($($GitArguments[0])): $($output -join [Environment]::NewLine)"
    }
    return $output
}

function Test-OwnedPath {
    param([string]$Path)
    foreach ($allowed in $script:ownedPaths) {
        if ($Path -ceq $allowed -or $Path.StartsWith($allowed + '/', [StringComparison]::Ordinal)) { return $true }
    }
    return $false
}

function Get-StagedPaths {
    $raw = (Invoke-RepoGit -GitArguments @('diff', '--cached', '--name-only', '-z', '--no-renames')) -join "`n"
    return @($raw.Split([char]0, [StringSplitOptions]::RemoveEmptyEntries))
}

$repoPath = (Resolve-Path -LiteralPath $Repository).Path
$top = (Invoke-RepoGit -GitArguments @('rev-parse', '--show-toplevel')) -join ''
if ([IO.Path]::GetFullPath($top).TrimEnd('\', '/') -ine $repoPath.TrimEnd('\', '/')) {
    throw 'Repository must be the exact Git repository root, not a folder inside another repository.'
}
$manifestPath = Join-Path $repoPath '.template-publish.json'
$manifest = Get-Content -LiteralPath $manifestPath -Raw -Encoding UTF8 | ConvertFrom-Json
$roleProperty = $manifest.roles.PSObject.Properties[$Role]
if ($null -eq $roleProperty) { throw 'Role is not configured in .template-publish.json.' }
$ownedPaths = @($roleProperty.Value.paths)
if ($ownedPaths.Count -eq 0) { throw 'Role must own at least one path.' }
foreach ($path in $ownedPaths) {
    if ($path -notmatch '^[A-Za-z0-9_.-]+(/[A-Za-z0-9_.-]+)*$' -or
        @($path.Split('/')) -contains '..' -or $path -eq '.git' -or $path.StartsWith('.git/')) {
        throw "Invalid owned path: $path"
    }
    $candidate = Join-Path $repoPath $path
    # Git stages a link itself rather than its target. Refuse links to keep ownership unambiguous.
    $current = $repoPath
    foreach ($segment in $path.Split('/')) {
        $current = Join-Path $current $segment
        if (Test-Path -LiteralPath $current) {
            if (((Get-Item -LiteralPath $current -Force).Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) {
                throw "Owned paths cannot pass through a junction or symbolic link: $path"
            }
        }
    }
}

if ($Action -eq 'Preview') {
    Write-Output "Role: $Role; preview only. No staging, commit, or push."
    Invoke-RepoGit -GitArguments (@('status', '--short', '--untracked-files=all', '--') + $ownedPaths)
    exit 0
}

if ([string]::IsNullOrWhiteSpace($Message)) { throw 'Commit and Push require an explicit -Message.' }
if ($Remote -notmatch '^[A-Za-z0-9_.-]+$' -or $Remote.StartsWith('-')) { throw 'Invalid remote name.' }

$commonDir = (Invoke-RepoGit -GitArguments @('rev-parse', '--git-common-dir')) -join ''
if (-not [IO.Path]::IsPathRooted($commonDir)) { $commonDir = Join-Path $repoPath $commonDir }
$lockPath = Join-Path ([IO.Path]::GetFullPath($commonDir)) 'template-publish.lock'
$lock = $null
$commitMade = $false
$commitHash = ''
try {
    try {
        $lock = [IO.File]::Open($lockPath, [IO.FileMode]::OpenOrCreate, [IO.FileAccess]::ReadWrite, [IO.FileShare]::None)
    } catch {
        throw 'Another publishing process holds the shared Git lock. Retry when it finishes.'
    }
    # Keep the lock file: deleting it can cause a race on Unix. Only the open handle locks it.
    $branch = (Invoke-RepoGit -GitArguments @('symbolic-ref', '--quiet', '--short', 'HEAD')) -join ''
    foreach ($state in @('MERGE_HEAD', 'CHERRY_PICK_HEAD', 'REVERT_HEAD', 'rebase-merge', 'rebase-apply', 'sequencer', 'index.lock')) {
        $statePath = (Invoke-RepoGit -GitArguments @('rev-parse', '--git-path', $state)) -join ''
        if (-not [IO.Path]::IsPathRooted($statePath)) { $statePath = Join-Path $repoPath $statePath }
        if (Test-Path -LiteralPath $statePath) { throw "Finish the existing Git operation first: $state" }
    }
    $alreadyStaged = @(Get-StagedPaths)
    if ($alreadyStaged.Count -gt 0) {
        throw 'The Git index already contains staged changes. Commit or unstage them yourself before running this script.'
    }
    if ($Action -eq 'Push') {
        $remotes = @(Invoke-RepoGit -GitArguments @('remote'))
        if ($remotes -notcontains $Remote) { throw "Remote '$Remote' is not configured. No commit was created." }
    }
    $stagePaths = @()
    foreach ($path in $ownedPaths) {
        if ((Test-Path -LiteralPath (Join-Path $repoPath $path)) -or
            @(Invoke-RepoGit -GitArguments @('ls-files', '--', $path)).Count -gt 0) {
            $stagePaths += $path
        }
    }
    if ($stagePaths.Count -gt 0) {
        Invoke-RepoGit -GitArguments (@('add', '-A', '--') + $stagePaths) | Out-Null
    }
    $staged = @(Get-StagedPaths)
    foreach ($path in $staged) {
        if (-not (Test-OwnedPath -Path $path)) {
            throw "Unexpected staged path outside role ownership: $path. Inspect the index manually."
        }
    }
    if ($staged.Count -gt 0) {
        Invoke-RepoGit -GitArguments @('commit', '-m', "${Role}: $Message")
        $commitMade = $true
        $commitHash = (Invoke-RepoGit -GitArguments @('rev-parse', '--short', 'HEAD')) -join ''
        Write-Output "Committed $($staged.Count) path(s) for $Role. Commit: $commitHash"
    } else {
        Write-Output 'No changes in this role scope; no new commit.'
    }
    if ($Action -eq 'Push') {
        # No force, automatic pull, branch switching, reset, stash, or conflict resolution.
        Invoke-RepoGit -GitArguments @('push', $Remote, "HEAD:refs/heads/$branch")
        Write-Output "Push completed on branch $branch."
    }
} catch {
    if ($commitMade) { Write-Warning "Local commit $commitHash is preserved. If push reported an error, verify the remote state before retrying." }
    throw
} finally {
    if ($null -ne $lock) { $lock.Dispose() }
}
