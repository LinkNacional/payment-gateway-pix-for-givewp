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
use PixHelperClass;

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

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        load_template(PAYMENT_GATEWAY_PIX_PLUGIN_DIR . 'public/partials/payment-gateway-pix-for-givewp-public-display.php', true, array(
            'pixType' => give_get_option('lkn-payment-pix-type-setting'),
            'pixKey' => give_get_option('lkn-payment-pix-key'),
            'pixName' => give_get_option('lkn-payment-pix-name-setting'),
            'pixCity' => give_get_option('lkn-payment-pix-city-setting'),
            'pixId' => give_get_option('lkn-payment-pix-paymentid-setting'),
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
                'pluginUrl' => PAYMENT_GATEWAY_PIX_PLUGIN_URL,
                'pixType' => give_get_option('lkn-payment-pix-type-setting'),
                'pixKey' => give_get_option('lkn-payment-pix-key'),
                'pixName' => give_get_option('lkn-payment-pix-name-setting'),
                'pixCity' => give_get_option('lkn-payment-pix-city-setting'),
                'pixId' => give_get_option('lkn-payment-pix-paymentid-setting'),
            ]
        );

        wp_set_script_translations(self::id(), 'payment-gateway-pix-for-givewp', PAYMENT_GATEWAY_PIX_LANGUAGE_DIR);
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

            PixHelperClass::log(wp_json_encode(array(
                'Donation success' => $gatewayData['pix-payment-gateway-id']
            ), JSON_PRETTY_PRINT));
            return new PaymentPending();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'pix-give'), $errorMessage)
            ]);

            PixHelperClass::log(wp_json_encode(array(
                'Donation failed' => $errorMessage,
                'Gateway Data' => $gatewayData,
                'Stack Trace' => $e->getTrace()
            ), JSON_PRETTY_PRINT));
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
