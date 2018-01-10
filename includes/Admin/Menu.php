<?php

namespace WeDevs\WeMail\Admin;

use WeDevs\WeMail\Traits\Hooker;

class Menu {

    use Hooker;

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->add_action( 'admin_menu', 'register_admin_menu' );
    }

    /**
     * Register admin menu items
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_admin_menu() {
        global $submenu;

        if ( current_user_can( 'manage_options' ) || wemail()->user->can( 'view_wemail' ) ) {
            $capability = 'read';

            $wemail = add_menu_page( __( 'weMail', 'wemail' ), __( 'weMail', 'wemail' ), $capability, 'wemail', [ $this, 'admin_view' ], 'dashicons-email-alt', 1 );

            $submenu['wemail'] = apply_filters( 'wemail_admin_submenu', [], $capability );

            $this->add_action( 'admin_print_styles-' . $wemail, 'enqueue_styles' );
            $this->add_action( 'admin_print_scripts-' . $wemail, 'enqueue_scripts' );
        }
    }

    /**
     * Enqueue weMail admin css hook
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_styles() {
        do_action( 'wemail_admin_enqueue_styles' );
    }

    /**
     * Enqueue weMail admin js hook
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        do_action( 'wemail_admin_enqueue_scripts' );
    }

    /**
     * Admin page view
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_view() {
        include_once WEMAIL_VIEWS . '/admin.php';
    }
}
