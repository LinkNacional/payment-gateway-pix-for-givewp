<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentPending;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;

/**
 * @inheritDoc
 */
final class PixGatewayClass extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'pix-payment-gateway';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return __('Pix QR Code', 'pix-give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Pix', 'pix-give');
    }


    public function crcChecksum(string $string): string
    {
        /*
            * Esta função auxiliar calcula o CRC-16/CCITT-FALSE
            *
            * Autor: evilReiko (https://stackoverflow.com/users/134824/evilreiko)
            * Postada originalmente em: https://stackoverflow.com/questions/30035582/how-to-calculate-crc16-ccitt-in-php-hex
            */

        $crc = 0xFFFF;
        $strlen = strlen($string);
        for($c = 0; $c < $strlen; $c++) {
            $crc ^= ord(substr($string, $c, 1)) << 8;
            for($i = 0; $i < 8; $i++) {
                if($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        $hex = $crc & 0xFFFF;
        $hex = dechex($hex);
        $hex = strtoupper($hex);
        $hex = str_pad($hex, 4, '0', STR_PAD_LEFT);

        return $hex;
    }

    public function cleanString(string $string): string
    {
        /*
            * Autor: Eng. Renato Monteiro Batista (http://renato.ovh)
            */
        $search  = explode(",", "à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż");
        $replace = explode(",", "a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z");

        return preg_replace('/\W /', '', str_replace(
            $search,
            $replace,
            preg_replace(
                '%(?:
            \xF0[\x90-\xBF][\x80-\xBF]{2}        # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )%xs',
                '  ',
                $string
            )
        ));
    }

    public function getQrCodeFromSettings(string $keyType, string $key, string $keyName, string $keyCity, string $keyId = '***', string $amount = ''): string
    {
        $pix_key  = (($keyType === 'Telefone') || (substr($key, 0, 3) === '+55')) ? $key : '+55' . $key;
        $pix_name = $this->cleanString((strlen($keyName) > 25) ? substr($keyName, 0, 25) : $keyName);
        $pix_city = $this->cleanString((strlen($keyCity) > 15) ? substr($keyCity, 0, 15) : $keyCity);

        $qr = '000201' .                                                                                                // (00 Payload Format Indicator)
        '26' . sprintf("%02d", 22 + strlen($pix_key)) .                                                                 // (26 Merchant Account Information)
        '0014BR.GOV.BCB.PIX' .                                                                                              // (00 GUI - Default br.gov.bcb.pix)
        '01' . sprintf("%02d", strlen($pix_key)) . $pix_key .                                                               // (01 Chave Pix)
        '52040000' .                                                                                                    // (52 Merchant Category Code)
        '5303986' .                                                                                                     // (53 Transaction  Currency - BRL 986)
        ((strlen($amount) === 0) ? '' : ('54' . sprintf("%02d", strlen($amount)) . $amount)) .                          // (54 Transaction Amount - Optional)
        '5802BR' .                                                                                                      // (58 Country Code - BR)
        '59' . sprintf("%02d", strlen($pix_name)) . $pix_name .                                                         // (59 Merchant Name)
        '60' . sprintf("%02d", strlen($pix_city)) . $pix_city .                                                         // (60 Merchant City)
        '62' . sprintf("%02d", 4 + strlen($keyId)) . '05' . sprintf("%02d", strlen($keyId)) . $keyId . '6304';          // (62 Additional Data Field - Default ***)
        $qr .= $this->crcChecksum($qr);                                                                                 // (63 CRC16 Chcksum)

        return $qr;
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        $qr = $this->getQrCodeFromSettings(give_get_option('lkn-payment-pix-type-setting'), give_get_option('lkn-payment-pix-key'), give_get_option('lkn-payment-pix-name-setting'), give_get_option('lkn-payment-pix-city-setting'));

        load_template(PAYMENT_GATEWAY_PIX_PLUGIN_DIR . 'public/partials/payment-gateway-pix-for-givewp-public-display.php', true, array(
            'pixType' => give_get_option('lkn-payment-pix-type-setting'),
            'pixKey' => give_get_option('lkn-payment-pix-key'),
            'pixName' => give_get_option('lkn-payment-pix-name-setting'),
            'pixCity' => give_get_option('lkn-payment-pix-city-setting'),
            'formId' => $formId,
            'isFormEnabled' => (give_get_option('lkn-payment-pix-details-setting') === 'enabled') ? true : false,
        ));

        return "";
    }

    /**
     * // TODO needs this function to appear in v3 forms
     * @since 3.0.0
     */
    public function enqueueScript(int $formId): void
    {
        wp_enqueue_script(
            self::id(),
            PAYMENT_GATEWAY_PIX_PLUGIN_URL . 'includes/js/lkn-pix.js',
            array('wp-element', 'wp-i18n'),
            PAYMENT_GATEWAY_PIX_FOR_GIVEWP_VERSION,
            true
        );


        wp_localize_script(
            self::id(),
            'lknAttr',
            [
                'pixKey' => $this->getQrCodeFromSettings(give_get_option('lkn-payment-pix-type-setting'), give_get_option('lkn-payment-pix-key'), give_get_option('lkn-payment-pix-name-setting'), give_get_option('lkn-payment-pix-city-setting')),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        try {
            if (empty($gatewayData['pix-payment-gateway-id'])) {
                throw new PaymentGatewayException(__('Payment ID is required.', 'pix-give'));
            }

            return new PaymentPending();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'pix-give'), $errorMessage)
            ]);

            throw new PaymentGatewayException($errorMessage);
        }
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        return new PaymentRefunded();
    }
}
