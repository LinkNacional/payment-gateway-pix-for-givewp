<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    Payment_Gateway_Pix_For_Givewp
 * @subpackage Payment_Gateway_Pix_For_Givewp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Payment_Gateway_Pix_For_Givewp
 * @subpackage Payment_Gateway_Pix_For_Givewp/admin
 * @author     Link Nacional <contato@linknacional.com>
 */
final class Payment_Gateway_Pix_For_Givewp_Admin {
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
            // Separar nome composto com travessão na área de configurações
            case 'lkn-payment-pix':
                $settings[] = array(
                    'type' => 'title',
                    'id' => 'lkn-payment-pix',
                );

                $settings[] = array(
                    'name' => __('Type of Key', 'payment-gateway-pix-for-givewp'),
                    'desc' => __('Insert the type of the pix key.', 'payment-gateway-pix-for-givewp'),
                    'id' => 'lkn-payment-pix-type-setting',
                    'type' => 'select',
                    'default' => 'tel',
                    'options' => array(
                        'tel' => __('Phone', 'payment-gateway-pix-for-givewp'),
                        'cpf' => __('CPF', 'payment-gateway-pix-for-givewp'),
                        'cnpj' => __('CNPJ', 'payment-gateway-pix-for-givewp'),
                        'email' => __('Email', 'payment-gateway-pix-for-givewp'),
                        'other' => __('Other', 'payment-gateway-pix-for-givewp'),
                    ),
                );

                $settings[] = array(
                    'name' => __('Pix Key', 'payment-gateway-pix-for-givewp'),
                    'id' => 'lkn-payment-pix-key',
                    'desc' => __('Insert the pix key that will be used on the donations.', 'payment-gateway-pix-for-givewp'),
                    'type' => 'text',
                );
                
                $settings[] = array(
                    'name' => __('Recipient Name', 'payment-gateway-pix-for-givewp'),
                    'id' => 'lkn-payment-pix-name-setting',
                    'desc' => __('Insert the name of the key\'s recipient.', 'payment-gateway-pix-for-givewp'),
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => __('Recipient City', 'payment-gateway-pix-for-givewp'),
                    'id' => 'lkn-payment-pix-city-setting',
                    'desc' => __('Insert the key recipient\'s city.', 'payment-gateway-pix-for-givewp'),
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => __('Payment Identificator (optional)', 'payment-gateway-pix-for-givewp'),
                    'id' => 'lkn-payment-pix-paymentid-setting',
                    'desc' => __('Insert the payment identificator, not required.', 'payment-gateway-pix-for-givewp'),
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => __('Enable Debug Mode', 'payment-gateway-pix-for-givewp'),
                    'desc' => __('Select if debug mode should be enabled.', 'payment-gateway-pix-for-givewp'),
                    'id' => 'lkn-payment-pix-log-setting',
                    'type' => 'radio_inline',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enable', 'payment-gateway-pix-for-givewp'),
                        'disabled' => __('Disable', 'payment-gateway-pix-for-givewp'),
                    ),
                );

                $settings[] = array(
                    'name' => __('Collect Billing Details', 'payment-gateway-pix-for-givewp'),
                    'desc' => __('Select if billing details should be added do the donation forms (classic and legacy forms).', 'payment-gateway-pix-for-givewp'),
                    'id' => 'lkn-payment-pix-details-setting',
                    'type' => 'radio_inline',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enable', 'payment-gateway-pix-for-givewp'),
                        'disabled' => __('Disable', 'payment-gateway-pix-for-givewp'),
                    ),
                );

                $settings[] = array(
                    'id' => 'lkn-payment-pix',
                    'type' => 'sectionend',
                );

                break;
        }// // End switch()

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
         * defined in Payment_Gateway_Pix_For_Givewp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Payment_Gateway_Pix_For_Givewp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/payment-gateway-pix-for-givewp-admin.css', array(), $this->version, 'all');
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
         * defined in Payment_Gateway_Pix_For_Givewp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Payment_Gateway_Pix_For_Givewp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/payment-gateway-pix-for-givewp-admin.js', array('jquery', 'wp-i18n'), $this->version, false);
        wp_set_script_translations($this->plugin_name, 'payment-gateway-pix-for-givewp', PAYMENT_GATEWAY_PIX_LANGUAGE_DIR);
    }
}
