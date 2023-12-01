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
use PixHelpers;

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
        $pix_opt  = give_get_option('lkn_payment_pix_key_optid');

        if(strlen($pix_opt) === 0) {
            $qr = PixHelpers::getQrCodeFromSettings(give_get_option('lkn_payment_pix_key'), give_get_option('lkn_payment_pix_key_name'), give_get_option('lkn_payment_pix_key_city'));
        } else {
            $qr = PixHelpers::getQrCodeFromSettings(give_get_option('lkn_payment_pix_key'), give_get_option('lkn_payment_pix_key_name'), give_get_option('lkn_payment_pix_key_city'), $pix_opt);
        }

        load_template(
            PAYMENT_GATEWAY_PIX_PLUGIN_URL . 'public/partials/payment-gateway-pix-for-givewp-public-display.php',
            true
        );
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
                'pixKey' => $this->getQrCodeFromSettings(),
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
