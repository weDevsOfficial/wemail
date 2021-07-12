<?php

namespace WeDevs\WeMail\Core\Ecommerce\Requests;

use WeDevs\WeMail\Traits\Singleton;

class Orders {

    use Singleton;

    private $revenue_cookie = 'wemail_campaign_revenue';

    /**
     * @param $payload
     * @param $source
     */
    public function received( $payload, $source ) {
        $response = wemail()->api->ecommerce()->orders()->post(
            [
                'source'               => $source,
                'id'                   => $payload['id'],
                'parent_id'            => $payload['parent_id'],
                'customer'             => $payload['customer'],
                'status'               => $payload['status'],
                'currency'             => $payload['currency'],
                'total'                => $payload['total'],
                'payment_method_title' => $payload['payment_method_title'],
                'date_created'         => $payload['date_created'],
                'date_completed'       => $payload['date_completed'],
                'permalink'            => $payload['permalink'],
                'products'             => $payload['products'],
                'campaign_id'          => $this->campaign_id(),
            ]
        );

        $this->clear_revenue_cookie( $response );
    }

    /**
     * @param $payload
     * @param $source
     */
    public function statusUpdated( $payload, $source ) {
        $response = wemail()->api->ecommerce()->order_status()->put(
            [
                'source'            => $source,
                'order_id'          => $payload['order_id'],
                'status'            => $payload['status'],
                'campaign_id'       => $this->campaign_id(),
                'is_first_order'    => $this->is_first_order( $payload['order_id'] ),
            ]
        );

        $this->clear_revenue_cookie( $response );
    }

    /**
     * Pass campaign information to track campaign along with order info
     */
    protected function campaign_id() {
        if ( isset( $_COOKIE[ $this->revenue_cookie ] ) ) {
            return sanitize_text_field( wp_unslash( $_COOKIE[ $this->revenue_cookie ] ) );
        }
    }

    /**
     * @param $response
     */
    protected function clear_revenue_cookie( $response ) {
        if ( is_object( $response ) ) {
            return $response;
        }

        if ( isset( $response['success'] ) && $response['success'] ) {
            unset( $_COOKIE[ $this->revenue_cookie ] );
            setcookie( $this->revenue_cookie, null, -1, '/' );
            return;
        }

        return $response;
    }


    /**
     * @param $order_id
     * @return bool
     * https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query
     */
    protected function is_first_order( $order_id ) {
        $order = wc_get_order( $order_id );

        $user_id = $order->get_user_id();

        $args = [
            'limit' => 2,
            'return' => 'ids',
        ];

        if ( $user_id ) {
            $args['customer_id'] = $user_id;
        } else {
            $args['billing_email'] = $order->get_billing_email();
        }

        $orders = wc_get_orders( $args );

        return $orders->total < 2;
    }
}
