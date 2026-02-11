<?php

namespace Draad\ContentGenerator;

use Draad\ContentGenerator\Admin\SettingsPage;
use Draad\ContentGenerator\Admin\GeneratorPage;

class Plugin
{
    private static ?Plugin $instance = null;

    public static function init(): self
    {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action( 'admin_menu', [ $this, 'registerMenuPages' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminAssets' ] );
    }

    public function registerMenuPages(): void
    {
        $settingsPage = new SettingsPage();
        $settingsPage->register();

        $generatorPage = new GeneratorPage();
        $generatorPage->register();
    }

    public function enqueueAdminAssets( string $hook ): void
    {
        if ( ! str_contains( $hook, 'draad-content-generator' ) ) {
            return;
        }

        wp_enqueue_style(
            'dcg-admin',
            DCG_PLUGIN_URL . 'assets/css/admin.css',
            [],
            DCG_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'dcg-admin',
            DCG_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            DCG_PLUGIN_VERSION,
            true
        );

        wp_localize_script( 'dcg-admin', 'dcgAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'dcg_generate' ),
        ] );
    }
}
