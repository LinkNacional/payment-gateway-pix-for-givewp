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
final class PGPFGForGivewpi18n
{
    private const TEXT_DOMAIN = 'payment-gateway-pix-for-givewp';

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain(): void
    {
        load_plugin_textdomain(
            self::TEXT_DOMAIN,
            false,
            dirname(plugin_basename(__DIR__)) . '/languages/'
        );

        add_filter('load_textdomain_mofile', array($this, 'prefer_bundled_translation'), 10, 2);
    }

    /**
     * Faz a tradução que acompanha o plugin ter precedência sobre o pacote de
     * idioma do wp.org. Sem isso o WordPress resolve o domínio em
     * wp-content/languages/plugins/ e o .mo do plugin nunca é lido.
     *
     * @param string $mofile Caminho resolvido pelo WordPress.
     * @param string $domain Text domain solicitado.
     * @return string
     */
    public function prefer_bundled_translation($mofile, $domain)
    {
        if (self::TEXT_DOMAIN !== $domain) {
            return $mofile;
        }

        $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
        $bundled = PGPFG_PIX_PLUGIN_DIR . 'languages/' . self::TEXT_DOMAIN . '-' . $locale . '.mo';

        return is_readable($bundled) ? $bundled : $mofile;
    }
}
