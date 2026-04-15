#!/usr/bin/env node
'use strict';

/**
 * Claude Code PreToolUse hook: pipe-permission.js
 *
 * Auto-approves Bash commands that contain pipes (|), &&, ||, ;
 * when every segment matches the allow list in ~/.claude/settings.json.
 *
 * Works on macOS, Linux/WSL, and Windows (Node.js required).
 */

const fs = require('fs');
const os = require('os');
const path = require('path');

// Patterns that should never be auto-approved regardless of allow list
const DANGEROUS_PATTERNS = [
  // Environment variable injections
  /\bPATH\s*=/,
  /\bLD_PRELOAD\s*=/,
  /\bLD_LIBRARY_PATH\s*=/,
  /\bDYLD_INSERT_LIBRARIES\s*=/,
  // Command substitution — executes arbitrary code inside an otherwise-approved segment
  /\$\(/,   // $(...)
  /`/,      // `...` backtick substitution
  // Process substitution — opens a subshell as a file descriptor
  /<\(/,    // <(...)
  />\(/,    // >(...)
];

function readAllInput() {
  return new Promise((resolve) => {
    let data = '';
    process.stdin.setEncoding('utf8');
    process.stdin.on('data', (chunk) => { data += chunk; });
    process.stdin.on('end', () => resolve(data));
    process.stdin.on('error', () => resolve(''));
  });
}

function loadSettings() {
  const settingsPath = path.join(os.homedir(), '.claude', 'settings.json');
  try {
    return JSON.parse(fs.readFileSync(settingsPath, 'utf8'));
  } catch {
    return null;
  }
}

/**
 * Split a shell command into segments by |, &&, ||, ;
 * while respecting single-quoted and double-quoted strings.
 */
function splitCommand(cmd) {
  const segments = [];
  let current = '';
  let inSingle = false;
  let inDouble = false;
  let i = 0;

  while (i < cmd.length) {
    const c = cmd[i];
    const next = cmd[i + 1];

    if (c === "'" && !inDouble) {
      inSingle = !inSingle;
      current += c;
    } else if (c === '"' && !inSingle) {
      inDouble = !inDouble;
      current += c;
    } else if (!inSingle && !inDouble) {
      // ||
      if (c === '|' && next === '|') {
        if (current.trim()) segments.push(current.trim());
        current = '';
        i += 2;
        continue;
      }
      // &&
      if (c === '&' && next === '&') {
        if (current.trim()) segments.push(current.trim());
        current = '';
        i += 2;
        continue;
      }
      // | (pipe)
      if (c === '|') {
        if (current.trim()) segments.push(current.trim());
        current = '';
        i++;
        continue;
      }
      // ;
      if (c === ';') {
        if (current.trim()) segments.push(current.trim());
        current = '';
        i++;
        continue;
      }
      current += c;
    } else {
      current += c;
    }
    i++;
  }

  if (current.trim()) segments.push(current.trim());
  return segments;
}

/**
 * Test whether a command segment matches a Claude Code allow-list pattern.
 * In patterns, * is a wildcard matching any sequence of characters.
 */
function matchPattern(cmd, pattern) {
  // Escape all regex special chars except *
  const escaped = pattern.replace(/[.+^${}()\[\]\\]/g, '\\$&');
  const regexStr = '^' + escaped.replace(/\*/g, '.*') + '$';
  try {
    return new RegExp(regexStr).test(cmd);
  } catch {
    return false;
  }
}

function matchesAny(cmd, patterns) {
  return patterns.some((p) => matchPattern(cmd, p));
}

async function main() {
  let input;
  try {
    const raw = await readAllInput();
    input = JSON.parse(raw);
  } catch {
    process.exit(0);
  }

  // Only handle Bash tool
  if (input.tool_name !== 'Bash') process.exit(0);

  const command = input.tool_input?.command;
  if (!command || typeof command !== 'string') process.exit(0);

  // Reject dangerous environment-variable injections
  for (const pattern of DANGEROUS_PATTERNS) {
    if (pattern.test(command)) process.exit(0);
  }

  const settings = loadSettings();
  if (!settings) process.exit(0);

  const allow = (settings.permissions?.allow || [])
    .filter((p) => typeof p === 'string' && p.startsWith('Bash(') && p.endsWith(')'))
    .map((p) => p.slice(5, -1));

  const deny = (settings.permissions?.deny || [])
    .filter((p) => typeof p === 'string' && p.startsWith('Bash(') && p.endsWith(')'))
    .map((p) => p.slice(5, -1));

  const segments = splitCommand(command);

  // Single-segment commands: let the built-in permission system handle them
  if (segments.length <= 1) process.exit(0);

  // Deny list takes priority over allow list
  for (const seg of segments) {
    if (matchesAny(seg, deny)) process.exit(0);
  }

  // Every segment must match the allow list
  for (const seg of segments) {
    if (!matchesAny(seg, allow)) process.exit(0);
  }

  // All segments are allowed — approve automatically
  process.stdout.write(JSON.stringify({ decision: 'approve' }));
  process.exit(0);
}

main().catch(() => process.exit(0));
