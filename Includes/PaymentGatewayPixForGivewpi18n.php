<?php

namespace Lkn\PaymentGatewayPixForGivewp\Includes;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    PaymentGatewayPixForGivewp
 * @subpackage PaymentGatewayPixForGivewp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    PaymentGatewayPixForGivewp
 * @subpackage PaymentGatewayPixForGivewp/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
class PaymentGatewayPixForGivewpi18n
{
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'payment-gateway-pix-for-givewp',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }



}
