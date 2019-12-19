<?php
namespace WeDevs\WeMail\Traits;

trait Ajax {

    /**
     * Wrapper method to hook an ajax action
     *
     * If method name is not provided, then the class must have
     * a method with the same name as tag or ajax action name.
     *
     * @since 1.0.0
     *
     * @param string $tag    Action name
     * @param string $method Method name
     * @param bool   $nopriv Also add nopriv hook
     */
    protected function add_ajax_action( $tag, $method = null, $nopriv = false ) {
        if ( ! $method ) {
            $method = $tag;
        } else {
            $tag = $tag . '_' . $method;
        }

        add_action( 'wp_ajax_wemail_' . $tag, [ $this, $method ] );

        if ( $nopriv ) {
            add_action( 'wp_ajax_nopriv_wemail_' . $tag, [ $this, $method ] );
        }
    }

    /**
     * Verify request nonce
     *
     * @since 1.0.0
     *
     * @param string the nonce action name
     *
     * @return void
     */
    public function verify_nonce( $action = 'wemail-nonce' ) {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), $action ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'wemail' ) );
        }
    }

    /**
     * Wrapper function for sending success response
     *
     * @since 1.0.0
     *
     * @param mixed $data
     *
     * @return void
     */
    public function send_success( $data = null ) {
        wp_send_json_success( $data );
    }

    /**
     * Wrapper function for sending error
     *
     * @since 1.0.0
     *
     * @param mixed $data
     *
     * @return void
     */
    public function send_error( $data = null ) {
        wp_send_json_error( $data );
    }

}
