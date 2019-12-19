<?php
namespace WeDevs\WeMail\FrontEnd;

use WeDevs\WeMail\Traits\Hooker;

class FormOptIn
{
    use Hooker;

    /**
     * @var $settings
     */
    protected $settings;

    /**
     *
     * FormOptIn constructor.
     */
    public function __construct()
    {
        wemail_set_owner_api_key();

        $this->settings = wemail()->settings->get('general');

        $this->registration_hooks();
    }

    /**
     *  Register Hooks
     */
    protected function registration_hooks() {

        if ( $this->settings['registration_enabled'] ) {

            if ( is_multisite() ) {
                $this->add_action( 'signup_extra_fields', 'subscribe_checkbox_field_multisite' );
                $this->add_action( 'after_signup_user', 'save_subscriber_from_registration' );
            } else {
                $this->add_action( 'register_form', 'subscribe_checkbox_field');
                $this->add_action( 'user_register', 'save_subscriber_from_registration' );
            }
        }

        if ( $this->settings['woocommerce_enabled'] && class_exists( 'WooCommerce' ) ) {
            $this->add_filter( 'woocommerce_billing_fields', 'add_subscribe_field_woocommerce_billing_form' );
            $this->add_filter('woocommerce_new_order', 'save_subscriber_from_woocommerce_billing' );
        }

        if ( $this->settings['comment_enabled'] ) {
            $this->add_action( 'comment_form_logged_in_after', 'subscribe_checkbox_field');
            $this->add_action( 'comment_form_after_fields', 'subscribe_checkbox_field');
            $this->add_action( 'comment_post', 'save_subscriber_from_comment' );
        }
    }

    /**
     *  Show Field.
     *
     */
    public function subscribe_checkbox_field() {
        ?>
        <p class="wemail-form-opt-in-field">
            <input id="wemail_form_opt_in" <?php echo isset( $_POST['wemail_form_opt_in'] ) ? 'checked' : '';?> type="checkbox" name="wemail_form_opt_in">
            <label for="wemail_form_opt_in"><?php esc_html_e( $this->settings['label'] ); ?></label>
        </p>
        <?php
    }

    /**
     *  Show Field on Multisite.
     */
    public function subscribe_checkbox_field_multisite() {
        ?>
        <p class="wemail-form-opt-in-field">
            <label>
                <input id="wemail_form_opt_in" <?php echo isset( $_POST['wemail_form_opt_in'] ) ? 'checked' : '';?> type="checkbox" name="wemail_form_opt_in">
                <?php esc_html_e( $this->settings['label'] ); ?>
            </label>
        </p>
        <?php
    }

    /**
     *  Add field on woocommerce billing section.
     *
     * @param $fields
     * @return mixed
     */
    public function add_subscribe_field_woocommerce_billing_form($fields ) {

        $fields['wemail_form_opt_in'] = [
            'type' => 'checkbox',
            'label' => $this->settings['label']
        ];

        return $fields;
    }

    /**
     *  Save subscriber from registration field.
     *
     * @param $user_id
     */
    public function save_subscriber_from_registration( $user_id ) {
        if ( isset( $_POST['wemail_form_opt_in'] ) ) {
            wemail()->subscriber->create([
                'email'     => filter_input(INPUT_POST, 'user_email', FILTER_VALIDATE_EMAIL ),
                'lists'     => [
                    $this->settings['list_id']
                ]
            ]);
        }
    }

    /**
     *  Save subscriber from comment.
     *
     * @param $comment_id
     */
    public function save_subscriber_from_comment($comment_id ) {

        if ( isset( $_POST['wemail_form_opt_in'] ) ) {

            $data = [
                'lists'     => [ $this->settings['list_id'] ],
                'email'     => get_comment_author_email( $comment_id )
            ];

            $name = explode( ' ', get_comment_author( $comment_id ), 2 );

            if ( count($name) === 2 ) {
                $data['first_name'] = $name[0];
                $data['last_name']  = $name[1];
            } else {
                $data['first_name'] = $name[0];
            }

            wemail()->subscriber->create($data);
        }
    }

    /**
     *  Save subscriber from woocommerce billing.
     *  @param $order_id
     */
    public function save_subscriber_from_woocommerce_billing($order_id ) {

        if ( isset( $_POST['wemail_form_opt_in'] ) ) {
            $order = wc_get_order( $order_id );

            wemail()->subscriber->create([
                'first_name'    => $order->get_billing_first_name(),
                'last_name'     => $order->get_billing_last_name(),
                'email'         => $order->get_billing_email(),
                'phone'         => $order->get_billing_phone(),
                'address1'      => $order->get_billing_address_1(),
                'address2'      => $order->get_billing_address_2(),
                'city'          => $order->get_billing_city(),
                'state'         => $order->get_billing_state(),
                'country'       => $order->get_billing_country(),
                'zip'           => $order->get_billing_postcode(),
                'lists'         => [ $this->settings['list_id'] ]
            ]);
        }
    }

}
