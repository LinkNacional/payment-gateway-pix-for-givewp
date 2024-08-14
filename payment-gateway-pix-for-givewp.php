<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linknacional.com.br
 * @since             1.0.0
 * @package           PGPFGForGivewp
 *
 * @wordpress-plugin
 * Plugin Name:       Payment Gateway Pix for GiveWP
 * Plugin URI:        https://www.linknacional.com.br/wordpress/givewp/
 * Description:       Streamline your donation process and expand your reach to Brazilian donors by integrating PIX, the instant payment system, into your GiveWP donation forms.
 * Version:           2.0.3
 * Author:            Link Nacional
 * Author URI:        https://www.linknacional.com.br/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       payment-gateway-pix-for-givewp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined('ABSPATH')) {
    die;
}

require_once __DIR__ . '/vendor/autoload.php';

use Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewp;
use Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpActivator;
use Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpDeactivator;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PGPFG_PIX_PLUGIN_VERSION', '2.0.3');
define('PGPFG_PIX_PLUGIN_FILE', __FILE__);
define('PGPFG_PIX_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PGPFG_PIX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PGPFG_PIX_LANGUAGE_DIR', plugin_dir_path(__FILE__) . '/languages');
define('PGPFG_PIX_PLUGIN_BASENAME', plugin_basename(PGPFG_PIX_PLUGIN_FILE));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-payment-gateway-pix-for-givewp-activator.php
 */
function pgpfg_pix_activate_plugin(): void {
    PGPFGForGivewpActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-payment-gateway-pix-for-givewp-deactivator.php
 */
function pgpfg_pix_deactivate_plugin(): void {
    PGPFGForGivewpDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'pgpfg_pix_activate_plugin');
register_deactivation_hook(__FILE__, 'pgpfg_pix_deactivate_plugin');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pgpfg_pix_wc_cielo_plugin_row_meta', 10, 2);

/**
 * Plugin row meta links.
 *
 * @since
 *
 * @param array  $plugin_meta an array of the plugin's metadata
 * @param string $plugin_file path to the plugin file, relative to the plugins directory
 *
 * @return array
 */
function pgpfg_pix_wc_cielo_plugin_row_meta($plugin_meta, $plugin_file) {
    $new_meta_links['setting'] = '<a href="' . esc_url(add_query_arg(
        array(
            'post_type' => 'give_forms',
            'page' => 'give-settings',
            'tab' => 'gateways',
            'section' => 'lkn-payment-pix'
        ),
        admin_url('edit.php')
    )) . '">' . __('Settings', 'payment-gateway-pix-for-givewp') . '</a>';

    return array_merge($plugin_meta, $new_meta_links);
}

function pgpfg_pix_run_plugin(): void {
    $plugin = new PGPFGForGivewp();
    $plugin->run();
}
pgpfg_pix_run_plugin();
