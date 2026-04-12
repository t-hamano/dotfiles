# Shared Agent Instructions

## Dotfiles instructions

- For any operation under dotfiles management, prefer `chezmoi` commands.
- Avoid direct edits under `.local/share/chezmoi` unless explicitly requested.
- Standard command flow:
  1. `chezmoi status`
  2. `chezmoi edit <path>` or `chezmoi add <path>`
  3. `chezmoi diff`
  4. `chezmoi apply`
  5. `chezmoi status`

## Commit instructions

- Some environments cannot reliably execute git hooks (pre-commit, commit-msg, etc.). When a commit aborts because the hook tooling itself fails to run due to such environment constraints, retry with `--no-verify`.
- Do not use `--no-verify` to bypass hook *output*. If a hook actually runs and reports a problem, fix the underlying issue instead of skipping it.

## Response style instructions

> Based on [InterfaceX-co-jp/genshijin](https://github.com/InterfaceX-co-jp/genshijin)

簡潔に返答せよ。技術的中身はすべて残す。無駄だけ消す。語尾はですます調を維持する。本ルールは日本語での応答を対象とする。

### 削除対象

- クッション言葉（えーと/まあ/ちなみに/一応/とりあえず/基本的に/ざっくり言うと）
- 前置き（ご質問ありがとうございます/お力になれれば幸いです）
- ぼかし（〜かもしれません/〜と思われます/おそらく）
- 冗長助詞（〜することができる→〜できる、〜というものは→〜は）
- 冗長接続（〜ということになりますので→だから、〜させていただく→します）
- 自明な副詞・形容詞（「基本的に」「一般的な」「適切に」「正しく」）
- マークダウンテーブル — 箇条書きで代替。テーブル記法はトークン浪費
- 情報水増し — 聞かれたことだけ答える。網羅的列挙・補足・派生パターン禁止。質問に対し1パターンだけ答える

### 許可

- 短い同義語（「大規模な」→「大きい」、「実装する」→「作る」）
- 技術用語はそのまま正確に維持
- コードブロックは変更なし
- エラーメッセージは原文のまま引用