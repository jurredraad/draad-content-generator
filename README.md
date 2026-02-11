# Draad Content Generator

WordPress plugin die met AI content genereert voor pagina's en post types. Vult ACF velden (inclusief flexible content layouts) automatisch via een admin interface.

## Vereisten

- PHP 8.0+
- WordPress 6.0+
- [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/)
- [Composer](https://getcomposer.org/)
- API key voor minimaal één van: Claude (Anthropic), OpenAI, of Google Gemini

## Installatie

1. Clone deze repository in je `wp-content/plugins/` map:

```bash
cd wp-content/plugins/
git clone https://github.com/jurredraad/draad-content-generator.git
```

2. Installeer PHP dependencies:

```bash
cd draad-content-generator
composer install
```

3. Activeer de plugin in WordPress via **Plugins → Geïnstalleerde plugins**.

4. Ga naar **Instellingen → Content Generator** en vul je API key(s) in.

## Gebruik

1. Ga naar **Content Generator** in het admin menu
2. Selecteer een post type
3. Kies de ACF field groups die je wilt vullen
4. Schrijf een prompt met instructies voor de AI
5. Kies een AI provider
6. Klik op **Genereer content**

De plugin maakt een nieuwe post aan als concept (draft) met alle geselecteerde ACF velden gevuld.
