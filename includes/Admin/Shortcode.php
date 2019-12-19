<?php

namespace WeDevs\WeMail\Admin;

use WeDevs\WeMail\Traits\Hooker;

class Shortcode {

    use Hooker;

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->add_filter( 'mce_buttons', 'add_shortcode_button' );
        $this->add_filter( 'mce_external_plugins', 'enqueue_script' );

        $this->add_action( 'before_wp_tiny_mce', 'print_tinymce_data' );
    }

    /**
     * Add tinymce button
     *
     * @since 1.0.0
     *
     * @param array $buttons
     *
     * @return array
     */
    public function add_shortcode_button( $buttons ) {
        $buttons[] = 'wemail_forms_button';

        return $buttons;
    }

    /**
     * Enqueue script for tinymce
     *
     * @since 1.0.0
     *
     * @param array $plugin_array
     *
     * @return array
     */
    public function enqueue_script( $plugin_array ) {
        $plugin_array['wemail_forms_button'] = WEMAIL_ASSETS . '/js/wemail-tinymce-shortcode.js';

        return $plugin_array;
    }

    /**
     * Print js data for tinymce button
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function print_tinymce_data() {
        $forms = wemail()->form->items();
        $icon = wemail()->wemail_cdn . '/images/logo/wemail-alt.svg';
        ?>
            <script type="text/javascript">
                var wemail_forms_shortcode_button = {
                    title: '<?php echo esc_html__( 'Insert weMail form', 'wemail' ); ?>',
                    forms: <?php echo $forms ? json_encode( $forms ) : json_encode( [] ); ?>,
                    icon: '<?php echo esc_url( $icon ); ?>'
                };
            </script>
        <?php
    }

}
