<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    Payment_Gateway_Pix_For_Givewp
 * @subpackage Payment_Gateway_Pix_For_Givewp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Payment_Gateway_Pix_For_Givewp
 * @subpackage Payment_Gateway_Pix_For_Givewp/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
final class Payment_Gateway_Pix_For_Givewp
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Payment_Gateway_Pix_For_Givewp_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('PAYMENT_GATEWAY_PIX_FOR_GIVEWP_VERSION')) {
            $this->version = PAYMENT_GATEWAY_PIX_FOR_GIVEWP_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'payment-gateway-pix-for-givewp';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Payment_Gateway_Pix_For_Givewp_Loader. Orchestrates the hooks of the plugin.
     * - Payment_Gateway_Pix_For_Givewp_i18n. Defines internationalization functionality.
     * - Payment_Gateway_Pix_For_Givewp_Admin. Defines all hooks for the admin area.
     * - Payment_Gateway_Pix_For_Givewp_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies(): void
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-payment-gateway-pix-for-givewp-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-payment-gateway-pix-for-givewp-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(__DIR__) . 'admin/class-payment-gateway-pix-for-givewp-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(__DIR__) . 'public/class-payment-gateway-pix-for-givewp-public.php';

        /**
         * The class responsible for defining the gateways
         * of the plugin.
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-payment-gateway-pix-for-givewp-gateway.php';

        /**
         * Helper class
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-payment-gateway-pix-for-givewp-helper.php';


        $this->loader = new Payment_Gateway_Pix_For_Givewp_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Payment_Gateway_Pix_For_Givewp_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale(): void
    {
        $plugin_i18n = new Payment_Gateway_Pix_For_Givewp_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    public function load_payment_gateway($paymentGatewayRegister): void
    {
        $paymentGatewayRegister->registerGateway('PixGatewayClass');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks(): void
    {
        $plugin_admin = new Payment_Gateway_Pix_For_Givewp_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Register the gateways
        $this->loader->add_action('givewp_register_payment_gateway', $this, 'load_payment_gateway');

        $this->loader->add_action('give_get_settings_gateways', $plugin_admin, 'add_setting_into_new_section');
        $this->loader->add_action('give_get_sections_gateways', $plugin_admin, 'add_new_setting_section');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks(): void
    {
        $plugin_public = new Payment_Gateway_Pix_For_Givewp_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run(): void
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Payment_Gateway_Pix_For_Givewp_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
