<?php

namespace Draad\ContentGenerator\Admin;

class SettingsPage
{
    private const OPTION_GROUP = 'dcg_settings';
    private const PAGE_SLUG = 'dcg-settings';

    public function register(): void
    {
        add_options_page(
            'Content Generator',
            'Content Generator',
            'manage_options',
            self::PAGE_SLUG,
            [ $this, 'renderPage' ]
        );

        add_action( 'admin_init', [ $this, 'registerSettings' ] );
    }

    public function registerSettings(): void
    {
        register_setting( self::OPTION_GROUP, 'dcg_default_provider' );
        register_setting( self::OPTION_GROUP, 'dcg_claude_api_key' );
        register_setting( self::OPTION_GROUP, 'dcg_openai_api_key' );
        register_setting( self::OPTION_GROUP, 'dcg_gemini_api_key' );

        add_settings_section(
            'dcg_provider_section',
            'AI Provider Instellingen',
            null,
            self::PAGE_SLUG
        );

        add_settings_field(
            'dcg_default_provider',
            'Default provider',
            [ $this, 'renderProviderField' ],
            self::PAGE_SLUG,
            'dcg_provider_section'
        );

        add_settings_field(
            'dcg_claude_api_key',
            'Claude (Anthropic) API Key',
            [ $this, 'renderApiKeyField' ],
            self::PAGE_SLUG,
            'dcg_provider_section',
            [ 'option' => 'dcg_claude_api_key' ]
        );

        add_settings_field(
            'dcg_openai_api_key',
            'OpenAI API Key',
            [ $this, 'renderApiKeyField' ],
            self::PAGE_SLUG,
            'dcg_provider_section',
            [ 'option' => 'dcg_openai_api_key' ]
        );

        add_settings_field(
            'dcg_gemini_api_key',
            'Google Gemini API Key',
            [ $this, 'renderApiKeyField' ],
            self::PAGE_SLUG,
            'dcg_provider_section',
            [ 'option' => 'dcg_gemini_api_key' ]
        );
    }

    public function renderProviderField(): void
    {
        $value = get_option( 'dcg_default_provider', 'claude' );
        ?>
        <select name="dcg_default_provider">
            <option value="claude" <?php selected( $value, 'claude' ); ?>>Claude (Anthropic)</option>
            <option value="openai" <?php selected( $value, 'openai' ); ?>>OpenAI</option>
            <option value="gemini" <?php selected( $value, 'gemini' ); ?>>Google Gemini</option>
        </select>
        <?php
    }

    public function renderApiKeyField( array $args ): void
    {
        $option = $args['option'];
        $value = get_option( $option, '' );
        ?>
        <input
            type="password"
            name="<?php echo esc_attr( $option ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
        />
        <?php
    }

    public function renderPage(): void
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Content Generator - Instellingen</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::PAGE_SLUG );
                submit_button( 'Opslaan' );
                ?>
            </form>
        </div>
        <?php
    }
}
