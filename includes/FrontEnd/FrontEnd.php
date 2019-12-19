<?php

namespace WeDevs\WeMail\FrontEnd;

use WeDevs\WeMail\Traits\Hooker;

class FrontEnd {

    use Hooker;

    public function __construct() {
        register_widget( '\WeDevs\WeMail\FrontEnd\Widget' );
        $this->add_action( 'template_redirect', 'init' );
        new FormOptIn();
    }

    public function init() {
        wemail_set_owner_api_key( false );

        new Scripts();
        new Shortcodes();

        $this->add_action('wp_footer', 'render_form' );
    }

    public function render_form() {
        wemail_set_owner_api_key( false );

        $forms = wemail()->form->all([
            'type' => 'floating-bar,floating-box,modal',
            'fields' => 'id,type,settings',
            'status' => 1
        ]);

        $forms = $forms instanceof \WP_Error ? [] : $forms['data'];

        $current_page_id = get_queried_object_id();

        if ( ! empty( $forms ) ) {

            $forms = $this->get_filtered_forms( $forms, $current_page_id );

            foreach ( $forms as $form ) {
                echo esc_html( wemail_form( $form['id'] ) );
            }
        }
    }

    protected function get_filtered_forms( $forms, $object_id ) {

        return array_filter( $forms, function ( $form ) use ( $object_id ){
            $settings = $form['settings'];

            if ( ! isset( $settings['showPage'], $settings['when'], $settings['who'] ) ) {
                return false;
            }

            return $this->is_passed_all_checks( $form, $object_id );
        });
    }

    protected function is_passed_all_checks($form, $object_id ) {

        if ( ! $this->checking_is_modal( $form ) ) {
            return false;
        }

        if ( ! $this->checking_show_page( $form['settings'], $object_id ) ) {
            return false;
        }

        if ( ! $this->checking_schedule( $form['settings'] ) ) {
            return false;
        }

        if ( ! $this->checking_who_see( $form['settings'] ) ) {
            return false;
        }

        return true;
    }

    protected function checking_is_modal( $form ) {
        $settings = $form['settings'];

        if ($form['type'] !== 'modal') {
            return true;
        }

        return $settings['modalTrigger'] === 'auto';
    }

    protected function checking_show_page( $settings, $object_id ) {
        switch ( $settings['showPage'] ) {
            case 'all':
                return true;
                break;
            case 'home':
                return is_front_page() || is_home();
                break;
            default:
                if ( empty( $settings['pages'] ) ) {
                    return false;
                }
                return in_array( $object_id, array_column( $settings['pages'], 'id') );
        }
    }

    /**
     * @param $settings
     * @return bool
     */
    protected function checking_schedule($settings) {
        switch ( $settings['when'] ) {
            case 'always':
                return true;
                break;
            case 'schedule':
                if (!isset( $settings['scheduleFrom'], $settings['scheduleTo'] )) {
                    return false;
                }
                $now   = date('Y-m-d');

                if ( ($now >= $settings['scheduleFrom']) && ($now <= $settings['scheduleTo']) ) {
                    return true;
                }

                break;
        }

        return false;
    }

    protected function checking_who_see( $settings )
    {
        switch ( $settings['who'] ) {
            case 'all_users':
                return true;
                break;

            case 'logged_users':
                return is_user_logged_in();
                break;

            case 'not_logged_users':
                return ! is_user_logged_in();
                break;
        }

        return false;
    }

}

