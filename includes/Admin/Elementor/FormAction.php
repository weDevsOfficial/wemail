<?php

namespace WeDevs\WeMail\Admin\Elementor;

use ElementorPro\Modules\Forms\Classes\Action_Base;

class FormAction extends Action_Base {

    public function get_name() {
        return 'wemail_form_action';
    }

    public function get_label() {
        return __( 'weMail',  'wemail' );
    }

    public function run( $record, $ajax_handler ) {
        $settings = $record->get( 'form_settings' );

        if ( empty( $settings['wemail_list'] ) ) {
            return;
        }

        if ( empty( $settings['wemail_field_maps'] ) ) {
            return;
        }

        $data = [
            'source' => 'elementor',
            'list_id' => $settings['wemail_list']
        ];

        $field_maps = array_column( $settings['wemail_field_maps'],  'form_field_id',  'wemail_field' );
        $raw_fields = $record->get( 'fields' );

        foreach ($field_maps as $column => $form_id) {
            if ( isset($raw_fields[$form_id]['value']) ) {
                $data[$column] = $raw_fields[$form_id]['value'];
            }
        }

        if ( isset( $data['email'] ) ) {
            if ( ! is_email( $data['email'] ) ) {
                return;
            }
        }

        wemail_set_owner_api_key( false );

        wemail()->api->subscribers()->put( $data );
    }

    public function register_settings_section( $widget ) {
        $widget->start_controls_section(
            'section_wemail',
            [
                'label' => $this->get_label(),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control('wemail_list', [
            'label' => __('weMail Lists'),
            'type' => \Elementor\Controls_Manager::TEXT,
        ]);

        $repeater = new \Elementor\Repeater();

        $repeater->add_control('wemail_field', [
            'label' => __('weMail Field', 'wemail'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'email',
            'options' => [
                'first_name' => __( 'First Name', 'wemail' ),
                'last_name'  => __( 'Last Name', 'wemail' ),
                'full_name'  => __( 'Full Name', 'wemail' ),
                'email'      => __( 'Email', 'wemail' ),
                'mobile'     => __( 'Mobile', 'wemail' ),
                'phone'     => __( 'Phone', 'wemail' )
            ]
        ]);

        $repeater->add_control(
            'form_field_id', [
                'label' => __( 'Form Field ID', 'wemail' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'email'
            ]
        );

        $widget->add_control(
            'wemail_field_maps',
            [
                'label' => __( 'Field Map', 'weMail' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ wemail_field }}}'
            ]
        );

        $widget->end_controls_section();
    }

    public function on_export( $element ) {
        unset(
            $element['settings']['wemail_list'],
            $element['settings']['wemail_field_maps'],
        );
    }
}
