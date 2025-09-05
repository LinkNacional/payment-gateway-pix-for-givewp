<?php

namespace Pgpfg\PGPFGForGivewp\Admin;

use Pgpfg\PGPFGForGivewp\Includes\PGPFGHelperClass;
use Pgpfg\PGPFGForGivewp\Includes\PGPFGivePaghiperHelper;
use WP_Error;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    PGPFGForGivewp
 * @subpackage PGPFGForGivewp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PGPFGForGivewp
 * @subpackage PGPFGForGivewp/admin
 * @author     Link Nacional <contato@linknacional.com>
 */
final class PGPFGForGivewpAdmin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name . '-admin';
        $this->version = $version;
    }

    private function merge_pro_translations($translation_array)
    {
        // Verificar se existe traduções do plugin PRO
        if (isset($GLOBALS['pgpfg_pro_translations']) && is_array($GLOBALS['pgpfg_pro_translations'])) {
            $pro_translations = $GLOBALS['pgpfg_pro_translations'];

            // Mesclar subtitle
            if (isset($pro_translations['subtitle']) && is_array($pro_translations['subtitle'])) {
                $translation_array['subtitle'] = array_merge(
                    $translation_array['subtitle'],
                    $pro_translations['subtitle']
                );
            }

            // Mesclar description
            if (isset($pro_translations['description']) && is_array($pro_translations['description'])) {
                $translation_array['description'] = array_merge(
                    $translation_array['description'],
                    $pro_translations['description']
                );
            }

            // Mesclar join
            if (isset($pro_translations['join']) && is_array($pro_translations['join'])) {
                $translation_array['join'] = array_merge(
                    $translation_array['join'],
                    $pro_translations['join']
                );
            }

            // Mesclar outras propriedades (translations, etc.)
            foreach ($pro_translations as $key => $value) {
                if (!in_array($key, ['subtitle', 'description', 'join'])) {
                    $translation_array[$key] = $value;
                }
            }
        }

        return $translation_array;
    }

    /**
     * Add setting to new section 'Custom Settings' of 'General' Tab.
     *
     * @param mixed $settings
     *
     * @return array
     */
    public function add_setting_into_new_section($settings)
    {
        switch (give_get_current_setting_section()) {
            case 'lkn-payment-pix':
                // Verifique se a configuração específica já está no array
                wp_enqueue_script('PGPFGForGivewpAdminSettingsScript', plugin_dir_url(__FILE__) . 'js/PGPFGForGivewpAdminSettings.js', array('jquery', 'wp-api'), $this->version, false);

                $translation_array = array(
                    'seeLogs' => __('See logs', 'payment-gateway-pix-for-givewp'),
                    'subtitle' => array(
                        'lkn-payment-pix-type-setting' => __("Choose the type of key you'll use to receive Pix payments", 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-key' => __("Enter the Pix key created in your bank that will be used to receive donations", 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-name-setting' => __("Recipient name", 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-city-setting' => __("Recipient city", 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-log-setting' => __('Saves transactions to the logs.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-details-setting' => __('Enable to show billing details on donation forms.', 'payment-gateway-pix-for-givewp'),
                        //free
                        'lkn-payment-pix-environment' => __('Select the environment in which the payment gateway will operate.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-logs' => __('Enable transaction logging', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-debug' => __('Displays details of gateway operations in the console.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-maxipago-key' => __('Store identification key', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-maxipago-id' => __('Store identification credential', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-bb-client-id' => __('Required to authenticate requests to the BB API.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-bb-client-secret' => __('Required to authenticate requests to the BB API.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-bb-developer-key' => __('Required to authenticate requests to the BB API.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-bb-pix-key' => __('The provided Pix key must be registered and active in Banco do Brasil.', 'payment-gateway-pix-for-givewp')
                    ),
                    'description' => array(
                        'lkn-payment-pix-details-setting' => __('Enable to show billing details on donation forms.', 'payment-gateway-pix-for-givewp'),
                        //free
                        'lkn-payment-pix-environment' => __('Development for test environment, Production for real donations.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-logs' => __('When enabled, all payment gateway operations will be recorded in the logging system.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-debug' => __('Enables detailed messages in the console with information about payment gateway operations.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-maxipago-key' => __('The Merchant Key is a secret credential provided by MaxiPago, used to authenticate all requests from your store to the payment API.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-maxipago-id' => __('The Merchant ID is the unique identifier provided by MaxiPago that represents your online store on the payment platform.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-bb-client-id' => __('This is the code that identifies your store in the Banco do Brasil system.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-bb-client-secret' => __('BB API secret credential. Use with Client ID for secure authentication.', 'payment-gateway-pix-for-givewp'),
                        'lkn-payment-pix-bb-developer-key' => __('The Developer Key is a security credential provided by Banco do Brasil to authenticate your application with the Payment API.', 'payment-gateway-pix-for-givewp')
                    ),
                    'join' => array(
                        'lkn-payment-pix-key' => 'with-previous',
                        'lkn-payment-pix-type-setting' => 'with-next'
                    )
                );
                $translation_array = $this->merge_pro_translations($translation_array);
                wp_localize_script('PGPFGForGivewpAdminSettingsScript', 'pgpfgTranslations', $translation_array);
                $exists = false;
                $pro_plugin_active = function_exists('is_plugin_active') && is_plugin_active('payment-gateway-pix-for-givewp-pro/payment-gateway-pix-for-givewp-pro.php');

                $all_pages = PGPFGivePaghiperHelper::get_all_pages_for_select();
                $paghiper_page = get_page_by_title('PagHiper Pix');
                $paghiper_page_id = $paghiper_page ? $paghiper_page->ID : '';

                foreach ($settings as $setting) {
                    if (isset($setting['id']) && 'lkn-payment-pix-type-setting' === $setting['id']) {
                        $exists = true;
                        break;
                    }
                }
                // Adicione as configurações apenas se ainda não existirem
                if (! $exists) {
                    $settings[] = array(
                        'type' => 'checkbox',
                        'id' => 'lkn-pix-menu',
                        'name' => '
                        <div = class="lkn-container-menu">
                            <div class="lkn-menu-toggle">☰
                                <div class="lkn-menu-container-mobile"></div>
                            </div>
                            <ul class="lkn-pix-menu">
                                <li id="0" class="lkn-pix-menu-ativo">Pix QRCode (free)</li>
                                <li id="1">PagHiper</li>
                                <li id="2">Pro Settings</li>
                                <li id="3">Pix MaxiPago</li>
                                <li id="4">Pix Banco do Brasil(BB)</li>
                                <li id="5">Pix Cielo</li>
                                <li id="6">Pix E-rede</li>
                            </ul>
                        </div>'
                    );

                    $settings[] = array(
                        'type' => 'title',
                        'id' => 'lkn-payment-pix',
                        "title" => __('PIX Key Settings', 'payment-gateway-pix-for-givewp'),
                    );

                    $settings[] = array(
                        'name' => __('Type of Key', 'payment-gateway-pix-for-givewp'),
                        'desc' => __('Choose which type of key you will use to receive the PIX.', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-type-setting',
                        'type' => 'select',
                        'default' => 'tel',
                        'options' => array(
                            'tel' => __('Phone', 'payment-gateway-pix-for-givewp'),
                            'cpf' => __('CPF', 'payment-gateway-pix-for-givewp'),
                            'cnpj' => __('CNPJ', 'payment-gateway-pix-for-givewp'),
                            'email' => __('Email', 'payment-gateway-pix-for-givewp')
                        ),
                        'subtitle' => __("Choose the type of key you'll use to receive Pix payments", 'payment-gateway-pix-for-givewp'),
                        'join' => 'with-next'

                    );

                    $settings[] = array(
                        'name' => __('Pix Key', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-key',
                        'desc' => __('Enter the Pix key created in your bank that will be used to receive donations.', 'payment-gateway-pix-for-givewp'),
                        'type' => 'text',
                        'attributes' => array(
                            'required' => 'required'
                        ),
                        'subtitle' => __("Enter the Pix key created in your bank that will be used to receive donations", 'payment-gateway-pix-for-givewp'),
                        'join' => 'with-previous'
                    );

                    $settings[] = array(
                        'name' => __('Recipient Name', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-name-setting',
                        'desc' => __('Enter the full name of the Pix beneficiary.', 'payment-gateway-pix-for-givewp'),
                        'type' => 'text',
                        'attributes' => array(
                            'required' => 'required'
                        ),
                        'subtitle' => __("Recipient Name", 'payment-gateway-pix-for-givewp')
                    );

                    $settings[] = array(
                        'name' => __("Recipient city ", 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-city-setting',
                        'desc' => __('Enter the name of the city of the Pix key beneficiary.', 'payment-gateway-pix-for-givewp'),
                        'type' => 'text',
                        'subtitle' => __("Recipient City", 'payment-gateway-pix-for-givewp')
                    );

                    $settings[] = array(
                        'name' => __('Enable Debug Mode', 'payment-gateway-pix-for-givewp'),
                        'desc' => __('When this feature is enabled, the plugin will record transaction logs, ideal for error identification.', 'payment-gateway-pix-for-givewp') . ((give_get_option('lkn-payment-pix-log-setting') === 'enabled' && file_exists(give_get_option('pgpfg_for_givewp_last_log')) && filesize(give_get_option('pgpfg_for_givewp_last_log'))) ? (' (<a href="#" id="check-logs">' . __('Check Last Log', 'payment-gateway-pix-for-givewp') . '</a>)') : ''),
                        'id' => 'lkn-payment-pix-log-setting',
                        'type' => 'radio_inline',
                        'default' => 'disabled',
                        'options' => array(
                            'enabled' => __('Enable', 'payment-gateway-pix-for-givewp'),
                            'disabled' => __('Disable', 'payment-gateway-pix-for-givewp')
                        ),
                        'subtitle' => __('Saves transactions to the logs.', 'payment-gateway-pix-for-givewp')
                    );

                    $settings[] = array(
                        'name' => __('Collect Billing Details', 'payment-gateway-pix-for-givewp'),
                        'desc' => __('Select whether billing details should be added to donation forms (classic and legacy forms).', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-details-setting',
                        'type' => 'radio_inline',
                        'default' => 'disabled',
                        'options' => array(
                            'enabled' => __('Enable', 'payment-gateway-pix-for-givewp'),
                            'disabled' => __('Disable', 'payment-gateway-pix-for-givewp')
                        ),
                        'subtitle' => __('Enable to show billing details on donation forms.', 'payment-gateway-pix-for-givewp'),
                        'description' => __('Enable to show billing details on donation forms.', 'payment-gateway-pix-for-givewp')
                    );

                    $settings[] = array(
                        'id' => 'lkn-payment-pix',
                        'type' => 'sectionend'
                    );

                    $settings[] = array(
                        'type' => 'title',
                        'id' => 'lkn_pgpf_paghiper',
                        'title' => __('PagHiper Settings', 'payment-gateway-pix-for-givewp')
                    );

                    $settings[] = array(
                        'name' => __('PagHiper API Key', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn_pgpf_paghiper_api_key_setting_field',
                        'desc' => __('PagHiper service API key', 'payment-gateway-pix-for-givewp'),
                        'type' => 'api_key',
                    );

                    $settings[] = array(
                        'name' => __('PagHiper Token', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn_pgpf_paghiper_token_setting_field',
                        'desc' => __('PagHiper service API token', 'payment-gateway-pix-for-givewp'),
                        'type' => 'api_key',
                    );

                    $settings[] = array(
                        'name' => __('Transaction Description', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn_pgpf_paghiper_desc_setting_field',
                        'desc' => __('The description that will appear on the customer\'s boleto/PIX', 'payment-gateway-pix-for-givewp'),
                        'type' => 'text',
                        'default' => __('Donation', 'payment-gateway-pix-for-givewp'),
                    );

                    $settings[] = array(
                        'name' => __('Default Due Date for Issued Boletos', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn_pgpf_paghiper_due_date_setting_field',
                        'desc' => __('Calendar days until expiration, maximum value until expiration is 400 days', 'payment-gateway-pix-for-givewp'),
                        'type' => 'number',
                        'default' => '1',
                    );

                    $settings[] = array(
                        'name' => __('PIX Fixed Fee', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn_pgpf_paghiper_fee_pix_setting_field',
                        'desc' => __('Additional fee charged to the customer for using PIX as payment method. Ex.: 2.0 (two reais). Note: Use dot (.) to separate decimal places', 'payment-gateway-pix-for-givewp'),
                        'type' => 'number',
                        'default' => '0',
                    );

                    $settings[] = array(
                        'name' => __('Boleto Fixed Fee', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn_pgpf_paghiper_fee_bol_setting_field',
                        'desc' => __('Additional fee charged to the customer for using boleto as payment method. Ex.: 2.0 (two reais). Note: Use dot (.) to separate decimal places', 'payment-gateway-pix-for-givewp'),
                        'type' => 'number',
                        'default' => '0',
                    );
                    $settings[] = array(
                        "name" => __('PIX Payment Page', 'payment-gateway-pix-for-givewp'),
                        'id' => "lkn_pgpf_paghiper_select_template_pix",
                        'default' => $paghiper_page_id,
                        'type' => "select",
                        'options' => count($all_pages) > 0 ? $all_pages : array(),
                        'desc' => __('Please insert the [lkn_pgpf_give_paghiper_pix] shortcode on the page you want to select', 'payment-gateway-pix-for-givewp')
                    );

                    $settings[] = array(
                        'name' => __('Debug Mode', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn_pgpf_paghiper_debug',
                        'desc' => __('Enable debug environment. <a id="lkn-give-debug">Transaction log.</a>', 'payment-gateway-pix-for-givewp'),
                        'type' => 'radio',
                        'default' => 'disabled',
                        'options' => array(
                            'enabled' => __('Enable', 'payment-gateway-pix-for-givewp'),
                            'disabled' => __('Disable', 'payment-gateway-pix-for-givewp'),
                        ),
                    );

                    $settings[] = array(
                        'id' => 'lkn_pgpf_paghiper',
                        'type' => 'sectionend',
                    );

                    //PRO SETTINGS free version
                    if (!$pro_plugin_active) {

                        $settings[] = array(
                            'type' => 'title',
                            'id' => 'lkn-payment-pix-general',
                            'title' => 'Pro Settings',
                            'give',
                        );

                        $settings[] = array(
                            'name' => __('License', 'payment-gateway-pix-for-givewp'),
                            'id' => 'lkn-payment-pix-license-setting-free',
                            'type' => 'password',
                            // translators: %1$s: start link, %2$s: end link
                            'desc' => sprintf(__('Enter the license acquired at Link Nacional %1$sLearn more%2$s.', 'payment-gateway-pix-for-givewp'), '<a target="_blank" href=https://www.linknacional.com.br/wordpress/givewp/>', '</a>')
                        );

                        $settings[] = array(
                            'name' => __('Environment', 'payment-gateway-pix-for-givewp'),
                            'id' => 'lkn-payment-pix-environment',
                            'type' => 'select',
                            'options' => array(
                                'app' => __('Production', 'payment-gateway-pix-for-givewp'),
                                'sandbox' => __('Development', 'payment-gateway-pix-for-givewp'),
                            ),
                            'subtitle' => __('Select the environment in which the payment gateway will operate.', 'payment-gateway-pix-for-givewp'),
                            'description' => __('Development for test environment, Production for real donations.', 'payment-gateway-pix-for-givewp')
                        );

                        $settings[] = array(
                            'name' => __('Enable Logs', 'payment-gateway-pix-for-givewp'),
                            'id' => 'lkn-payment-pix-logs',
                            'desc' => __('When enabled, all transactions will be logged, ideal for error identification. Default: Disabled', 'payment-gateway-pix-for-givewp'),
                            'type' => 'radio_inline',
                            'default' => 'disabled',
                            'options' => array(
                                'enabled' => __('Enable', 'payment-gateway-pix-for-givewp'),
                                'disabled' => __('Disable', 'payment-gateway-pix-for-givewp')
                            ),
                            'subtitle' => __('Enable transaction logging', 'payment-gateway-pix-for-givewp'),
                            'description' => __('When enabled, all payment gateway operations will be recorded in the logging system.', 'payment-gateway-pix-for-givewp')
                        );

                        $settings[] = array(
                            'name' => __('Enable Advanced Debugging (JS Console)', 'payment-gateway-pix-for-givewp'),
                            'id' => 'lkn-payment-pix-debug',
                            'desc' => __('When enabled, the console will have detailed information about the operations of the PIX payment gateway. Default: Disabled', 'payment-gateway-pix-for-givewp'),
                            'type' => 'radio_inline',
                            'default' => 'disabled',
                            'options' => array(
                                'enabled' => __('Enable', 'payment-gateway-pix-for-givewp'),
                                'disabled' => __('Disable', 'payment-gateway-pix-for-givewp')
                            ),
                            'subtitle' => __('Displays details of gateway operations in the console.', 'payment-gateway-pix-for-givewp'),
                            'description' => __('Enables detailed messages in the console with information about payment gateway operations.', 'payment-gateway-pix-for-givewp')
                        );

                        $settings[] = array(
                            'id' => 'lkn-payment-pix-sectionend',
                            'type' => 'sectionend'
                        );

                        $settings[] = array(
                            'type' => 'title',
                            'id' => 'lkn-payment-pix-maxipago',
                            'title' => 'Maxipago Settings',
                        );

                        $settings[] = array(
                            'name' => 'Maxipago Merchant Key',
                            'id' => 'lkn-payment-pix-maxipago-key',
                            'desc' => __('Unique key linked to your store in Maxipago.', 'payment-gateway-pix-for-givewp'),
                            'type' => 'password',
                            'subtitle' => __('Store identification key', 'payment-gateway-pix-for-givewp'),
                            'description' => __('The Merchant Key is a secret credential provided by MaxiPago, used to authenticate all requests from your store to the payment API.', 'payment-gateway-pix-for-givewp')
                        );

                        $settings[] = array(
                            'name' => 'Maxipago Merchant Id',
                            'id' => 'lkn-payment-pix-maxipago-id',
                            'desc' => __('Unique identifier used to identify your store in Maxipago.', 'payment-gateway-pix-for-givewp'),
                            'type' => 'password',
                            'subtitle' => __('Store identification credential', 'payment-gateway-pix-for-givewp'),
                            'description' => __('The Merchant ID is the unique identifier provided by MaxiPago that represents your online store on the payment platform.', 'payment-gateway-pix-for-givewp')
                        );
                        $settings[] = array(
                            'id' => 'lkn-payment-pix-sectionend',
                            'type' => 'sectionend'
                        );

                        $settings[] = array(
                            'type' => 'title',
                            'id' => 'lkn-payment-pix-bb',
                            'title' => 'Banco do Brasil PIX API',
                        );

                        $settings[] = array(
                            'name' => 'BB Client Id',
                            'id' => 'lkn-payment-pix-bb-client-id',
                            'desc' => __('Unique identifier used to identify your account at Banco do Brasil.', 'payment-gateway-pix-for-givewp'),
                            'type' => 'password',
                            'subtitle' => __('Required to authenticate requests to the BB API.', 'payment-gateway-pix-for-givewp'),
                            'description' => __('This is the code that identifies your store in the Banco do Brasil system.', 'payment-gateway-pix-for-givewp')
                        );

                        $settings[] = array(
                            'name' => 'BB Client Secret',
                            'id' => 'lkn-payment-pix-bb-client-secret',
                            'desc' => __('Private key used to authenticate integrations with Banco do Brasil services.', 'payment-gateway-pix-for-givewp'),
                            'type' => 'password',
                            'subtitle' => __('Required to authenticate requests to the BB API.', 'payment-gateway-pix-for-givewp'),
                            'description' => __('BB API secret credential. Use with Client ID for secure authentication.', 'payment-gateway-pix-for-givewp')
                        );

                        $settings[] = array(
                            'name' => 'BB Developer Key',
                            'id' => 'lkn-payment-pix-bb-developer-key',
                            'desc' => __('Key used by developers to access Banco do Brasil APIs.', 'payment-gateway-pix-for-givewp'),
                            'type' => 'password',
                            'subtitle' => __('Required to authenticate requests to the BB API.', 'payment-gateway-pix-for-givewp'),
                            'description' => __('The Developer Key is a security credential provided by Banco do Brasil to authenticate your application with the Payment API.', 'payment-gateway-pix-for-givewp')
                        );

                        $settings[] = array(
                            'name' => 'BB Pix Key',
                            'id' => 'lkn-payment-pix-bb-pix-key',
                            'type' => 'text',
                            // translators: %1$s: start link, %2$s: end link    
                            'desc' => sprintf(__('Pix key linked to the client and registered at Banco do Brasil. It must be active for the transaction to be processed correctly. %1$sLearn more%2$s', 'payment-gateway-pix-for-givewp'), '<a target="_blank" href="https://apoio.developers.bb.com.br/referency/post/648385d0de39c800131d8579">', '</a>'),
                            'subtitle' => __('The provided Pix key must be registered and active in Banco do Brasil.', 'payment-gateway-pix-for-givewp')
                        );

                        $settings[] = array(
                            'id' => 'lkn-payment-pix-sectionend',
                            'type' => 'sectionend'
                        );
                        $settings[] = array(
                            'type' => 'title',
                            'id' => 'lkn-payment-pix-bb',
                            'title' => 'Cielo PIX API',
                        );

                        $settings[] = array(
                            'name' => 'Cielo Merchant ID',
                            'id' => 'lkn-payment-pix-cielo-merchant-id',
                            'desc' => __('Unique identifier of your store on Cielo.', 'payment-gateway-pix-for-givewp-pro'),
                            'type' => 'password'
                        );

                        $settings[] = array(
                            'name' => 'Cielo Merchant Key',
                            'id' => 'lkn-payment-pix-cielo-merchant-key',
                            'desc' => __('Chave de acesso da sua loja na Cielo.', 'payment-gateway-pix-for-givewp-pro'),
                            'type' => 'password'
                        );
                        $settings[] = array(
                            'id' => 'lkn-payment-pix-sectionend',
                            'type' => 'sectionend'
                        );

                        $settings[] = array(
                            'type' => 'title',
                            'id' => 'lkn-payment-pix-pro-bb',
                            'title' => 'Cielo PIX API',
                        );

                        $settings[] = array(
                            'name' => "E-rede PV",
                            'id' => 'lkn-payment-pix-pro-erede-pv',
                            'desc' => __('Unique identifier of your merchant within Rede', 'payment-gateway-pix-for-givewp-pro'),
                            'type' => 'password'
                        );

                        $settings[] = array(
                            'name' => "E-rede Token",
                            'id' => 'lkn-payment-pix-pro-erede-token',
                            'desc' => __('Secret key associated with your PV', 'payment-gateway-pix-for-givewp-pro'),
                            'type' => 'password'
                        );

                        $settings[] = array(
                            'id' => 'lkn-payment-pix-pro-sectionend',
                            'type' => 'sectionend'
                        );
                    }
                }
                break;
        }

        return $settings;
    }

    /**
     * Add new section to "General" setting tab.
     *
     * @param mixed $sections
     *
     * @return array
     */
    public function add_new_setting_section($sections)
    {
        // Separar palavras com travessão no atributo $sections
        $sections['lkn-payment-pix'] = __('Pix QR Code', 'payment-gateway-pix-for-givewp');

        return $sections;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PGPFGForGivewp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The PGPFGForGivewp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pgpfg-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PGPFGForGivewp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The PGPFGForGivewp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pgpfg-admin.js', array('jquery', 'wp-i18n'), $this->version, false);
        wp_set_script_translations($this->plugin_name, 'payment-gateway-pix-for-givewp', PGPFG_PIX_LANGUAGE_DIR);

        $logPath = give_get_option('pgpfg_for_givewp_last_log_url');
        $wp_error = false;
        $remote = null;
        if (false !== $logPath) {
            $remote = wp_remote_get($logPath);

            if (gettype($remote) === gettype(new WP_Error())) {
                PGPFGHelperClass::log(
                    'info',
                    array(
                        'Remote Response' => $remote,
                        'log url' => $logPath,
                        'log path' => give_get_option('pgpfg_for_givewp_last_log')
                    )
                );

                $wp_error = $remote;
            }
        }
        $logContents = wp_remote_retrieve_body($remote);

        wp_localize_script(
            $this->plugin_name,
            'lknAttr',
            array(
                'logContents' => ($wp_error ? wp_json_encode(array(
                    'Error' => 'Error retrieving log'
                ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : $logContents)
            )
        );
    }
}
