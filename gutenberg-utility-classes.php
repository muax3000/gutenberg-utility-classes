<?php
/**
 * Plugin Name:       Gutenberg Utility Classes
 * Plugin URI:        https://github.com/muax3000/gutenberg-utility-classes
 * Description:       Responsive CSS-Hilfsklassen für den Gutenberg Block Editor
 * Version:           1.2.0
 * Requires at least: 6.3
 * Requires PHP:      8.0
 * Author:            Maximilian Huhle
 * Author URI:        https://github.com/muax3000
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gutenberg-utility-classes
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GUC_VERSION', '1.2.0');
define('GUC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GUC_PLUGIN_URL', plugin_dir_url(__FILE__));

// ---------------------------------------------------------------------------
// GitHub Updater-Konfiguration
// Trage hier deinen GitHub-Benutzernamen und Repository-Namen ein.
// Für private Repos: GUC_GITHUB_TOKEN auf einen Personal Access Token setzen.
// ---------------------------------------------------------------------------
define('GUC_GITHUB_USER', 'muax3000');
define('GUC_GITHUB_REPO', 'gutenberg-utility-classes');
define('GUC_GITHUB_TOKEN', '');

require_once GUC_PLUGIN_DIR . 'includes/class-guc-loader.php';
require_once GUC_PLUGIN_DIR . 'includes/class-guc-admin.php';
require_once GUC_PLUGIN_DIR . 'includes/class-guc-updater.php';

/**
 * Bootstraps the plugin after all plugins are loaded.
 */
function guc_init(): void
{
    GUC_Loader::get_instance()->init();
    GUC_Admin::get_instance()->init();

    $updater = new GUC_Updater(
        __FILE__,
        GUC_VERSION,
        GUC_GITHUB_USER,
        GUC_GITHUB_REPO,
        GUC_GITHUB_TOKEN
    );
    $updater->init();
}
add_action('plugins_loaded', 'guc_init');
