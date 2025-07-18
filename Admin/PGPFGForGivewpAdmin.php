<?php

namespace Pgpfg\PGPFGForGivewp\Admin;

use Pgpfg\PGPFGForGivewp\Includes\PGPFGHelperClass;
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
final class PGPFGForGivewpAdmin {
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
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name . '-admin';
        $this->version = $version;
    }

    /**
     * Add setting to new section 'Custom Settings' of 'General' Tab.
     *
     * @param mixed $settings
     *
     * @return array
     */
    public function add_setting_into_new_section($settings) {
        switch (give_get_current_setting_section()) {
            case 'lkn-payment-pix':
                // Verifique se a configuração específica já está no array
                wp_enqueue_script('PGPFGForGivewpAdminSettingsScript', plugin_dir_url(__FILE__) . 'js/PGPFGForGivewpAdminSettings.js', array('jquery', 'wp-api'), $this->version, false);

                $translation_array = array(
                    'seeLogs' => __('See logs', 'payment-gateway-pix-for-givewp')
                );

                wp_localize_script('PGPFGForGivewpAdminSettingsScript', 'pgpfgTranslations', $translation_array);
                $exists = false;
                $pro_plugin_active = function_exists('is_plugin_active') && is_plugin_active('payment-gateway-pix-for-givewp-pro/payment-gateway-pix-for-givewp-pro.php');
                foreach ($settings as $setting) {
                    if (isset($setting['id']) && 'lkn-payment-pix-type-setting' === $setting['id']) {
                        $exists = true;
                        break;
                    }
                }
                // Adicione as configurações apenas se ainda não existirem
                if ( ! $exists) {
                        $settings[] = array(
                        'type' => 'checkbox',
                        'id' => 'lkn-pix-menu',
                        'name' =>'
                        <div = class="lkn-container-menu">
                            <div class="lkn-menu-toggle">☰
                                <div class="lkn-menu-container-mobile"></div>
                            </div>
                            <ul class="lkn-pix-menu">
                                <li id="0" class="lkn-pix-menu-ativo">Pix QRCode (free)</li>
                                <li id="1">Pro Settings</li>
                                <li id="2">Pix MaxiPago</li>
                                <li id="3">Pix Banco do Brasil(BB)</li>
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
                    );

                    $settings[] = array(
                        'name' => __('Pix Key', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-key',
                        'desc' => __('Enter the Pix key created in your bank that will be used to receive donations.', 'payment-gateway-pix-for-givewp'),
                        'type' => 'text',
                        'attributes' => array(
                            'required' => 'required'
                        )
                    );

                    $settings[] = array(
                        'name' => __('Recipient Name', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-name-setting',
                        'desc' => __('Enter the full name of the Pix beneficiary.', 'payment-gateway-pix-for-givewp'),
                        'type' => 'text',
                        'attributes' => array(
                            'required' => 'required'
                        )
                    );

                    $settings[] = array(
                        'name' => __("Recipient city (optional)", 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-city-setting',
                        'desc' => __('Enter the name of the city of the Pix key beneficiary.', 'payment-gateway-pix-for-givewp'),
                        'type' => 'text'
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
                        )
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
                        )
                    );

                    $settings[] = array(
                        'id' => 'lkn-payment-pix',
                        'type' => 'sectionend'
                    );
                
                    //PRO SETTINGS free version
                    if(!$pro_plugin_active){

                        $settings[] = array(
                        'type' => 'checkbox',
                        'id' => 'lkn-label-pro',
                        'name' =>'
                        <div = class="lkn-label-pro">
                            Disponivel apenas com a versão Pro
                        </div>'
                        );

                        $settings[] = array(
                        'type' => 'title',
                        'id' => 'lkn-payment-pix-general',
                        'title' => 'Pro Settings', 'give',
                        );

                        $settings[] = array(
                        'name' => __('License', 'payment-gateway-pix-for-givewp-pro'),
                        'id' => 'lkn-payment-pix-license-setting-free',
                        'type' => 'password',
                        'desc' => sprintf(__('Enter the license acquired at Link Nacional %sLink Nacional%s.', 'payment-gateway-pix-for-givewp'), '<a target="_blank" href=https://www.linknacional.com.br/wordpress/givewp/>', '</a>'),
                        );

                        $settings[] = array(
                        'name' => __('Environment', 'payment-gateway-pix-for-givewp'),
                        'id' => 'lkn-payment-pix-environment',
                        'type' => 'select',
                        'options' => array(
                            'app' => __('Production', 'payment-gateway-pix-for-givewp'),
                            'sandbox' => __('Development', 'payment-gateway-pix-for-givewp'),
                        )
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
                        )
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
                        )
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
                        )
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
                            'type' => 'password'
                        );

                        $settings[] = array(
                            'name' => 'Maxipago Merchant Id',
                            'id' => 'lkn-payment-pix-maxipago-id',
                            'desc' => __('Unique identifier used to identify your store in Maxipago.', 'payment-gateway-pix-for-givewp'),
                            'type' => 'password'
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
                            'id' => 'lkn-payment-pix-pro-bb-client-id',
                            'desc' => __('Unique identifier used to identify your account at Banco do Brasil.', 'payment-gateway-pix-for-givewp-pro'),
                            'type' => 'password'
                        );

                        $settings[] = array(
                            'name' => 'BB Client Secret',
                            'id' => 'lkn-payment-pix-pro-bb-client-secret',
                            'desc' => __('Private key used to authenticate integrations with Banco do Brasil services.', 'payment-gateway-pix-for-givewp-pro'),
                            'type' => 'password'
                        );

                        $settings[] = array(
                            'name' => 'BB Developer Key',
                            'id' => 'lkn-payment-pix-pro-bb-developer-key',
                            'desc' => __('Key used by developers to access Banco do Brasil APIs.', 'payment-gateway-pix-for-givewp-pro'),
                            'type' => 'password'
                        );

                        $settings[] = array(
                            'name' => 'BB Pix Key',
                            'id' => 'lkn-payment-pix-pro-bb-pix-key',
                            'type' => 'text',
                            'desc' => sprintf(__('Pix key linked to the client and registered at Banco do Brasil. It must be active for the transaction to be processed correctly. %sLearn more%s', 'payment-gateway-pix-for-givewp-pro'), '<a target="_blank" href="https://apoio.developers.bb.com.br/referency/post/648385d0de39c800131d8579">', '</a>')
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
    public function add_new_setting_section($sections) {
        // Separar palavras com travessão no atributo $sections
        $sections['lkn-payment-pix'] = __('Pix QR Code', 'payment-gateway-pix-for-givewp');

        return $sections;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void {
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
    public function enqueue_scripts(): void {
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