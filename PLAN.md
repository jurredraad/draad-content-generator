# Plan: Draad Content Generator WordPress Plugin

## Context

WordPress plugin die met AI content genereert voor pagina's en bestaande post types. Vult ACF velden (inclusief flexible content layouts) via post meta. Classic editor. Configureerbare AI provider (Claude, OpenAI, Gemini). PHP 8.0+.

---

## Fase 1: Plugin Basis & Autoloading

- [ ] `composer.json` aanmaken met PSR-4 autoloading (`Draad\ContentGenerator\` → `src/`)
- [ ] `draad-content-generator.php` — plugin header, composer autoload require, `Plugin::init()` aanroepen
- [ ] `src/Plugin.php` — singleton class, registreert admin hooks en menu items
- [ ] `composer dump-autoload` draaien
- [ ] Plugin activeren in WordPress zonder errors

## Fase 2: Settings Pagina

- [ ] `src/Admin/SettingsPage.php` aanmaken
- [ ] Menu item toevoegen onder "Instellingen"
- [ ] Velden registreren via WordPress Settings API:
  - [ ] API key veld voor Claude (Anthropic)
  - [ ] API key veld voor OpenAI
  - [ ] API key veld voor Google Gemini
  - [ ] Dropdown voor default AI provider
- [ ] Keys opslaan in `wp_options`
- [ ] Verificatie: keys opslaan en ophalen werkt

## Fase 3: AI Provider Abstractie

- [ ] `src/AI/ProviderInterface.php` — interface met `generate(string $prompt): string`
- [ ] `src/AI/ClaudeProvider.php` — Anthropic Messages API via `wp_remote_post()`
- [ ] `src/AI/OpenAIProvider.php` — Chat Completions API via `wp_remote_post()`
- [ ] `src/AI/GeminiProvider.php` — GenerateContent API via `wp_remote_post()`
- [ ] Verificatie: simpele prompt sturen en response terugkrijgen per provider

## Fase 4: ACF Integratie

- [ ] `src/ACF/FieldReader.php` aanmaken
  - [ ] ACF field groups ophalen per post type via `acf_get_field_groups()` + `acf_get_fields()`
  - [ ] Veldstructuur omzetten naar JSON schema (naam, type, keuzes, sub_fields)
  - [ ] Flexible content layouts correct uitlezen (layouts + sub_fields per layout)
- [ ] `src/ACF/FieldWriter.php` aanmaken
  - [ ] Gegenereerde content schrijven naar post meta via `update_field()`
  - [ ] Flexible content layouts correct opslaan (layout naam + sub_fields als array)
- [ ] Verificatie: field groups uitlezen en terugschrijven werkt

## Fase 5: Generator Admin Pagina

- [ ] `src/Admin/GeneratorPage.php` aanmaken
- [ ] Menu item toevoegen in WordPress admin
- [ ] UI elementen:
  - [ ] Dropdown: post type selectie
  - [ ] Checkboxes: ACF field groups (dynamisch op basis van post type)
  - [ ] Textarea: prompt / instructies voor de AI
  - [ ] Dropdown: AI provider keuze
  - [ ] Tekstveld: post titel
  - [ ] Button: "Genereer content"
- [ ] AJAX endpoint registreren voor form submission
- [ ] `assets/js/admin.js` — AJAX call + loading state
- [ ] `assets/css/admin.css` — basis admin styling
- [ ] Verificatie: pagina laadt, post type selectie toont juiste field groups

## Fase 6: Content Generator (Orkestratie)

- [ ] `src/Generator/ContentGenerator.php` aanmaken
- [ ] Flow implementeren:
  - [ ] Geselecteerde ACF field groups uitlezen via FieldReader
  - [ ] Gestructureerde prompt opbouwen met veldnamen, types en gebruikersinstructies
  - [ ] AI instructie: "geef JSON terug die matcht met deze veldstructuur"
  - [ ] Prompt sturen naar gekozen AI provider
  - [ ] JSON response parsen en valideren
  - [ ] Nieuwe post aanmaken via `wp_insert_post()`
  - [ ] ACF velden vullen via FieldWriter
- [ ] Error handling: API failures, ongeldige JSON, missende velden
- [ ] Verificatie: volledige flow testen — prompt → AI → post met gevulde ACF velden

## Fase 7: Afronding

- [ ] CLAUDE.md updaten met plugin-specifieke info (commando's, architectuur)
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
