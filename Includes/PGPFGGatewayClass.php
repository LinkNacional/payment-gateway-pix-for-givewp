<?php

namespace Pgpfg\PGPFGForGivewp\Includes;

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
use Pgpfg\PGPFGForGivewp\Includes\PGPFGHelperClass;

/**
 * @inheritDoc
 */
final class PGPFGGatewayClass extends PaymentGateway {
    /**
     * @inheritDoc
     */
    public static function id(): string {
        return 'pix-payment-gateway';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return __('Pix QR Code', 'payment-gateway-pix-for-givewp');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string {
        return __('Pix', 'payment-gateway-pix-for-givewp');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string {
        load_template(PGPFG_PIX_PLUGIN_DIR . 'public/partials/pgpfg-public-display.php', true, array(
            'pixType' => give_get_option('lkn-payment-pix-type-setting'),
            'pixKey' => give_get_option('lkn-payment-pix-key'),
            'pixName' => give_get_option('lkn-payment-pix-name-setting'),
            'pixCity' => give_get_option('lkn-payment-pix-city-setting'),
            'pixId' => give_get_option('lkn-payment-pix-paymentid-setting'),
            'formId' => $formId,
            'isFormEnabled' => (give_get_option('lkn-payment-pix-details-setting') === 'enabled') ? true : false,
        ));

        return '';
    }

    /**
     * // TODO needs this function to appear in v3 forms
     * @since 3.0.0
     */
    public function enqueueScript(int $formId): void {
        wp_enqueue_script('qrcode', PGPFG_PIX_PLUGIN_URL . 'public/js/qrcode.js', array(), PGPFG_PIX_PLUGIN_VERSION, false);
        wp_enqueue_script(
            self::id(),
            PGPFG_PIX_PLUGIN_URL . 'includes/js/lkn-pix.js',
            array('wp-element', 'wp-i18n', 'qrcode'),
            PGPFG_PIX_PLUGIN_VERSION,
            true
        );

        wp_localize_script(
            self::id(),
            'lknAttr',
            array(
                'pluginUrl' => PGPFG_PIX_PLUGIN_URL,
                'pixType' => give_get_option('lkn-payment-pix-type-setting'),
                'pixKey' => give_get_option('lkn-payment-pix-key'),
                'pixName' => give_get_option('lkn-payment-pix-name-setting'),
                'pixCity' => give_get_option('lkn-payment-pix-city-setting'),
                'pixId' => give_get_option('lkn-payment-pix-paymentid-setting'),
            )
        );

        wp_set_script_translations(self::id(), 'payment-gateway-pix-for-givewp', PGPFG_PIX_LANGUAGE_DIR);
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand {
        try {
            if (empty($gatewayData['pix-payment-gateway-id'])) {
                throw new PaymentGatewayException(__('Payment ID is required.', 'payment-gateway-pix-for-givewp'));
            }

            PGPFGHelperClass::log(wp_json_encode(array(
                'Donation success' => $gatewayData['pix-payment-gateway-id']
            ), JSON_PRETTY_PRINT));

            return new PaymentPending();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create(array(
                'donationId' => $donation->id,
                'content' => sprintf('Donation failed. Reason: %1$s', esc_html($errorMessage))
            ));

            PGPFGHelperClass::log(wp_json_encode(array(
                'Donation failed' => $errorMessage,
                'Gateway Data' => $gatewayData,
                'Stack Trace' => $e->getTrace()
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

            throw new PaymentGatewayException(esc_html($errorMessage));
        }
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded {
        return new PaymentRefunded();
    }
}
