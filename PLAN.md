# Plan: Draad Content Generator WordPress Plugin

## Context

WordPress plugin die met AI content genereert voor pagina's en bestaande post types. Vult ACF velden (inclusief flexible content layouts) via post meta. Classic editor. Configureerbare AI provider (Claude, OpenAI, Gemini). PHP 8.0+.

---

## Fase 1: Plugin Basis & Autoloading

- [x] `composer.json` aanmaken met PSR-4 autoloading (`Draad\ContentGenerator\` → `src/`)
- [x] `draad-content-generator.php` — plugin header, composer autoload require, `Plugin::init()` aanroepen
- [x] `src/Plugin.php` — singleton class, registreert admin hooks en menu items
- [x] `composer dump-autoload` draaien
- [ ] Plugin activeren in WordPress zonder errors

## Fase 2: Settings Pagina

- [x] `src/Admin/SettingsPage.php` aanmaken
- [x] Menu item toevoegen onder "Instellingen"
- [x] Velden registreren via WordPress Settings API:
  - [x] API key veld voor Claude (Anthropic)
  - [x] API key veld voor OpenAI
  - [x] API key veld voor Google Gemini
  - [x] Dropdown voor default AI provider
- [x] Keys opslaan in `wp_options`
- [ ] Verificatie: keys opslaan en ophalen werkt

## Fase 3: AI Provider Abstractie

- [x] `src/AI/ProviderInterface.php` — interface met `generate(string $prompt): string`
- [x] `src/AI/ClaudeProvider.php` — Anthropic Messages API via `wp_remote_post()`
- [x] `src/AI/OpenAIProvider.php` — Chat Completions API via `wp_remote_post()`
- [x] `src/AI/GeminiProvider.php` — GenerateContent API via `wp_remote_post()`
- [ ] Verificatie: simpele prompt sturen en response terugkrijgen per provider

## Fase 4: ACF Integratie

- [x] `src/ACF/FieldReader.php` aanmaken
  - [x] ACF field groups ophalen per post type via `acf_get_field_groups()` + `acf_get_fields()`
  - [x] Veldstructuur omzetten naar JSON schema (naam, type, keuzes, sub_fields)
  - [x] Flexible content layouts correct uitlezen (layouts + sub_fields per layout)
- [x] `src/ACF/FieldWriter.php` aanmaken
  - [x] Gegenereerde content schrijven naar post meta via `update_field()`
  - [x] Flexible content layouts correct opslaan (layout naam + sub_fields als array)
- [ ] Verificatie: field groups uitlezen en terugschrijven werkt

## Fase 5: Generator Admin Pagina

- [x] `src/Admin/GeneratorPage.php` aanmaken
- [x] Menu item toevoegen in WordPress admin
- [x] UI elementen:
  - [x] Dropdown: post type selectie
  - [x] Checkboxes: ACF field groups (dynamisch op basis van post type)
  - [x] Textarea: prompt / instructies voor de AI
  - [x] Dropdown: AI provider keuze
  - [x] Tekstveld: post titel
  - [x] Button: "Genereer content"
- [x] AJAX endpoint registreren voor form submission
- [x] `assets/js/admin.js` — AJAX call + loading state
- [x] `assets/css/admin.css` — basis admin styling
- [ ] Verificatie: pagina laadt, post type selectie toont juiste field groups

## Fase 6: Content Generator (Orkestratie)

- [x] `src/Generator/ContentGenerator.php` aanmaken
- [x] Flow implementeren:
  - [x] Geselecteerde ACF field groups uitlezen via FieldReader
  - [x] Gestructureerde prompt opbouwen met veldnamen, types en gebruikersinstructies
  - [x] AI instructie: "geef JSON terug die matcht met deze veldstructuur"
  - [x] Prompt sturen naar gekozen AI provider
  - [x] JSON response parsen en valideren
  - [x] Nieuwe post aanmaken via `wp_insert_post()`
  - [x] ACF velden vullen via FieldWriter
- [x] Error handling: API failures, ongeldige JSON, missende velden
- [ ] Verificatie: volledige flow testen — prompt → AI → post met gevulde ACF velden

## Fase 7: Afronding

- [x] CLAUDE.md updaten met plugin-specifieke info (commando's, architectuur)
- [ ] Edge cases testen: lege velden, nested flexible content, image/file velden overslaan
- [ ] Admin notices voor feedback (succes/fout meldingen)

---

## Plugin Structuur

```
draad-content-generator/
├── CLAUDE.md
├── PLAN.md
├── composer.json
├── draad-content-generator.php
├── src/
│   ├── Plugin.php
│   ├── Admin/
│   │   ├── SettingsPage.php
│   │   └── GeneratorPage.php
│   ├── AI/
│   │   ├── ProviderInterface.php
│   │   ├── ClaudeProvider.php
│   │   ├── OpenAIProvider.php
│   │   └── GeminiProvider.php
│   ├── ACF/
│   │   ├── FieldReader.php
│   │   └── FieldWriter.php
│   └── Generator/
│       └── ContentGenerator.php
└── assets/
    ├── css/admin.css
    └── js/admin.js
```

## AI Prompt Strategie

De prompt naar de AI bevat:
1. De gebruikersinstructie (vrije tekst)
2. De ACF veldstructuur als JSON schema (veldnaam, type, keuzes bij select/radio, sub_fields bij flexible content)
3. Instructie om JSON terug te geven die exact matcht met de veldstructuur
