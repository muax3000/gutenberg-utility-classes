<?php
/**
 * Main loader class for Gutenberg Utility Classes.
 *
 * Registers all hooks and handles asset enqueueing for both
 * the frontend and the block editor.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GUC_Loader {

    private static ?GUC_Loader $instance = null;

    private function __construct() {}

    /**
     * Returns the single instance of this class (Singleton).
     */
    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registers all WordPress hooks.
     */
    public function init(): void {
        add_action( 'wp_enqueue_scripts',       [ $this, 'enqueue_styles' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_styles' ] );
    }

    /**
     * Enqueues the utility stylesheet on the frontend.
     */
    public function enqueue_styles(): void {
        wp_enqueue_style(
            'gutenberg-utility-classes',
            self::get_css_url(),
            [],
            filemtime( self::get_css_path() )
        );
    }

    /**
     * Enqueues the utility stylesheet inside the block editor.
     */
    public function enqueue_editor_styles(): void {
        wp_enqueue_style(
            'gutenberg-utility-classes',
            self::get_css_url(),
            [],
            filemtime( self::get_css_path() )
        );
    }

    /**
     * Returns the absolute filesystem path to the CSS file.
     */
    public static function get_css_path(): string {
        return GUC_PLUGIN_DIR . 'assets/css/utility-classes.css';
    }

    /**
     * Returns the public URL to the CSS file.
     */
    public static function get_css_url(): string {
        return GUC_PLUGIN_URL . 'assets/css/utility-classes.css';
    }
}
