<?php
/**
 * Plugin Name: Draad Content Generator
 * Description: Genereer pagina's en post types met AI-gegenereerde ACF content
 * Version: 1.0.0
 * Author: Draad
 * Text Domain: draad-content-generator
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'DCG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DCG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DCG_PLUGIN_VERSION', '1.0.0' );

require_once DCG_PLUGIN_DIR . 'vendor/autoload.php';

Draad\ContentGenerator\Plugin::init();
