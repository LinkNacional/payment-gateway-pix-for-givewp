<?php

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;

/**
 * @inheritDoc
 */
final class PixGatewayClass extends PaymentGateway {
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
        return __('Pix QR Code', 'pix-give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string {
        return __('Pix', 'pix-give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string {
        // Step 1: add any gateway fields to the form using html.  In order to retrieve this data later the name of the input must be inside the key gatewayData (name='gatewayData[input_name]').
        // Step 2: you can alternatively send this data to the $gatewayData param using the filter `givewp_create_payment_gateway_data_{gatewayId}`.
        return "<div style='text-align: center;'><h3>Hello Linkers!</h3><p>Payment Gateway Pix for GiveWP</p></div>";
    }

    /**
     * // TODO needs this function to appear in v3 forms
     * @since 3.0.0
     */
    public function enqueueScript(int $formId): void {
        wp_enqueue_script(
            self::id(),
            PAYMENT_GATEWAY_PIX_PLUGIN_URL . 'includes/js/lkn-pix.js',
            array('wp-element', 'wp-i18n'),
            PAYMENT_GATEWAY_PIX_FOR_GIVEWP_VERSION,
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand {
        // try {
        //     // Step 1: Validate any data passed from the gateway fields in $gatewayData.  Throw the PaymentGatewayException if the data is invalid.
        //     if (empty($gatewayData['pix-payment-gateway-id'])) {
        //         throw new PaymentGatewayException(__('Payment ID is required.', 'pix-give'));
        //     }

        //     // Step 2: Create a payment with your gateway.
        //     $response = $this->exampleRequest(['transaction_id' => $gatewayData['pix-payment-gateway-id']]);

        //     // Step 3: Return a command to complete the donation. You can alternatively return PaymentProcessing for gateways that require a webhook or similar to confirm that the payment is complete. PaymentProcessing will trigger a Payment Processing email notification, configurable in the settings.

        //     return new PaymentComplete($response['transaction_id']);
        // } catch (Exception $e) {
        //     // Step 4: If an error occurs, you can update the donation status to something appropriate like failed, and finally throw the PaymentGatewayException for the framework to catch the message.
        //     $errorMessage = $e->getMessage();

        //     $donation->status = DonationStatus::FAILED();
        //     $donation->save();

        //     DonationNote::create([
        //         'donationId' => $donation->id,
        //         'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'pix-give'), $errorMessage)
        //     ]);

        //     throw new PaymentGatewayException($errorMessage);
        // }
        return new PaymentComplete();
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded {
        // Step 1: refund the donation with your gateway.
        // Step 2: return a command to complete the refund.
        return new PaymentRefunded();
    }

    /**
     * Example request to gateway
     */
    private function exampleRequest(array $data): array {
        return array_merge(array(
            'success' => true,
            'transaction_id' => '1234567890',
            'subscription_id' => '0987654321',
        ), $data);
    }
}
