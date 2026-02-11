# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

WordPress plugin die met AI (Claude, OpenAI, Gemini) content genereert voor pagina's en post types. Vult ACF velden (inclusief flexible content layouts) via post meta. Classic editor, geen Gutenberg.

## Commands

```bash
composer install          # PHP dependencies installeren
composer dump-autoload    # Autoloader regenereren na nieuwe classes
```

Geen test suite of linter geconfigureerd. Plugin testen door te activeren in WordPress.

## Architecture

PHP 8.0+, OOP met PSR-4 autoloading via Composer. Namespace: `Draad\ContentGenerator\`.

### Key Components

- **`Plugin`** (`src/Plugin.php`) — Singleton, bootstrap. Registreert admin menu's en enqueues assets.
- **`Admin\SettingsPage`** — WordPress Settings API. Slaat API keys en default provider op in `wp_options` (`dcg_claude_api_key`, `dcg_openai_api_key`, `dcg_gemini_api_key`, `dcg_default_provider`).
- **`Admin\GeneratorPage`** — Admin UI met AJAX endpoints. Post type selectie laadt dynamisch ACF field groups. Twee AJAX actions: `dcg_get_field_groups` en `dcg_generate`.
- **`AI\ProviderInterface`** — Contract: `generate( string $prompt ): string`. Drie implementaties: `ClaudeProvider`, `OpenAIProvider`, `GeminiProvider`. Alle HTTP calls via `wp_remote_post()`.
- **`ACF\FieldReader`** — Leest ACF field groups uit per post type. Bouwt een JSON schema op met veldnamen, types, keuzes, en sub_fields. Ondersteunt flexible content, repeaters en groups recursief.
- **`ACF\FieldWriter`** — Schrijft gegenereerde data naar post meta via `update_field()`. Flexible content layouts met `acf_fc_layout` key.
- **`Generator\ContentGenerator`** — Orkestratie: leest schema → bouwt AI prompt → parse JSON response → `wp_insert_post()` als draft → schrijft ACF velden.

### Flow

1. Gebruiker kiest post type, ACF field groups, provider, en schrijft een prompt
2. `ContentGenerator` leest ACF veldstructuur uit via `FieldReader`
3. Prompt wordt opgebouwd met het schema als JSON, AI moet JSON teruggeven
4. AI response wordt geparsed en gevalideerd
5. Post wordt aangemaakt als draft, ACF velden worden gevuld via `FieldWriter`

### Coding Style

- WordPress spacing: spaties binnen `(` `)` en `[ ]` — bijv. `get_option( 'key', '' )`
- Allman brace style (accolade op nieuwe regel)
