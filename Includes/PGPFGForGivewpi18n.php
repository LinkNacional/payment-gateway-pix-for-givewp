<?php

namespace Pgpfg\PGPFGForGivewp\Includes;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    PGPFGForGivewp
 * @subpackage PGPFGForGivewp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    PGPFGForGivewp
 * @subpackage PGPFGForGivewp/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
final class PGPFGForGivewpi18n {
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain(): void {
        load_plugin_textdomain(
            'payment-gateway-pix-for-givewp',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
