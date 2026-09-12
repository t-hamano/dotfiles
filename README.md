# Dotfiles Settings Summary

This repository manages personal dotfiles with [chezmoi](https://www.chezmoi.io/).

## Preconditions

Required:

- [chezmoi](https://www.chezmoi.io/) is installed.
- [mise](https://mise.jdx.dev/) is installed.
- Git is installed.
- GitHub CLI (`gh`) is installed and available in PATH.
- `gh auth login` has been completed.

## How to use

```sh
chezmoi init --apply t-hamano/dotfiles  # first-time setup
chezmoi edit ~/.zshrc                   # edit a managed file
chezmoi diff                            # preview changes
chezmoi apply                           # apply changes
chezmoi add ~/.foo                      # add a new file
chezmoi update                          # pull and apply remote changes
```

On first run, you will be prompted for your Git email and name.

## Configurations

### Git

Manages user identity, commonly used aliases, and OS-specific line-ending and credential settings.

- chezmoi path: `dot_gitconfig.tmpl`
- target path: `~/.gitconfig`

### WSL

Windows host OS only: manages WSL2 resource allocation such as memory, CPU cores, and swap.

- chezmoi path: `dot_wslconfig`
- target path: `~/.wslconfig`

### Zsh

Manages zsh shell startup files and shared aliases. Uses [Oh My Zsh](https://ohmyz.sh/) as the plugin framework with [Powerlevel10k](https://github.com/romkatv/powerlevel10k) theme and [zsh-syntax-highlighting](https://github.com/zsh-users/zsh-syntax-highlighting) plugin. These are fetched automatically via `.chezmoiexternal.toml`.

- chezmoi paths:
  - `dot_profile` → `~/.profile`
  - `dot_zprofile` → `~/.zprofile`
  - `dot_zshrc` → `~/.zshrc`
  - `dot_aliases` → `~/.aliases`
  - `dot_p10k.zsh` → `~/.p10k.zsh`

### mise

Manages runtime and tool version pinning for development. See `dot_config/mise/config.toml` for the current list.

- chezmoi path: `dot_config/mise/`
- target path: `~/.config/mise/`

### PowerShell

Windows host OS only: manages startup configuration to activate mise automatically in PowerShell.

- chezmoi path: `readonly_Documents/PowerShell/`
- target path: `~/Documents/PowerShell/`

### Claude

Manages Claude agent behavior rules, permissions, and custom skills. `CLAUDE.md` references `AGENTS.md` via Claude's `@` include syntax for shared rules. Includes permission settings (`settings.json.tmpl`) and custom skills.

- chezmoi path: `dot_claude/`
- target path: `~/.claude/`

### Shared agent skills

The skills in `dot_agents/skills/` are the common configuration for Windows,
WSL, and macOS. Run `chezmoi apply` in each environment to install them into
that environment's `~/.agents/skills/`. WSL uses its own Linux home directory.

Codex reads `~/.agents/skills/` directly. The scripts in `.chezmoiscripts/`
only create `~/.claude/skills` as a directory junction on Windows or a symbolic
link on WSL/macOS/Linux. Windows junctions require neither Developer Mode nor
administrator privileges. An existing ordinary directory at this path must be
removed before the link can be created.

Edit shared skills with `chezmoi edit ~/.agents/skills/<name>/SKILL.md`. Add new
skills with `chezmoi add ~/.agents/skills/<name>`, then run `chezmoi diff` and
`chezmoi apply`. Commit and push changes, then use `chezmoi update` in the
other environments to apply the same skills.

### Copilot

Manages shared instructions for GitHub Copilot. The template embeds `AGENTS.md` via chezmoi's `{{ include }}` for shared rules.

- chezmoi path: `dot_github/`
- target path: `~/.github/`

### Hammerspoon

macOS only: manages [Hammerspoon](https://www.hammerspoon.org/) configuration for macOS-level key remapping. Used for two purposes: remapping keys on the Mac itself, and remapping keys sent from Windows via [Deskflow](https://github.com/deskflow/deskflow).

- chezmoi path: `dot_hammerspoon/`
- target path: `~/.hammerspoon/`
