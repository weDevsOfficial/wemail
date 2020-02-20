<?php
namespace WeDevs\WeMail\Core\Mail;

use PHPMailer;

class WeMailMailer extends PHPMailer {
    /**
     * @var $phpmailer PHPMailer
     */
    protected $phpmailer;

    /**
     * Overwrite phpmailer send method
     *
     * @throws \phpmailerException
     */
    public function send() {
        $response = wemail()->api->emails()->transactional()->post( array(
            'to' => $this->formatEmailAddress($this->phpmailer->getToAddresses()),
            'subject' => $this->phpmailer->Subject,
            'message' => $this->phpmailer->Body,
            'type' => $this->phpmailer->ContentType,
            'attachments' => $this->phpmailer->getAttachments()
        ) );

        if ( isset( $response['success'] ) && ( $response['success'] != 'true' || $response['success'] != 1 ) ) {
            throw new \phpmailerException( 'Could not send transactional email' );
        }

        return true;
    }

    /**
     *  Format Email Addresses
     *
     * @param $address
     * @return array
     */
    protected function formatEmailAddress( $address ) {
        return array_map( function ( $address ) {
            return $address[0];
        }, $address );
    }

    /**
     * Set Mailer
     *
     * @param $mailer
     */
    public function setPHPMailer( $mailer ) {
        $this->phpmailer = $mailer;
    }
}
