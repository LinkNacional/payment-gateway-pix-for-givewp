<?php

namespace Lkn\PaymentGatewayPixForGivewp\Includes;

use Lkn\PaymentGatewayPixForGivewp\Admin\PaymentGatewayPixForGivewpAdmin;
use Lkn\PaymentGatewayPixForGivewp\PublicView\PaymentGatewayPixForGivewpPublic;
use Lkn\PaymentGatewayPixForGivewp\PublicView\PaymentGatewayPixGatewayClass;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    PaymentGatewayPixForGivewp
 * @subpackage PaymentGatewayPixForGivewp/includes
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
 * @package    PaymentGatewayPixForGivewp
 * @subpackage PaymentGatewayPixForGivewp/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
final class PaymentGatewayPixForGivewp
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PaymentGatewayPixForGivewp_Loader    $loader    Maintains and registers all hooks for the plugin.
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
        if (defined('PAYMENT_GATEWAY_PIX_PLUGIN_VERSION')) {
            $this->version = PAYMENT_GATEWAY_PIX_PLUGIN_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'payment-gateway-pix-for-givewp';
        $this->run();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - PaymentGatewayPixForGivewp_Loader. Orchestrates the hooks of the plugin.
     * - PaymentGatewayPixForGivewp_i18n. Defines internationalization functionality.
     * - PaymentGatewayPixForGivewp_Admin. Defines all hooks for the admin area.
     * - PaymentGatewayPixForGivewp_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies(): void
    {
        $this->loader = new PaymentGatewayPixForGivewpLoader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the PaymentGatewayPixForGivewp_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale(): void
    {
        $plugin_i18n = new PaymentGatewayPixForGivewpi18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    public function load_payment_gateway($paymentGatewayRegister): void
    {
        $paymentGatewayRegister->registerGateway(PaymentGatewayPixGatewayClass::class);
    }

    public function add_new_cron_recurrencies()
    {
        $schedules = array(
            'biweekly' => array(
                'interval' => 15 * DAY_IN_SECONDS,
                'display' => 'Quinzenal'
            )
        );

        return $schedules;
    }

    public function define_cron_hook(): void
    {
        add_action('lkn_payment_pix_delete_old_logs_cron_hook', array(PaymentGatewayPixHelperClass::class, 'delete_old_logs'));
    }

    public function define_event_delete_old_logs(): void
    {
        if (!wp_next_scheduled('lkn_payment_pix_delete_old_logs_cron_hook')) {
            $time = time() + (15 * DAY_IN_SECONDS);
            wp_schedule_event($time, 'biweekly', 'lkn_payment_pix_delete_old_logs_cron_hook');
        }
    }
    public function check_environment()
    {
        // Load plugin helper functions.
        if (!function_exists('deactivate_plugins') || !function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Flag to check whether deactivate plugin or not.
        $is_deactivate_plugin = null;

        // Verify minimum Give plugin version.
        if (
            defined('GIVE_VERSION')
            && version_compare(GIVE_VERSION, PAYMENT_GATEWAY_PIX_PLUGIN_VERSION, '<')
        ) {
            // Show admin notice.
            $this->dependency_notice();

            $is_deactivate_plugin = true;
        }

        $is_give_active = is_plugin_active('give/give.php');


        // Verify if Free plugin is actived.
        if (!$is_give_active) {
            // Show admin notice.
            $this->inactive_notice();

            $is_deactivate_plugin = true;
        }

        // Deactivate plugin.
        if ($is_deactivate_plugin) {
            deactivate_plugins(PAYMENT_GATEWAY_PIX_PLUGIN_BASENAME);

            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }

            return false;
        }

        return true;
    }


    public function dependency_notice(): void
    {
        // Admin notice.
        $message = sprintf(
            '<strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a>  %5$s %6$s+ %7$s.',
            'Erro de Ativação:',
            'Você deve ter',
            'https://givewp.com',
            'Give',
            'versão',
            PAYMENT_GATEWAY_PIX_PLUGIN_VERSION,
            'para o complemento Payment Gateway Pix For GiveWp ativar'
        );

        Give()->notices->register_notice(array(
            'id' => 'give-activation-error',
            'type' => 'error',
            'description' => $message,
            'show' => true
        ));
    }

    /**
     * Notice for No Core Activation
     *
     * @since 1.0.0
     */
    public function inactive_notice(): void
    {
        // Admin notice.
        $message = sprintf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a> %5$s.</p></div>',
            'Erro de Ativação:',
            'Você deve ter',
            'https://givewp.com',
            'Give',
            'plugin instalado e ativado para o complemento Payment Gateway Pix For GiveWP ativar'
        );

        echo esc_html($message);
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */

    private function define_admin_hooks()
    {
        $plugin_admin = new PaymentGatewayPixForGivewpAdmin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Set the cron jobs
        $this->loader->add_filter('cron_schedules', $this, 'add_new_cron_recurrencies');
        $this->loader->add_action('init', $this, 'define_event_delete_old_logs');
        $this->loader->add_action('init', $this, 'define_cron_hook');

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
        $plugin_public = new PaymentGatewayPixForGivewpPublic($this->plugin_name, $this->version);
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
        $is_give_active = $this->check_environment();
        if ($is_give_active) {
            $this->load_dependencies();
            $this->set_locale();
            $this->define_admin_hooks();
            $this->define_public_hooks();
            $this->loader->run();

        }
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
     * @return    PaymentGatewayPixForGivewp_Loader    Orchestrates the hooks of the plugin.
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
