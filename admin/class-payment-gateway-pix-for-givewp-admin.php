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
                    'name' => 'Chave Pix',
                    'id' => 'lkn_payment_pix_key',
                    'desc' => 'Insira a chave Pix que deseja utilizar para receber as doações.',
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => 'Nome do Beneficiário',
                    'id' => 'lkn_payment_pix_key_name',
                    'desc' => 'Insira o nome que está cadastrado em seu banco.',
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => 'Cidade do Beneficiário',
                    'id' => 'lkn_payment_pix_key_city',
                    'desc' => 'Insira a cidade em que está cadastrada sua chave.',
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => 'Identificador do Pagamento (opcional)',
                    'id' => 'lkn_payment_pix_key_optid',
                    'desc' => 'Insira o identificador caso seja exigido pelo seu banco de escolha, por padrão será utilizado o identificador \'***\'.',
                    'type' => 'text',
                    'default' => '***',
                );

                $settings[] = array(
                    'name' => 'Habilitar Modo de Depuração',
                    'id' => 'lkn_payment_pix_log_toggle',
                    'type' => 'checkbox',
                    'desc' => 'Clique para habilitar o modo de depuração',
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
