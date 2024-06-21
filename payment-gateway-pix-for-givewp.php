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
 * Version:           2.0.0
 * Author:            Link Nacional
 * Author URI:        https://www.linknacional.com.br/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       payment-gateway-pix-for-givewp
 * Domain Path:       /languages
 */

require_once(__DIR__. '/vendor/autoload.php');
use Lkn\PGPFGForGivewp\Includes\PGPFGForGivewp;
use Lkn\PGPFGForGivewp\Includes\PGPFGForGivewpActivator;
use Lkn\PGPFGForGivewp\Includes\PGPFGForGivewpDeactivator;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PGPFG_PIX_PLUGIN_VERSION', '1.0.0');
define('PGPFG_PIX_PLUGIN_FILE', __DIR__. '/payment-gateway-pix-for-givewp.php');
define('PGPFG_PIX_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PGPFG_PIX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PGPFG_PIX_LANGUAGE_DIR', plugin_dir_path(__FILE__) . '/languages');
define('PGPFG_PIX_PLUGIN_BASENAME', plugin_basename(PGPFG_PIX_PLUGIN_FILE));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-payment-gateway-pix-for-givewp-activator.php
 */
function pgpfg_pix_activate_plugin(): void
{
    PGPFGForGivewpActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-payment-gateway-pix-for-givewp-deactivator.php
 */
function pgpfg_pix_deactivate_plugin(): void
{
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
function pgpfg_pix_run_plugin(): void
{
    $plugin = new PGPFGForGivewp();
    $plugin->run();
}
pgpfg_pix_run_plugin();
