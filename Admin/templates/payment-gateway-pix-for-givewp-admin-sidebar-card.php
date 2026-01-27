<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Provide an admin-facing sidebar card view for the plugin
 *
 * This file is used to markup the admin sidebar card.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    PGPFGForGivewp
 * @subpackage PGPFGForGivewp/admin/templates
 */

// Get plugin data using defined constants
$plugin_url = defined('PGPFG_PIX_PLUGIN_URL') ? PGPFG_PIX_PLUGIN_URL : plugin_dir_url(dirname(dirname(__FILE__)));
$version = 'Plugin PIX v' . PGPFG_PIX_PLUGIN_VERSION;
if (defined('PAYMENT_GATEWAY_PIX_FOR_GIVEWP_PRO_VERSION')) {
    $version .= ' | PIX Pro v' . constant('PAYMENT_GATEWAY_PIX_FOR_GIVEWP_PRO_VERSION');
} else {
    $version .= ' | GiveWP v' . GIVE_VERSION;
}
?>

<div class="lkn-card-container">
    <div id="pgpfgSideCard">
        <div id="pgpfgDivLogo">
            <div>
                <img src="<?php echo esc_url($plugin_url . 'Admin/images/linkNacionalLogo.webp'); ?>" alt="Plugin Logo" class="plugin-logo">
            </div>
            <p class="version-text"><?php echo esc_html($version); ?></p>
        </div>
        <div id="pgpfgDivContent">
            <div id="pgpfgDivLinks">
                <div class="link-column">
                    <a target="_blank" href="https://www.linknacional.com.br/wordpress/givewp/pix/?utm=plugin">
                        <b class="bullet">•</b> Documentação
                    </a>
                    <a target="_blank" href="https://www.linknacional.com.br/wordpress/">
                        <b class="bullet">•</b> Hosting
                    </a>
                </div>
                <div class="link-column">
                    <a target="_blank" href="https://www.linknacional.com.br/wordpress/plugins/">
                        <b class="bullet">•</b> WP Plugin
                    </a>
                    <a target="_blank" href="https://www.linknacional.com.br/wordpress/suporte/">
                        <b class="bullet">•</b> Suporte WP
                    </a>
                </div>
            </div>
            <div class="pgpfgSupportLinks">
                <div id="pgpfgStarsDiv">
                    <a target="_blank" href="https://wordpress.org/plugins/payment-gateway-pix-for-givewp/#reviews" class="stars-link">
                        <p class="rate-text">Avaliar o plugin</p>
                        <div class="PGPFGForGivewpStarRating">
                            <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                            <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                            <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                            <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                            <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                        </div>
                    </a>
                </div>
                <div class="pgpfgContactLinks">
                    <a href="https://chat.whatsapp.com/IjzHhDXwmzGLDnBfOibJKO" target="_blank" class="contact-link">
                        <img src="<?php echo esc_url($plugin_url . 'Admin/images/whatsapp-icon.svg'); ?>" alt="WhatsApp Icon" class="contact-icon">
                    </a>
                    <a href="https://t.me/wpprobr" target="_blank" class="contact-link">
                        <img src="<?php echo esc_url($plugin_url . 'Admin/images/telegram-icon.svg'); ?>" alt="Telegram Icon" class="contact-icon">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>