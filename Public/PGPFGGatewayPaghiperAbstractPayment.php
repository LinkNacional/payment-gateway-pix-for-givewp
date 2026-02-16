<?php

/**
 * @link      https://www.linknacional.com.br/
 *
 * @package    GivePaghiper
 * @subpackage GivePaghiper/PublicView
 */

namespace Pgpfg\PGPFGForGivewp\PublicView;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners\PaymentIntentPaymentFailed;
use Pgpfg\PGPFGForGivewp\Includes\PGPFGivePaghiperHelper;
use Pgpfg\PGPFGForGivewp\Includes\LknGivePaghiperLicense;
use WP_REST_Request;

/**
 * @package    GivePaghiper
 * @subpackage GivePaghiper/PublicView
 * @author     Link Nacional
 */
abstract class PGPFGGatewayPaghiperAbstractPayment extends PaymentGateway
{
    protected $id;
    protected $idName;
    protected $name;
    protected $pay_method_name;

    public function __construct(
        string $id,
        string $idName,
        string $name,
        string $pay_method_name,
        SubscriptionModule $subscription_module = null
    ) {
        $this->id = $id;
        $this->idName = $idName;
        $this->name = $name;
        $this->pay_method_name = $pay_method_name;

        parent::__construct($subscription_module);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return  $this->pay_method_name;
    }

    /**
     * Function that build the donation form.
     *
     * @param int   $form_id - the form identificator
     * @param array $args    - list of additional arguments
     *
     * @return mixed
     */
    public function render_form(int $formId, array $args): string
    {
        $id_prefix = ! empty($args['id_prefix']) ? $args['id_prefix'] : '';

        $configs = PGPFGivePaghiperHelper::get_configs();

        wp_enqueue_script($this->id . '-script-js', plugin_dir_url(__FILE__) . 'js/paghiper/lkn-pgpf-give-paghiper-public.js', array('jquery'), PGPFG_PIX_PLUGIN_VERSION, false);

        $pixFee = $configs['pixFee'];
        $bolFee = $configs['bolFee'];

        $token = $configs['token'];
        $apiKey = $configs['apiKey'];

        $alertMessagePix = esc_html__('Attention! It will not be possible to make recurring donations via Pix..', 'payment-gateway-pix-for-givewp');
        $alertMessageBol = esc_html__('Attention! It will not be possible to make recurring donations via Boleto.', 'payment-gateway-pix-for-givewp');
        $title = esc_html__('Payment Information', 'payment-gateway-pix-for-givewp');
        $secureNotice = esc_html__('Secure Donation via SSL Encryption.', 'payment-gateway-pix-for-givewp');
        $gatewayForm = $this->gateway_form();

        $scriptGlobals = array(
            'pix_fee' => $pixFee,
            'bol_fee' => $bolFee,
            'alert_pix' => $alertMessagePix,
            'alert_bol' => $alertMessageBol
        );

        wp_localize_script($this->id . '-script-js', 'scriptGlobals', $scriptGlobals);

        if (empty($apiKey) || empty($token)) {
            return Give()->notices->print_frontend_notice(
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__('Error:', 'payment-gateway-pix-for-givewp'),
                    esc_html__('PagHiper credentials not provided or invalid.', 'payment-gateway-pix-for-givewp')
                )
            );
        }
        if (! is_ssl()) {
            return Give()->notices->print_frontend_notice(
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__('Error:', 'payment-gateway-pix-for-givewp'),
                    esc_html__('Donation disabled due to lack of SSL (HTTPS).', 'payment-gateway-pix-for-givewp')
                )
            );
        }
        return "
            <fieldset id=\"give_cc_fields\" class=\"give-do-validate\">
                <legend>
                {$title}
                </legend>

                <div id=\"give_secure_site_wrapper\">
                    <span class=\"give-icon padlock\"></span>
                    <span>
                        {$secureNotice}
                    </span>
                </div>

                <div id=\"give-paghiper-single-cc-fields-{$id_prefix}\" class=\"give-paghiper-single-cc-field-wrap\">
                    {$gatewayForm}
                </div>
                <input type=\"hidden\" id=\"lkn-input-form\" name=\"gatewayData[is_multi]\" value=\"false\"> 
            </fieldset>
        ";
    }

    /**
     * Will be defined in classes that inherit this abstract class.
     *
     * @return mixed
     */
    protected function gateway_form(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData)
    {
        try {
            // Set the configs values.
            $configs = PGPFGivePaghiperHelper::get_configs();

            // Make sure we don't have any left over errors present.
            give_clear_errors();

            // Any errors?
            $errors = give_get_errors();

            if ($errors) {
                PGPFGivePaghiperHelper::regLog('error', 'payment', 'GiveWP Error',  wp_json_encode($errors, true));

                // Errors? Send back.
                throw new PaymentGatewayException(esc_html((wp_json_encode($errors))));
            }

            $apiKey = $configs['apiKey'];
            $expDate = $configs['expDate'];

            // Donation informations.
            $donPrice = $donation->amount->formatToDecimal();
            $donId = $donation->id;
            $donEmail = $donation->email;
            $donCurrency = give_get_currency($donation->formId, $donation);
            $donFirstName = $donation->firstName;
            $donLastName = $donation->lastName;
            $donGateway = $donation->gatewayId;

            $donGatewayName = ('lkn-pgpf-give-paghiper-pix' === $donGateway) ? 'Pix' : 'Boleto';

            // Verify the payment.
            if (empty($donId)) {
                // Record the error.
                PGPFGivePaghiperHelper::regLog('error', 'payment', 'Payment error. Payment creation failed before completing authorization.', wp_json_encode($gatewayData));

                // Problems? Send back.
                throw new PaymentGatewayException(esc_html__('Payment error. Payment creation failed before completing authorization.', 'payment-gateway-pix-for-givewp'));
            }

            // Verify the currency.
            if ('BRL' !== $donCurrency) {
                // Not suport international currencies.
                throw new PaymentGatewayException(esc_html__('Payment with foreign currency not supported.', 'payment-gateway-pix-for-givewp'));
            }

            $donPrice = number_format($donPrice, 2, '', '');

            if ($donPrice < 300) {
                // translators: %s is the payment gateway name (e.g., "Pix", "Boleto")
                throw new PaymentGatewayException(sprintf(esc_html__('The minimum amount for donations via %s is R$ 3.00.', 'payment-gateway-pix-for-givewp'), $donGatewayName));
            }

            // Attributes sanitizing.
            $donDescript = $configs['description'];
            $donDescript = wp_strip_all_tags($donDescript);
            $donEmail = filter_var($donEmail, \FILTER_SANITIZE_EMAIL);
            $donFullName = $donFirstName . ' ' . $donLastName;
            $donFullName = wp_strip_all_tags($donFullName);
            $donCpfCnpj = $gatewayData['lkn_give_primary_document'];
            $donCpfCnpj = preg_replace('/\D/', '', $donCpfCnpj);
            $donValidDocument = false;

            // If payment using Pix gateway, catch the pix fee, else, catch the slip fee.
            $donFee = ('lkn-pgpf-give-paghiper-pix' === $donGateway) ? $configs['pixFee'] : $configs['bolFee'];

            if (empty($donDescript)) {
                $donDescript = 'Doação';
            }

            // If have fee, add to total price.
            if (is_numeric($donFee)) {
                $donFee = number_format($donFee, 2, '', ''); // Format to cents for PagHiper request.
                $donPrice += $donFee;
            } else {
                $donFee = 0;
            }

            // Validate the CPF/CNPJ.
            if (strlen($donCpfCnpj) == 11) {
                $donValidDocument = PGPFGivePaghiperHelper::validate_cpf($donCpfCnpj);
            } else {
                $donValidDocument = PGPFGivePaghiperHelper::validate_cnpj($donCpfCnpj);
            }

            // In CPF/CNPJ not valid.
            if (false == $donValidDocument) {
                throw new PaymentGatewayException(esc_html__('The provided CPF/CNPJ is invalid', 'payment-gateway-pix-for-givewp'));
            }

            // Create a query arg with the donation ID to listener callback.
            $donListenerUrl = add_query_arg('id_for_paghiper_listener', $donId, site_url() . '/wp-json/lkn-pgpf-give-paghiper-listener/v1/notification');

            $header = array(
                'Accept' => 'application/json',
                'Accept-Charset' => 'UTF-8',
                'Accept-Encoding' => 'application/json',
                'Content-Type' => 'application/json;charset=UTF-8'
            );

            $orderIdPrefix = ('lkn-pgpf-give-paghiper-pix' === $donGateway) ? 'pix_' : 'bol_';

            // Initialize variable.
            $body = null;

            // Body for Pix.
            if ('lkn-pgpf-give-paghiper-pix' === $donGateway) {
                $body = array(
                    'apiKey' => $apiKey,                    // PagHiper Api Key.
                    'order_id' => $orderIdPrefix . $donId,  // Unique ID of order.
                    'payer_email' => $donEmail,             // Customer email.
                    'payer_name' => $donFullName,           // Customer name.
                    'payer_cpf_cnpj' => $donCpfCnpj,        // Customer CPF or CNPJ (only numbers).
                    'days_due_date' => $expDate,            // Number of days to expire the QR code (order).
                    'notification_url' => $donListenerUrl,  // Notification URL to update the transaction informations.
                    'partners_id' => '14P9ZE4C',            // Partner ID.
                    'items' => array(array(
                        'item_id' => '01',
                        'description' => $donDescript,      // Transaction description.
                        'quantity' => 1,
                        'price_cents' => $donPrice          // Price in cents.
                    ))
                );
            }

            // Body for Banking Slip.
            if ('lkn-pgpf-give-paghiper-slip' === $donGateway) {
                $body = array(
                    'apiKey' => $apiKey,                    // PagHiper Api Key.
                    'order_id' => $orderIdPrefix . $donId,  // Unique ID of order.
                    'payer_email' => $donEmail,             // Customer email.
                    'payer_name' => $donFullName,           // Customer name.
                    'payer_cpf_cnpj' => $donCpfCnpj,        // Customer CPF or CNPJ (only numbers).
                    'days_due_date' => $expDate,            // Number of days to expire the QR code (order).
                    'type_bank_slip' => 'boletoA4',         // Slip format.
                    'notification_url' => $donListenerUrl,  // Notification URL to update the transaction informations.
                    'partners_id' => '14P9ZE4C',            // Partner ID.
                    'items' => array(array(
                        'item_id' => '01',
                        'description' => $donDescript,      // Transaction description.
                        'quantity' => 1,
                        'price_cents' => $donPrice          // Price in cents.
                    ))
                );
            }

            $postUrl = ('lkn-pgpf-give-paghiper-pix' === $donGateway) ? $configs['urlPix'] . 'invoice/create/' : $configs['urlBol'] . 'transaction/create/';

            // Make POST request.
            $resultRequest = PGPFGivePaghiperHelper::connect_request($header, $body, $postUrl);

            // Register log.
            PGPFGivePaghiperHelper::regLog('info', 'payment', 'Gateway ID: ' . esc_html($donGateway) . ' - Response Request', wp_json_encode($resultRequest, true));

            if ('lkn-pgpf-give-paghiper-pix' === $donGateway) {
                $paghiperResult = $resultRequest->pix_create_request->result;
                $paghiperMsg = $resultRequest->pix_create_request->response_message;
                $paghiperTransaction = $resultRequest->pix_create_request->transaction_id;

                // Verifies if the Pix API not returned a success code.
                if ('success' != $paghiperResult) {
                    // translators: %s is the error message from PagHiper API
                    throw new PaymentGatewayException(sprintf(esc_html__('Donation failed. Reason: %s', 'payment-gateway-pix-for-givewp'), $paghiperMsg));
                }

                // Break the due date in YY/mm/dd.
                $dateBr = explode('-', $resultRequest->pix_create_request->due_date);

                // Array with Pix informations.
                $transactionArray = array(
                    'qrcode' => $resultRequest->pix_create_request->pix_code->qrcode_image_url,     // QR Code URL.
                    'key' => $resultRequest->pix_create_request->pix_code->emv,                     // Pix copy/paste code.
                    'transactionId' => $paghiperTransaction,                                        // PIX Transaction ID.
                    'donationId' => $donId,                                                         // PIX Donation ID.
                    'value' => $resultRequest->pix_create_request->value_cents / 100,               // Pix value.
                    'date' => $dateBr[2] . '/' . $dateBr[1] . '/' . $dateBr[0],                     // Pix date.
                    'title' => $donDescript,                                                        // Pix description.
                );

                $transactionArray = base64_encode(wp_json_encode($transactionArray));

                $transactionPage = empty($this->lkn_get_page()) ? get_permalink(give_get_option("lkn_pgpf_paghiper_pix_payment_page"), false) : $this->lkn_get_page();
                // URL to page with payment informations.[

                $dir = $transactionPage . $this->lkn_set_link($transactionPage, $transactionArray);

                $arrayMeta = array(
                    'result' => $paghiperResult,                 // Result code of transaction.
                    'transactionId' => $paghiperTransaction,     // ID of transaction.
                    'message' => $paghiperMsg,                   // Message of transaction.
                    'pix_page' => $dir                           // URL of pix payment page.
                );

                give_update_payment_meta($donId, 'lkn_pgpf_give_paghiper_response', wp_json_encode($arrayMeta));

                $donation->status = DonationStatus::PENDING();
                $donation->save();

                return new RedirectOffsite($dir);
            }
            if ('lkn-pgpf-give-paghiper-slip' === $donGateway) {
                $paghiperResult = $resultRequest->create_request->result;
                $paghiperMsg = $resultRequest->create_request->response_message;
                $paghiperTransaction = isset($resultRequest->create_request->transaction_id) ? $resultRequest->create_request->transaction_id : '';

                // Verifies if the Slip API not returned a success code.
                if ('success' != $paghiperResult) {
                    // translators: %s is the error message from PagHiper API
                    throw new PaymentGatewayException(sprintf(esc_html__('Donation failed. Reason: %s', 'payment-gateway-pix-for-givewp'), $paghiperMsg));
                }

                $dir = null;

                if (preg_match('/^https/', $resultRequest->create_request->bank_slip->url_slip)) {
                    $dir = $resultRequest->create_request->bank_slip->url_slip;
                } else {
                    $dir = $resultRequest->create_request->bank_slip->url_slip_pdf;
                }

                $arrayMeta = array(
                    'result' => $paghiperResult,                 // Result code of transaction.
                    'transactionId' => $paghiperTransaction,     // ID of transaction.
                    'message' => $paghiperMsg,                   // Message of transaction.
                    'bol_page' => $dir                           // URL of slip page.
                );

                give_update_payment_meta($donId, 'lkn_pgpf_give_paghiper_response', wp_json_encode($arrayMeta));

                // Redirect to page with banking slip.
                $donation->status = DonationStatus::PENDING();
                $donation->save();
                return new RedirectOffsite($dir);
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();

            // Register log.
            PGPFGivePaghiperHelper::regLog('error', 'payment', 'Erro no processamento do pagamento', $errorMsg);

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create(array(
                'donationId' => $donation->id,
                // translators: %s is the error message
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'payment-gateway-pix-for-givewp'), esc_html($errorMsg))
            ));

            throw new PaymentGatewayException(esc_html($errorMsg));
            exit;
        }
    }

    private function lkn_set_link($link, $value)
    {
        // Verifica se o link já possui parâmetros GET
        if (strpos($link, '?') !== false) {
            // Se o link já tiver parâmetros GET, adiciona o novo parâmetro
            $link_with_new_param = '&pix=' . $value;
        } else {
            // Se o link não tiver parâmetros GET, cria o link com o novo parâmetro
            $link_with_new_param = "?pix=" . $value;
        }

        return $link_with_new_param;
    }

    private function lkn_get_page()
    {
        // Obtém o ID da página a partir das opções
        $selected_page_id = give_get_option("lkn_pgpf_paghiper_select_template_pix");

        // Verifica se o ID está vazio e retorna se estiver
        if (empty($selected_page_id)) {
            return;
        }

        // Inicializa a variável para armazenar a página selecionada
        $selected_page = null;

        // Obtém todas as páginas
        $pages = get_pages();
        // Itera sobre as páginas para encontrar a página com o ID correspondente
        foreach ($pages as $page) {
            if ($page->ID == $selected_page_id) {
                $selected_page = $page;
                break;
            }
        }

        // Verifica se a página foi encontrada
        if (empty($selected_page)) {
            return;
        }

        // Obtém o permalink da página
        $permalink = $selected_page->guid;

        // Verifica se o permalink foi obtido corretamente
        if ($permalink) {
            return $permalink;
        } else {
            return;
        }
    }

    /**
     * @inheritDoc
     */
    public static function refundDonation(Donation $donation): PaymentRefunded
    {
        $donation->status = DonationStatus::REFUNDED();
        $donation->save();

        DonationNote::create(array(
            'donationId' => $donation->id,
            'content' => esc_html__('Donation refunded via PagHiper.', 'payment-gateway-pix-for-givewp')
        ));

        return new PaymentRefunded();
    }

    /**
     * Function that builds and executes a curl Query.
     *
     * @param array  $header   - contains headers info
     * @param string $url     - contains the url the query is consulting
     * @param string $query   - contains the Query to be executed
     *
     * @return array $response - contains Query data
     */
    public function connect_query($header, $url, $query)
    {
        try {
            // Make the request args.
            $args = array(
                'headers' => $header,
                'timeout' => 10,
                'redirection' => 5,
                'httpversion' => '1.1'
            );

            // Make the query.
            $response = wp_remote_get($url . $query, $args);

            return wp_remote_retrieve_body($response);
        } catch (Exception $e) {
            // Register log.
            PGPFGivePaghiperHelper::regLog('error', 'curl', 'GET Curl Error', $e->getMessage());

            return array();
        }
    }

    /**
     * Listens for a PagHiper notification on Wordpress init.
     *
     * @param WP_REST_Request $request
     */
    public static function listener(WP_REST_Request $request)
    {
        try {
            $configs = PGPFGivePaghiperHelper::get_configs();

            $urlParams = $request->get_params();

            $donationId = sanitize_text_field($urlParams['id_for_paghiper_listener']);

            if (empty($donationId)) {
                PGPFGivePaghiperHelper::regLog('error', 'listener', 'Falha no listener, ID não retornado.', '');

                return false;
            }

            // Find donation obj by ID.
            $donation = Donation::find($donationId);

            $meta = give_get_payment_meta($donationId, '_give_payment_gateway');

            // Get the gateway identifier: pix or slip.
            $donationGateway = $meta;
            $donationGateway = explode('-', $donationGateway);
            $donationGateway = end($donationGateway);

            $params = $request->get_body_params();

            // Register log.
            PGPFGivePaghiperHelper::regLog('info', 'listener', 'notification POST received', esc_html($donationGateway) . ' - Response Request: ' . wp_json_encode($params, true));

            // Body Params:
            $token = $configs['token'];
            $apiKey = sanitize_text_field($params['apiKey']);
            $transactionId = sanitize_text_field($params['transaction_id']);
            $notificationId = sanitize_text_field($params['notification_id']);

            // Header.
            $header = array(
                'Accept' => 'application/json',
                'Accept-Charset' => 'UTF-8',
                'Accept-Encoding' => 'application/json',
                'Content-Type' => 'application/json;charset=UTF-8'
            );

            // Body.
            $body = array(
                'token' => $token,
                'apiKey' => $apiKey,
                'transaction_id' => $transactionId,
                'notification_id' => $notificationId
            );

            // Initialize variable.
            $response = null;

            $postUrl = ('pix' === $donationGateway) ? $configs['urlPix'] . 'invoice/notification/' : $configs['urlBol'] . 'transaction/notification/';

            // Make POST request.
            $response = PGPFGivePaghiperHelper::connect_request($header, $body, $postUrl);

            // Register log.
            PGPFGivePaghiperHelper::regLog('info', 'listener', 'Listener notification POST - Response Request', wp_json_encode($response, true));

            $requestResult = $response->status_request->result;
            $requestMsg = $response->status_request->response_message;

            if ('success' != $requestResult) {
                give_insert_payment_note($donationId, 'Notificação recebida, erro na consulta da API verifique as chaves de acesso e os logs da transação.');

                PGPFGivePaghiperHelper::regLog('error', 'listener', 'Falha na atualização de status da doação #' . $donationId, 'Razão: ' . esc_html($requestMsg) . ' | Result: ' . wp_json_encode($requestResult, true));

                return false;
            }

            // Order informations.
            $orderCode = $response->status_request->http_code;          // Request http code.
            $orderId = $response->status_request->order_id;             // ID of PagHiper order.
            $orderStatus = $response->status_request->status ?? 'Pendente';           // Status of PagHiper order.

            give_insert_payment_note($donationId, 'Notificação recebida, status do pagamento atualizado para: ' . $orderStatus);

            // Att Give order according to PagHiper order.
            switch ($orderStatus) {
                case 'completed':
                case 'paid':
                    give_update_payment_meta($donationId, 'lkn_pgpf_give_paghiper_response', $orderCode . ':::' . $orderId . ':::Pagamento confirmado com sucesso');

                    $donation->status = DonationStatus::COMPLETE();
                    $donation->save();

                    break;
                case 'canceled':
                    give_update_payment_meta($donationId, 'lkn_pgpf_give_paghiper_response', $orderCode . ':::' . $orderId . ':::Falha no processamento do pagamento');

                    $donation->status = DonationStatus::FAILED();
                    $donation->save();

                    break;
                case 'refunded':
                    PGPFGGatewayPaghiperAbstractPayment::refundDonation($donation);

                    break;

                default:
                    return false;

                    break;
            }

            return true;
        } catch (Exception $e) {
            // Register log.
            PGPFGivePaghiperHelper::regLog('error', 'listener', 'Listener Error', $e->getMessage());

            return false;
        }
    }
}
