<?php
/**
 * GitHub-based plugin updater for Gutenberg Utility Classes.
 *
 * Checks the configured GitHub repository for a newer release tag
 * and integrates with the WordPress plugin update mechanism so the
 * plugin can be updated directly from the WordPress admin.
 *
 * Usage (see gutenberg-utility-classes.php):
 *   new GUC_Updater( __FILE__, GUC_VERSION, GUC_GITHUB_USER, GUC_GITHUB_REPO );
 *
 * The GitHub release must use a tag in the format "v1.2.3" or "1.2.3".
 * The release asset should be a single ZIP file named *.zip.
 * If no ZIP asset is attached, the GitHub-generated source ZIP is used.
 *
 * For private repositories set GUC_GITHUB_TOKEN to a Personal Access
 * Token with the "repo" scope.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GUC_Updater {

    private string $plugin_file;
    private string $plugin_slug;
    private string $plugin_basename;
    private string $version;
    private string $github_user;
    private string $github_repo;
    private string $github_token;

    private const CACHE_TTL = 12 * HOUR_IN_SECONDS;

    public function __construct(
        string $plugin_file,
        string $version,
        string $github_user,
        string $github_repo,
        string $github_token = ''
    ) {
        $this->plugin_file     = $plugin_file;
        $this->plugin_basename = plugin_basename( $plugin_file );
        $this->plugin_slug     = dirname( $this->plugin_basename );
        $this->version         = $version;
        $this->github_user     = $github_user;
        $this->github_repo     = $github_repo;
        $this->github_token    = $github_token;
    }

    public function init(): void {
        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'inject_update'   ] );
        add_filter( 'plugins_api',                           [ $this, 'plugin_info'     ], 20, 3 );
        add_filter( 'upgrader_post_install',                 [ $this, 'after_install'   ], 10, 3 );
        add_action( 'upgrader_process_complete',             [ $this, 'purge_cache'     ], 10, 2 );
    }

    // -------------------------------------------------------------------------
    // WordPress hooks
    // -------------------------------------------------------------------------

    /**
     * Injects update data into the WordPress update transient when a newer
     * release exists on GitHub.
     *
     * @param object $transient The existing update transient.
     * @return object Modified transient.
     */
    public function inject_update( object $transient ): object {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $release = $this->fetch_latest_release();
        if ( ! $release ) {
            return $transient;
        }

        $remote_version = $this->parse_version( $release['tag_name'] );

        if ( version_compare( $this->version, $remote_version, '<' ) ) {
            $update = new stdClass();
            $update->slug        = $this->plugin_slug;
            $update->plugin      = $this->plugin_basename;
            $update->new_version = $remote_version;
            $update->url         = $release['html_url'] ?? '';
            $update->package     = $this->get_zip_url( $release );
            $update->icons       = [];
            $update->banners     = [];
            $update->tested      = get_bloginfo( 'version' );
            $update->requires    = '6.3';

            $transient->response[ $this->plugin_basename ] = $update;
        }

        return $transient;
    }

    /**
     * Provides plugin information for the "View version details" overlay.
     *
     * @param false|object|array $result Current result.
     * @param string             $action plugins_api action.
     * @param object             $args   Request arguments.
     * @return false|object Modified result.
     */
    public function plugin_info( $result, string $action, object $args ) {
        if ( 'plugin_information' !== $action ) {
            return $result;
        }

        if ( ( $args->slug ?? '' ) !== $this->plugin_slug ) {
            return $result;
        }

        $release = $this->fetch_latest_release();
        if ( ! $release ) {
            return $result;
        }

        $remote_version = $this->parse_version( $release['tag_name'] );

        $info                = new stdClass();
        $info->name          = 'Gutenberg Utility Classes';
        $info->slug          = $this->plugin_slug;
        $info->version       = $remote_version;
        $info->author        = '<a href="https://github.com/' . esc_attr( $this->github_user ) . '">Maximilian Huhle</a>';
        $info->homepage      = 'https://github.com/' . $this->github_user . '/' . $this->github_repo;
        $info->download_link = $this->get_zip_url( $release );
        $info->requires      = '6.3';
        $info->tested        = get_bloginfo( 'version' );
        $info->last_updated  = $release['published_at'] ?? '';
        $info->sections      = [
            'description' => wpautop( $release['body'] ?? __( 'Keine Release-Notizen verfügbar.', 'gutenberg-utility-classes' ) ),
        ];

        return $info;
    }

    /**
     * Renames the extracted plugin folder after installation so WordPress
     * maps it back to the original plugin slug instead of the GitHub archive
     * name (which typically ends in "-main" or "-{tag}").
     *
     * @param bool  $response   Unused.
     * @param array $hook_extra Extra data about the plugin being updated.
     * @param array $result     Upgrader result data.
     * @return array Modified result.
     */
    public function after_install( bool $response, array $hook_extra, array $result ): array {
        if ( ( $hook_extra['plugin'] ?? '' ) !== $this->plugin_basename ) {
            return $result;
        }

        global $wp_filesystem;

        $target = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->plugin_slug;

        if ( $wp_filesystem->exists( $target ) ) {
            $wp_filesystem->delete( $target, true );
        }

        $wp_filesystem->move( $result['destination'], $target );
        $result['destination'] = $target;

        if ( is_plugin_active( $this->plugin_basename ) ) {
            activate_plugin( $this->plugin_basename );
        }

        return $result;
    }

    /**
     * Clears the cached GitHub release data after a plugin update completes.
     *
     * @param \WP_Upgrader $upgrader Upgrader instance.
     * @param array        $options  Options for the current update.
     */
    public function purge_cache( $upgrader, array $options ): void {
        if (
            'update' === ( $options['action'] ?? '' ) &&
            'plugin' === ( $options['type']   ?? '' )
        ) {
            delete_transient( $this->cache_key() );
        }
    }

    // -------------------------------------------------------------------------
    // GitHub API
    // -------------------------------------------------------------------------

    /**
     * Fetches the latest release from the GitHub API (cached for 12 hours).
     *
     * @return array|null Decoded release data or null on failure.
     */
    private function fetch_latest_release(): ?array {
        $cached = get_transient( $this->cache_key() );
        if ( false !== $cached ) {
            return $cached ?: null;
        }

        $url  = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            rawurlencode( $this->github_user ),
            rawurlencode( $this->github_repo )
        );
        $args = [
            'timeout' => 10,
            'headers' => [
                'Accept'     => 'application/vnd.github+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
            ],
        ];

        if ( $this->github_token ) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->github_token;
        }

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            // Cache a falsy sentinel for 5 minutes to avoid hammering the API.
            set_transient( $this->cache_key(), [], 5 * MINUTE_IN_SECONDS );
            return null;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
            set_transient( $this->cache_key(), [], 5 * MINUTE_IN_SECONDS );
            return null;
        }

        set_transient( $this->cache_key(), $data, self::CACHE_TTL );
        return $data;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function cache_key(): string {
        return 'guc_github_release_' . md5( $this->github_user . '/' . $this->github_repo );
    }

    private function parse_version( string $tag ): string {
        return ltrim( $tag, 'vV' );
    }

    /**
     * Returns the download URL for the release ZIP.
     * Prefers an explicitly attached asset over the auto-generated source ZIP.
     *
     * @param array $release GitHub release data.
     * @return string ZIP download URL.
     */
    private function get_zip_url( array $release ): string {
        foreach ( $release['assets'] ?? [] as $asset ) {
            if ( str_ends_with( strtolower( $asset['name'] ?? '' ), '.zip' ) ) {
                return $asset['browser_download_url'] ?? '';
            }
        }

        // Fall back to the GitHub-generated source ZIP.
        return $release['zipball_url'] ?? '';
    }
}
