<?php
/**
 * Admin settings page for Gutenberg Utility Classes.
 *
 * Registers an options page under Settings → Utility Classes that
 * documents every available utility class in filterable tables.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GUC_Admin {

    private static ?GUC_Admin $instance = null;

    private function __construct() {}

    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // -------------------------------------------------------------------------
    // Hooks
    // -------------------------------------------------------------------------

    public function init(): void {
        add_action( 'admin_menu',            [ $this, 'register_menu'  ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function register_menu(): void {
        add_options_page(
            __( 'Utility Classes', 'gutenberg-utility-classes' ),
            __( 'Utility Classes', 'gutenberg-utility-classes' ),
            'manage_options',
            'gutenberg-utility-classes',
            [ $this, 'render_page' ]
        );
    }

    /**
     * Loads admin assets only on this plugin's own settings page.
     */
    public function enqueue_assets( string $hook ): void {
        if ( 'settings_page_gutenberg-utility-classes' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'guc-admin',
            GUC_PLUGIN_URL . 'assets/css/admin.css',
            [],
            filemtime( GUC_PLUGIN_DIR . 'assets/css/admin.css' )
        );

        wp_enqueue_script(
            'guc-admin',
            GUC_PLUGIN_URL . 'assets/js/admin.js',
            [],
            filemtime( GUC_PLUGIN_DIR . 'assets/js/admin.js' ),
            true
        );
    }

    // -------------------------------------------------------------------------
    // Page renderer
    // -------------------------------------------------------------------------

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Zugriff verweigert.', 'gutenberg-utility-classes' ) );
        }
        ?>
        <div class="wrap guc-wrap">

            <h1>
                <?php esc_html_e( 'Gutenberg Utility Classes', 'gutenberg-utility-classes' ); ?>
                <span class="guc-version"><?php echo esc_html( GUC_VERSION ); ?></span>
            </h1>

            <p class="guc-description">
                <?php esc_html_e( 'CSS-Hilfsklassen für den Gutenberg Block Editor. Klassen werden über Einstellungen → Erweitert → Zusätzliche CSS-Klassen im Block-Editor eingetragen.', 'gutenberg-utility-classes' ); ?>
            </p>

            <div class="notice notice-info inline guc-notice">
                <p>
                    <strong><?php esc_html_e( 'Tipp:', 'gutenberg-utility-classes' ); ?></strong>
                    <?php esc_html_e( 'Mehrere Klassen mit Leerzeichen trennen, z.B.:', 'gutenberg-utility-classes' ); ?>
                    <code>hide-on-mobile width-50-desktop stack-from-desktop</code>
                </p>
            </div>

            <div class="guc-search-wrap">
                <label for="guc-search" class="screen-reader-text">
                    <?php esc_html_e( 'Klasse suchen', 'gutenberg-utility-classes' ); ?>
                </label>
                <input
                    type="search"
                    id="guc-search"
                    class="regular-text"
                    placeholder="<?php esc_attr_e( 'Klasse suchen, z.B. hide, stack, width-50 …', 'gutenberg-utility-classes' ); ?>"
                >
            </div>

            <nav
                class="nav-tab-wrapper guc-tabs"
                aria-label="<?php esc_attr_e( 'Klassen-Gruppen', 'gutenberg-utility-classes' ); ?>"
            >
                <a href="#" class="nav-tab nav-tab-active" data-tab="visibility"><?php esc_html_e( 'Sichtbarkeit', 'gutenberg-utility-classes' ); ?></a>
                <a href="#" class="nav-tab" data-tab="stacking"><?php esc_html_e( 'Stacking', 'gutenberg-utility-classes' ); ?></a>
                <a href="#" class="nav-tab" data-tab="width"><?php esc_html_e( 'Breiten', 'gutenberg-utility-classes' ); ?></a>
                <a href="#" class="nav-tab" data-tab="order"><?php esc_html_e( 'Order', 'gutenberg-utility-classes' ); ?></a>
                <a href="#" class="nav-tab" data-tab="spacing"><?php esc_html_e( 'Abstände', 'gutenberg-utility-classes' ); ?></a>
                <a href="#" class="nav-tab" data-tab="text"><?php esc_html_e( 'Text', 'gutenberg-utility-classes' ); ?></a>
            </nav>

            <?php
            $this->render_visibility_panel();
            $this->render_stacking_panel();
            $this->render_width_panel();
            $this->render_order_panel();
            $this->render_spacing_panel();
            $this->render_text_panel();
            $this->render_examples();
            ?>

        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Panel renderers
    // -------------------------------------------------------------------------

    private function render_visibility_panel(): void {
        $rows = [
            [ 'hide-on-mobile',  'Mobile (max 599px)',  'Blendet Block aus',        'Alle Blöcke' ],
            [ 'hide-on-tablet',  'Tablet (600–781px)',  'Blendet Block aus',        'Alle Blöcke' ],
            [ 'hide-on-desktop', 'Desktop (min 782px)', 'Blendet Block aus',        'Alle Blöcke' ],
            [ 'show-on-mobile',  'Mobile (max 599px)',  'Nur auf Mobile sichtbar',  'Alle Blöcke' ],
            [ 'show-on-tablet',  'Tablet (600–781px)',  'Nur auf Tablet sichtbar',  'Alle Blöcke' ],
            [ 'show-on-desktop', 'Desktop (min 782px)', 'Nur auf Desktop sichtbar', 'Alle Blöcke' ],
        ];

        $this->open_panel( 'visibility', false );
        $this->table_open();
        $this->table_head();
        echo '<tbody>';
        foreach ( $rows as [ $class, $bp, $effect, $target ] ) {
            $this->row( $class, $bp, $effect, $target );
        }
        $this->no_results_row();
        echo '</tbody>';
        $this->table_close();
        $this->close_panel();
    }

    private function render_stacking_panel(): void {
        $rows = [
            [ 'stack-on-mobile',    'Mobile (max 599px)',  'Stapelt Spalten auf Mobile',                 'Columns, Row' ],
            [ 'stack-on-tablet',    'Tablet (600–781px)',  'Stapelt Spalten auf Tablet',                 'Columns, Row' ],
            [ 'stack-on-desktop',   'Desktop (min 782px)', 'Stapelt Spalten auf Desktop',                'Columns, Row' ],
            [ 'stack-from-mobile',  'Alle Breakpoints',    'Immer gestapelt (Mobile + Tablet + Desktop)', 'Columns, Row' ],
            [ 'stack-from-tablet',  'Tablet + Desktop',    'Ab Tablet gestapelt',                        'Columns, Row' ],
            [ 'stack-from-desktop', 'Desktop (min 782px)', 'Nur Desktop gestapelt (= stack-on-desktop)', 'Columns, Row' ],
        ];

        $this->open_panel( 'stacking' );
        $this->table_open();
        $this->table_head();
        echo '<tbody>';
        foreach ( $rows as [ $class, $bp, $effect, $target ] ) {
            $this->row( $class, $bp, $effect, $target );
        }
        $this->no_results_row();
        echo '</tbody>';
        $this->table_close();
        $this->close_panel();
    }

    private function render_width_panel(): void {
        $values = [ 10, 20, 25, 30, 33, 40, 50, 60, 66, 70, 75, 80, 90, 100 ];
        $breakpoints = [
            'mobile'  => 'Mobile (max 599px)',
            'tablet'  => 'Tablet (600–781px)',
            'desktop' => 'Desktop (min 782px)',
        ];

        $this->open_panel( 'width' );
        $this->table_open();
        $this->table_head();
        echo '<tbody>';
        foreach ( $breakpoints as $bp_key => $bp_label ) {
            foreach ( $values as $val ) {
                $this->row(
                    sprintf( 'width-%d-%s', $val, $bp_key ),
                    $bp_label,
                    sprintf( '%d%% Breite', $val ),
                    'Columns, Spalten'
                );
            }
            $this->row(
                sprintf( 'width-auto-%s', $bp_key ),
                $bp_label,
                'Automatische Breite (width: auto)',
                'Columns, Spalten'
            );
        }
        $this->no_results_row();
        echo '</tbody>';
        $this->table_close();
        $this->close_panel();
    }

    private function render_order_panel(): void {
        $orders = [
            'first' => 'Erstes Element (order: -1)',
            '1'     => 'Position 1 (order: 1)',
            '2'     => 'Position 2 (order: 2)',
            '3'     => 'Position 3 (order: 3)',
            '4'     => 'Position 4 (order: 4)',
            '5'     => 'Position 5 (order: 5)',
            'last'  => 'Letztes Element (order: 99)',
        ];
        $breakpoints = [
            'mobile'  => 'Mobile (max 599px)',
            'tablet'  => 'Tablet (600–781px)',
            'desktop' => 'Desktop (min 782px)',
        ];

        $this->open_panel( 'order' );
        $this->table_open();
        $this->table_head();
        echo '<tbody>';
        foreach ( $breakpoints as $bp_key => $bp_label ) {
            foreach ( $orders as $order_key => $effect ) {
                $this->row(
                    sprintf( 'order-%s-%s', $order_key, $bp_key ),
                    $bp_label,
                    $effect,
                    'Columns, Spalten'
                );
            }
        }
        $this->no_results_row();
        echo '</tbody>';
        $this->table_close();
        $this->close_panel();
    }

    private function render_spacing_panel(): void {
        $types = [
            'm'   => 'Margin (alle Seiten)',
            'mx'  => 'Margin horizontal (links + rechts)',
            'my'  => 'Margin vertikal (oben + unten)',
            'p'   => 'Padding (alle Seiten)',
            'px'  => 'Padding horizontal (links + rechts)',
            'py'  => 'Padding vertikal (oben + unten)',
            'gap' => 'Gap',
        ];
        $presets = [
            '0'  => '0',
            '20' => 'var(--wp--preset--spacing--20)',
            '30' => 'var(--wp--preset--spacing--30)',
            '40' => 'var(--wp--preset--spacing--40)',
            '50' => 'var(--wp--preset--spacing--50)',
            '60' => 'var(--wp--preset--spacing--60)',
            '70' => 'var(--wp--preset--spacing--70)',
            '80' => 'var(--wp--preset--spacing--80)',
        ];
        $breakpoints = [
            'mobile'  => 'Mobile (max 599px)',
            'tablet'  => 'Tablet (600–781px)',
            'desktop' => 'Desktop (min 782px)',
        ];
        ?>
        <div id="guc-panel-spacing" class="guc-panel" hidden>

            <div class="notice notice-info inline guc-spacing-notice">
                <p>
                    <?php esc_html_e( 'Diese Klassen nutzen die WordPress Standard Spacing Presets (--wp--preset--spacing--*), die von Gutenberg Core bereitgestellt werden.', 'gutenberg-utility-classes' ); ?>
                </p>
            </div>

            <div class="guc-table-wrap">
                <table class="widefat fixed striped guc-table">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e( 'CSS-Klasse',   'gutenberg-utility-classes' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Typ',          'gutenberg-utility-classes' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Preset-Stufe', 'gutenberg-utility-classes' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Breakpoint',   'gutenberg-utility-classes' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'CSS-Wert',     'gutenberg-utility-classes' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Kopieren',     'gutenberg-utility-classes' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ( $breakpoints as $bp_key => $bp_label ) {
                        foreach ( $types as $type_key => $type_label ) {
                            foreach ( $presets as $preset_key => $css_value ) {
                                $class = sprintf( '%s-%s-%s', $type_key, $preset_key, $bp_key );
                                $this->spacing_row( $class, $type_label, $preset_key, $bp_label, $css_value );
                            }
                        }
                    }
                    ?>
                    <tr class="guc-no-results" hidden>
                        <td colspan="6"><?php esc_html_e( 'Keine Ergebnisse für diesen Suchbegriff.', 'gutenberg-utility-classes' ); ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>
        <?php
    }

    private function render_text_panel(): void {
        $alignments = [
            'left'   => 'Linksbündig (text-align: left)',
            'center' => 'Zentriert (text-align: center)',
            'right'  => 'Rechtsbündig (text-align: right)',
        ];
        $breakpoints = [
            'mobile'  => 'Mobile (max 599px)',
            'tablet'  => 'Tablet (600–781px)',
            'desktop' => 'Desktop (min 782px)',
        ];

        $this->open_panel( 'text' );
        $this->table_open();
        $this->table_head();
        echo '<tbody>';
        foreach ( $breakpoints as $bp_key => $bp_label ) {
            foreach ( $alignments as $align_key => $effect ) {
                $this->row(
                    sprintf( 'text-%s-%s', $align_key, $bp_key ),
                    $bp_label,
                    $effect,
                    'Alle Blöcke'
                );
            }
        }
        $this->no_results_row();
        echo '</tbody>';
        $this->table_close();
        $this->close_panel();
    }

    private function render_examples(): void {
        ?>
        <div class="guc-examples">

            <h2><?php esc_html_e( 'Praxis-Beispiele', 'gutenberg-utility-classes' ); ?></h2>

            <details class="guc-example">
                <summary><?php esc_html_e( 'Beispiel 1: Bild auf Mobile oben, auf Desktop rechts', 'gutenberg-utility-classes' ); ?></summary>
                <div class="guc-example-body">
                    <p><?php esc_html_e( 'Ziel: Ein Bild erscheint auf Desktop neben dem Text (rechts), auf Mobile aber darüber.', 'gutenberg-utility-classes' ); ?></p>
                    <ol>
                        <li><?php esc_html_e( 'Einen Columns-Block mit zwei Spalten anlegen: Textspalte links, Bildspalte rechts.', 'gutenberg-utility-classes' ); ?></li>
                        <li>
                            <?php esc_html_e( 'Der Bildspalte (Column-Block) die Klasse', 'gutenberg-utility-classes' ); ?>
                            <code>order-first-mobile</code>
                            <?php esc_html_e( 'vergeben.', 'gutenberg-utility-classes' ); ?>
                        </li>
                        <li><?php esc_html_e( 'Dem Columns-Block selbst keine Stacking-Klasse vergeben – er behält das Zeilen-Layout auf Mobile bei.', 'gutenberg-utility-classes' ); ?></li>
                    </ol>
                    <p>
                        <strong><?php esc_html_e( 'Ergebnis:', 'gutenberg-utility-classes' ); ?></strong>
                        <?php esc_html_e( 'Auf Mobile erscheint das Bild über dem Text, auf Tablet und Desktop nebeneinander (Bild rechts).', 'gutenberg-utility-classes' ); ?>
                    </p>
                </div>
            </details>

            <details class="guc-example">
                <summary><?php esc_html_e( 'Beispiel 2: Spalte responsive skalieren', 'gutenberg-utility-classes' ); ?></summary>
                <div class="guc-example-body">
                    <p><?php esc_html_e( 'Ziel: Eine Spalte nimmt auf Mobile die volle Breite ein, auf Tablet 50 % und auf Desktop 33 %.', 'gutenberg-utility-classes' ); ?></p>
                    <ol>
                        <li><?php esc_html_e( 'Den Column-Block (Spalte) im Block-Editor auswählen.', 'gutenberg-utility-classes' ); ?></li>
                        <li>
                            <?php esc_html_e( 'Im Einstellungsbereich unter "Erweitert → Zusätzliche CSS-Klassen" eintragen:', 'gutenberg-utility-classes' ); ?>
                            <br>
                            <code>width-100-mobile width-50-tablet width-33-desktop</code>
                        </li>
                    </ol>
                    <p>
                        <strong><?php esc_html_e( 'Wichtig:', 'gutenberg-utility-classes' ); ?></strong>
                        <?php esc_html_e( 'Diese Klassen überschreiben die im Editor per Schieberegler gesetzte Breite (inline flex-basis).', 'gutenberg-utility-classes' ); ?>
                    </p>
                </div>
            </details>

            <details class="guc-example">
                <summary><?php esc_html_e( 'Beispiel 3: Layout nur auf Desktop stapeln', 'gutenberg-utility-classes' ); ?></summary>
                <div class="guc-example-body">
                    <p><?php esc_html_e( 'Ziel: Ein Columns-Block soll auf Mobile und Tablet als Zeile, auf Desktop als Stapel erscheinen.', 'gutenberg-utility-classes' ); ?></p>
                    <ol>
                        <li><?php esc_html_e( 'Den Columns-Block auswählen.', 'gutenberg-utility-classes' ); ?></li>
                        <li>
                            <?php esc_html_e( 'Die Klasse', 'gutenberg-utility-classes' ); ?>
                            <code>stack-from-desktop</code>
                            <?php esc_html_e( '(oder gleichwertig', 'gutenberg-utility-classes' ); ?>
                            <code>stack-on-desktop</code>
                            <?php esc_html_e( ') vergeben.', 'gutenberg-utility-classes' ); ?>
                        </li>
                    </ol>
                    <p>
                        <strong><?php esc_html_e( 'Ergebnis:', 'gutenberg-utility-classes' ); ?></strong>
                        <?php esc_html_e( 'Auf Mobile und Tablet bleibt das Zeilen-Layout erhalten. Auf Desktop werden die Spalten übereinander dargestellt.', 'gutenberg-utility-classes' ); ?>
                    </p>
                </div>
            </details>

            <details class="guc-example">
                <summary><?php esc_html_e( 'Beispiel 4: Abstand auf Mobile reduzieren', 'gutenberg-utility-classes' ); ?></summary>
                <div class="guc-example-body">
                    <p><?php esc_html_e( 'Ziel: Auf kleinen Screens soll der Innenabstand eines Blocks kleiner sein und der Außenabstand entfernt werden.', 'gutenberg-utility-classes' ); ?></p>
                    <ol>
                        <li><?php esc_html_e( 'Den betreffenden Block im Block-Editor auswählen.', 'gutenberg-utility-classes' ); ?></li>
                        <li>
                            <?php esc_html_e( 'Im Feld "Zusätzliche CSS-Klassen" eintragen:', 'gutenberg-utility-classes' ); ?>
                            <br>
                            <code>p-20-mobile m-0-mobile</code>
                        </li>
                    </ol>
                    <p>
                        <strong><?php esc_html_e( 'Ergebnis:', 'gutenberg-utility-classes' ); ?></strong>
                        <?php esc_html_e( 'Auf Mobile wird Padding auf Preset-Stufe 20 (kleinster Wert) gesetzt und Margin auf 0 entfernt. Auf Tablet und Desktop greifen die im Editor gesetzten Werte.', 'gutenberg-utility-classes' ); ?>
                    </p>
                    <p>
                        <strong><?php esc_html_e( 'Hinweis:', 'gutenberg-utility-classes' ); ?></strong>
                        <?php esc_html_e( 'Der tatsächliche Wert von Preset-Stufe 20 hängt von der theme.json des aktiven Themes ab (typisch ca. 0.44 rem).', 'gutenberg-utility-classes' ); ?>
                    </p>
                </div>
            </details>

            <details class="guc-example">
                <summary><?php esc_html_e( 'Beispiel 5: Spalten-Abstand auf Mobile entfernen', 'gutenberg-utility-classes' ); ?></summary>
                <div class="guc-example-body">
                    <p><?php esc_html_e( 'Ziel: Ein Columns-Block soll auf Mobile ohne Lücke zwischen den Spalten erscheinen.', 'gutenberg-utility-classes' ); ?></p>
                    <ol>
                        <li><?php esc_html_e( 'Den Columns-Block auswählen.', 'gutenberg-utility-classes' ); ?></li>
                        <li>
                            <?php esc_html_e( 'Im Feld "Zusätzliche CSS-Klassen" eintragen:', 'gutenberg-utility-classes' ); ?>
                            <code>gap-0-mobile</code>
                        </li>
                    </ol>
                    <p>
                        <strong><?php esc_html_e( 'Ergebnis:', 'gutenberg-utility-classes' ); ?></strong>
                        <?php esc_html_e( 'Auf Mobile rücken die Spalten ohne Lücke zusammen. Auf Tablet und Desktop bleibt der reguläre Gap erhalten.', 'gutenberg-utility-classes' ); ?>
                    </p>
                </div>
            </details>

        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Micro-helpers
    // -------------------------------------------------------------------------

    /**
     * Opens the panel wrapper divs.
     *
     * @param string $id     Tab/panel identifier, e.g. "visibility".
     * @param bool   $hidden Whether to add the HTML hidden attribute.
     */
    private function open_panel( string $id, bool $hidden = true ): void {
        $hidden_attr = $hidden ? ' hidden' : '';
        printf(
            '<div id="guc-panel-%s" class="guc-panel"%s><div class="guc-table-wrap">',
            esc_attr( $id ),
            $hidden_attr
        );
    }

    private function close_panel(): void {
        echo '</div></div>';
    }

    private function table_open(): void {
        echo '<table class="widefat fixed striped guc-table">';
    }

    private function table_close(): void {
        echo '</table>';
    }

    private function table_head(): void {
        ?>
        <thead>
            <tr>
                <th scope="col"><?php esc_html_e( 'CSS-Klasse',     'gutenberg-utility-classes' ); ?></th>
                <th scope="col"><?php esc_html_e( 'Breakpoint',     'gutenberg-utility-classes' ); ?></th>
                <th scope="col"><?php esc_html_e( 'Effekt',         'gutenberg-utility-classes' ); ?></th>
                <th scope="col"><?php esc_html_e( 'Beispiel-Block', 'gutenberg-utility-classes' ); ?></th>
            </tr>
        </thead>
        <?php
    }

    private function row( string $class, string $bp, string $effect, string $target ): void {
        ?>
        <tr class="guc-row">
            <td><code><?php echo esc_html( '.' . $class ); ?></code></td>
            <td><?php echo esc_html( $bp ); ?></td>
            <td><?php echo esc_html( $effect ); ?></td>
            <td>
                <?php echo esc_html( $target ); ?>
                <button
                    type="button"
                    class="guc-copy-btn"
                    data-class="<?php echo esc_attr( $class ); ?>"
                    title="<?php esc_attr_e( 'Klasse kopieren', 'gutenberg-utility-classes' ); ?>"
                    aria-label="<?php printf( esc_attr__( 'Klasse %s kopieren', 'gutenberg-utility-classes' ), esc_attr( $class ) ); ?>"
                >📋</button>
            </td>
        </tr>
        <?php
    }

    private function no_results_row(): void {
        ?>
        <tr class="guc-no-results" hidden>
            <td colspan="4"><?php esc_html_e( 'Keine Ergebnisse für diesen Suchbegriff.', 'gutenberg-utility-classes' ); ?></td>
        </tr>
        <?php
    }

    private function spacing_row(
        string $class,
        string $type,
        string $preset,
        string $bp,
        string $css_val
    ): void {
        ?>
        <tr class="guc-row">
            <td><code><?php echo esc_html( '.' . $class ); ?></code></td>
            <td><?php echo esc_html( $type ); ?></td>
            <td><?php echo esc_html( $preset ); ?></td>
            <td><?php echo esc_html( $bp ); ?></td>
            <td><code><?php echo esc_html( $css_val ); ?></code></td>
            <td>
                <button
                    type="button"
                    class="guc-copy-btn"
                    data-class="<?php echo esc_attr( $class ); ?>"
                    title="<?php esc_attr_e( 'Klasse kopieren', 'gutenberg-utility-classes' ); ?>"
                    aria-label="<?php printf( esc_attr__( 'Klasse %s kopieren', 'gutenberg-utility-classes' ), esc_attr( $class ) ); ?>"
                >📋</button>
            </td>
        </tr>
        <?php
    }
}
