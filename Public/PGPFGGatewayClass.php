<?php

namespace Pgpfg\PGPFGForGivewp\PublicView;

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
use Pgpfg\PGPFGForGivewp\Includes\PGPFGHelperClass ;

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
        wp_enqueue_style('pgpfg-public', PGPFG_PIX_PLUGIN_URL . 'Public/css/pgpfg-public.css');
        wp_enqueue_style('pgpfg-material-symbols-outlined', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0');

        // Array de argumentos
        $template_args = array(
            'pixType' => give_get_option('lkn-payment-pix-type-setting'),
            'pixKey' => give_get_option('lkn-payment-pix-key'),
            'pixName' => give_get_option('lkn-payment-pix-name-setting'),
            'pixCity' => give_get_option('lkn-payment-pix-city-setting'),
            'pixId' => give_get_option('lkn-payment-pix-paymentid-setting'),
            'formId' => $formId,
            'isFormEnabled' => (give_get_option('lkn-payment-pix-details-setting') === 'enabled') ? true : false,
        );

        // Construindo a string HTML
        $html = '<input
        type="hidden"
        id="pix_type"
        value="' . esc_attr($template_args['pixType']) . '"
        />
        <input
            type="hidden"
            id="pix_key"
            value="' . esc_attr($template_args['pixKey']) . '"
        />
        <input
            type="hidden"
            id="pix_name"
            value="' . esc_attr($template_args['pixName']) . '"
        />
        <input
            type="hidden"
            id="pix_city"
            value="' . esc_attr($template_args['pixCity']) . '"
        />
        <input
            type="hidden"
            id="pix_id"
            value="' . esc_attr($template_args['pixId']) . '"
        />
        <input
            type="hidden"
            name="gatewayData[pix-payment-gateway-id]"
            value="pix"
        />

        <div id="lkn-pix-form-donation">
            ' . ($template_args['isFormEnabled'] ? give_default_cc_address_fields($template_args['formId']) . '<br/>' : '') . '
            <legend>' . esc_html__('Pix Key:', 'payment-gateway-pix-for-givewp') . '</legend>
            <div class="pix-container" style="display:flex;flex-direction:column;justify-content:center;align-items:center">
                <p id="qr">' . esc_html__('Loading...', 'payment-gateway-pix-for-givewp') . '</p>
                <br/>
                <p id="pix"></p>
                <p id="copy-pix" style="display: none;">
                    <button id="toggle-viewing" type="button" title="' . esc_attr__('Show Pix', 'payment-gateway-pix-for-givewp') . '">
                        <span id="show" class="material-symbols-outlined" style="display: none;">Show</span>
                        <span id="hide" class="material-symbols-outlined">Hide</span>
                    </button>
                    <button id="copy-button" type="button" title="' . esc_attr__('Copy Pix', 'payment-gateway-pix-for-givewp') . '">
                        <span class="material-symbols-outlined">Copy</span>
                    </button>
                </p>
            </div>
        </div>';

        return $html;
    }

    /**
     * // TODO needs this function to appear in v3 forms
     * @since 3.0.0
     */
    public function enqueueScript(int $formId): void {
        wp_enqueue_script('qrcode', PGPFG_PIX_PLUGIN_URL . 'Public/js/qrcode.js', array(), PGPFG_PIX_PLUGIN_VERSION, false);
        wp_enqueue_script(
            self::id(),
            PGPFG_PIX_PLUGIN_URL . 'Public/js/lkn-pix.js',
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
                'pgpfgPublicCssUrl' => plugin_dir_url(__FILE__) . 'css/pgpfg-public.css'
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
