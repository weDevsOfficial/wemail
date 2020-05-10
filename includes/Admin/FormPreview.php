<?php

namespace WeDevs\WeMail\Admin;

use WeDevs\WeMail\Traits\Hooker;

class FormPreview {

    use Hooker;

    protected $form_id;

    /**
     * FormPreview constructor.
     *
     * @since 0.6.0
     */
    public function __construct() {
        wemail_set_owner_api_key(false);

        $this->add_action('wp_ajax_wemail_preview', 'render_form_component');

        if ( isset( $_GET['action'], $_GET['form_id'] ) ) {
            $this->form_id = $_GET['form_id'];
        }
    }


    /**
     *  Rendering Form Component
     *
     * @since 0.6.0
     * @return string
     */
    public function render_form_component() {
        global $wp_scripts;
        ?>
        <html <?php language_attributes(); ?>>
            <head>
                <title><?php _e('weMail Form Preview');?></title>
                <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri() . '/style.css'?>">
                <script>
                    var weMail = {
                        'restURL': '<?php echo untrailingslashit( get_rest_url( null, '/wemail/v1') );?>',
                        'nonce': '<?php echo wp_create_nonce( 'wp_rest' );?>',
                        'cdn': '<?php echo wemail()->wemail_cdn;?>'
                    };
                    var wemailForm = <?php echo json_encode(wemail()->form->get($this->form_id));?>
                </script>
            </head>
            <body>
                <div>
                    <?php echo sprintf('<div id="preview-wemail-form"><wemail-form-preview id="%s"/></div>', $this->form_id);?>
                </div>
                <script src="<?php echo get_site_url() . $wp_scripts->registered['jquery-core']->src;?>"></script>
                <script src="<?php echo wemail()->wemail_cdn . '/js/frontend-vendor.js';?>"></script>
                <script src="<?php echo wemail()->wemail_cdn . '/js/preview.js';?>"></script>
            </body>
        </html>
        <?php
        exit(0);
    }
}
