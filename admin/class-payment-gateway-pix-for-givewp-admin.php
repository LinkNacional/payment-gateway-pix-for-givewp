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
class Payment_Gateway_Pix_For_Givewp_Admin
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

        $this->plugin_name = $plugin_name.'-admin';
        $this->version = $version;

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
            // Separar nome composto com travessão na área de configurações
            case 'lkn-payment-pix':
                $settings[] = array(
                    'type' => 'title',
                    'id' => 'lkn-payment-pix',
                );

                $settings[] = array(
                    'name' => 'Tipo de Chave',
                    'id' => 'lkn-payment-pix-type-setting',
                    'desc' => 'Insira a o tipo da sua chave pix.',
                    'type' => 'select',
                    'options' => array(
                        'Telefone',
                        'CPF',
                        'CNPJ',
                        'E-mail',
                        'Outro',
                    ),
                );

                $settings[] = array(
                    'name' => 'Chave Pix',
                    'id' => 'lkn-payment-pix-key',
                    'desc' => 'Insira a chave pix que deseja utilizar nas doações.',
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => 'Nome do Beneficiário',
                    'id' => 'lkn-payment-pix-name-setting',
                    'desc' => 'Insira o nome do beneficiário da chave.',
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => 'Cidade do Beneficiário',
                    'id' => 'lkn-payment-pix-city-setting',
                    'desc' => 'Insira a cidade do beneficiário da chave.',
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => 'Identificador de Pagamento (opcional)',
                    'id' => 'lkn-payment-pix-paymentid-setting',
                    'desc' => 'Insira o identificador de pagamento do banco, não obrigatório.',
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => 'Habilitar Modo de Depuração',
                    'id' => 'lkn-payment-pix-log-setting',
                    'type' => 'checkbox',
                    'desc' => 'Clique para habilitar o modo de depuração',
                );

                $settings[] = array(
                    'name' => 'Coletar Detalhes de Cobrança',
                    'id' => 'lkn-payment-pix-details-setting',
                    'type' => 'checkbox',
                    'desc' => 'Clique para adicionar detalhes de endereço de cobrança aos forms.',
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
    public function add_new_setting_section($sections)
    {
        // Separar palavras com travessão no atributo $sections
        $sections['lkn-payment-pix'] = 'Pix QR Code';

        return $sections;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

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
    public function enqueue_scripts()
    {

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

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/payment-gateway-pix-for-givewp-admin.js', array( 'jquery' ), $this->version, false);

    }

}
