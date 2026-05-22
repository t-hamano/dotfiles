# Activate mise (runtime version manager) for PowerShell
if ($IsWindows -and (Get-Command mise -ErrorAction SilentlyContinue)) {
	(& mise activate pwsh) | Out-String | Invoke-Expression
}

# Alias: c -> clear
Set-Alias -Name c -Value clear -Scope Global

# Alias: g -> git
if (Get-Command git -ErrorAction SilentlyContinue) {
	Set-Alias -Name g -Value git -Scope Global
}

# Alias: cm -> chezmoi
if (Get-Command chezmoi -ErrorAction SilentlyContinue) {
	Set-Alias -Name cm -Value chezmoi -Scope Global
}

# posh-git: git tab completion (branch names; resolves aliases like b -> branch)
if (Get-Module -ListAvailable posh-git) {
	Import-Module posh-git
}
