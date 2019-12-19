<?php

namespace WeDevs\WeMail;

use Stringy\StaticStringy;
use WeDevs\WeMail\Admin\Admin;
use WeDevs\WeMail\FrontEnd\FrontEnd;
use WeDevs\WeMail\Hooks;
use WeDevs\WeMail\Privacy\Privacy;
use WeDevs\WeMail\Rest\Rest;

final class WeMail {

    /**
     * Plugin version
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $version = '0.0.1';

    /**
     * DB version
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $db_version = '0.0.1';

    /**
     * Minimum PHP version required
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $min_php = '5.4.0';

    /**
     * Minimum WordPress version required
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $min_wp = '4.4.0';

    /**
     * weMail text domain
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $text_domain = 'wemail';

    /**
     * Contains the core related class instance
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $core = [];

    /**
     * weMail site URL
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $wemail_site = '';

    /**
     * weMail API URL
     * @since 1.0.0
     *
     * @var string
     */
    public $wemail_api = '';

    /**
     * weMail CDN URL
     * @since 1.0.0
     *
     * @var string
     */
    public $wemail_cdn = '';

    /**
     * weMail wemail_app URL
     * @since 1.0.0
     *
     * @var string
     */
    public $wemail_app = '';

    /**
     * @var object
     *
     * @since 1.0.0
     *
     * @var WeMail
     */
    private static $instance;

    /**
     * Admin notice messages
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $admin_notices = [];

    /**
     * Initializes the WeMail() class
     *
     * @since 1.0.0
     *
     * Find any existing WeMail() instance
     * and if it doesn't find one, creates one.
     *
     * @return WeMail
     */
    public static function instance() {
        if (! isset( self::$instance ) && ! ( self::$instance instanceof WeMail ) ) {
            self::$instance = new WeMail;
            self::$instance->boot();
        }

        return self::$instance;
    }

    /**
     * Bootstrap the plugin
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function boot() {
        // Check requirements
        if ( ! $this->met_requirements() ) {
            add_action( 'admin_notices', [ $this, 'admin_notices' ] );
            return;
        }

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // Hook into actions and filters
        $this->init_hooks();
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @since 1.0.0
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->core ) ) {
            $core_class = $this->core[ $prop ];

            return $core_class::instance();
        }

        return $this->{$prop};
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    public function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                $request = is_admin();
                break;

            case 'ajax' :
                $request = defined( 'DOING_AJAX' );
                break;

            case 'cron' :
                $request = defined( 'DOING_CRON' );
                break;

            case 'frontend' :
                $request = ! is_admin() && ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' );
                break;

            default:
                $request = false;
        }

        return $request;
    }

    /**
     * Validate plugin requirements
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    private function met_requirements() {
        if ( version_compare( PHP_VERSION, $this->min_php, '<' ) ) {
            $this->admin_notices['unmet_php_version'] = sprintf(
                __( '%s requires PHP version %s but you are using version %s', 'wemail' ),
                '<strong>weMail</strong>',
                '<strong>' . $this->min_php . '</strong>',
                '<strong>' . PHP_VERSION . '</strong>'
            );

            return false;
        }

        $current_wp_version = get_bloginfo('version');

        if ( version_compare( $current_wp_version, $this->min_wp, '<' ) ) {
            $this->admin_notices['unmet_wordpress_version'] = sprintf(
                __( '%s requires WordPress version %s but you are using version %s', 'wemail' ),
                '<strong>weMail</strong>',
                '<strong>' . $this->min_wp . '</strong>',
                '<strong>' . $current_wp_version . '</strong>'
            );

            return false;
        }

        return true;
    }

    /**
     * `admin_notices` hook
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_notices() {
        foreach ( $this->admin_notices as $notice ) {
            printf( '<div class="error"><p>' . esc_html( $notice ) . '</p></div>' );
        }
    }

    /**
     * Define plugin constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'WEMAIL_VERSION', $this->version );
        define( 'WEMAIL_INCLUDES', WEMAIL_PATH . '/includes' );
        define( 'WEMAIL_CORE', WEMAIL_INCLUDES . '/Core' );
        define( 'WEMAIL_URL', plugins_url( '', WEMAIL_FILE ) );
        define( 'WEMAIL_ASSETS', WEMAIL_URL . '/assets' );
        define( 'WEMAIL_VIEWS', WEMAIL_PATH . '/views' );
    }

    /**
     * Include plugin files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        require_once WEMAIL_INCLUDES . '/functions.php';
    }

    /**
     * Hooks methods to WP actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function init_hooks() {
        register_activation_hook( WEMAIL_FILE, [ '\WeDevs\WeMail\Install', 'install' ] );
        add_action( 'init', [ $this, 'init' ], 0 );
    }

    /**
     * Create class instances
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init() {
        // Before init action.
        do_action( 'wemail_before_init' );

        $this->include_core();
        $this->set_wemail_site();
        $this->set_wemail_api();
        $this->set_wemail_cdn();
        $this->set_wemail_app();

        new Hooks();
        new Rest();

        if ( $this->is_request( 'admin' ) ) {
            new Admin();
        }

        new FrontEnd();
        new Privacy();

        // Init action.
        do_action( 'wemail_init' );
    }

    /**
     * Include core classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function include_core() {
        $class_dirs = glob( WEMAIL_CORE . '/*', GLOB_ONLYDIR );

        foreach ( $class_dirs as $class_dir ) {
            $class_name  = str_replace( WEMAIL_CORE . '/', '', $class_dir );
            $class_slug  = StaticStringy::underscored( $class_name );

            $core_class = "\\WeDevs\\WeMail\\Core\\$class_name\\$class_name";
            $menu_class = "\\WeDevs\\WeMail\\Core\\$class_name\\Menu";

            $this->core[ $class_slug ] = $core_class;

            if ( class_exists( $menu_class ) ) {
                new $menu_class();
            }
        }
    }

    /**
     * Set weMail site URL
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function set_wemail_site() {
        /**
         * weMail site URL
         *
         * @since 1.0.0
         *
         * @param string
         */
        $site = apply_filters( 'wemail_site_url', 'https://getwemail.io' );
        $this->wemail_site = untrailingslashit( $site );
    }

    /**
     * Set weMail API URL
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function set_wemail_api() {
        /**
         * weMail API URL
         *
         * @since 1.0.0
         *
         * @param string
         */
        $api = apply_filters( 'wemail_api_url', 'https://api.getwemail.io/v1' );
        $this->wemail_api = untrailingslashit( $api );
    }

    /**
     * Set weMail CDN URL
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function set_wemail_cdn() {
        /**
         * weMail CDN url
         *
         * @since 1.0.0
         *
         * @param string
         */
        $cdn = apply_filters( 'wemail_cdn_url', 'https://cdn.getwemail.io' );
        $this->wemail_cdn = untrailingslashit( $cdn );
    }

    /**
     * Set weMail APP URL
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function set_wemail_app() {
        /**
         * weMail APP url
         *
         * @since 1.0.0
         *
         * @param string
         */
        $app = apply_filters( 'wemail_app_url', 'https://app.getwemail.io' );
        $this->wemail_app = untrailingslashit( $app );
    }

}
