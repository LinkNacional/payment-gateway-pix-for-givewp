<?php

namespace Pgpfg\PGPFGForGivewp\PublicView;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The file that defines the Pix Payment Page.
 *
 * @link       https://www.linknacional.com.br
 *
 * @package    GivePaghiper
 */

// Catch URL params.
try {
    if (! empty($_GET["pix"]) && isset($_GET["pix"])) {
        wp_enqueue_script('lkn-pix-page-script-js', plugin_dir_url(__FILE__) . 'js/paghiper/lkn-pgpf-give-paghiper-pix-page.js', array('jquery'), PGPFG_PIX_PLUGIN_VERSION, false);

        wp_enqueue_style("lkn-pix-page-css", PGPFG_PIX_PLUGIN_URL . "Public/css/lkn-pgpf-give-paghiper-public.css", array(), PGPFG_PIX_PLUGIN_VERSION);
        $pixParams = sanitize_text_field(wp_unslash($_GET['pix']));

        $pixParamsEncoded = sanitize_text_field($pixParams);
        $pixParamsDecoded = json_decode(base64_decode($pixParamsEncoded, true));
        if (! is_array($pixParamsDecoded) && ! is_object($pixParamsDecoded)) {
            throw new Exception("Error", 1);
        }
        $donQrCode = $pixParamsDecoded->qrcode;

        $donKey = $pixParamsDecoded->key;
        $donValue = $pixParamsDecoded->value;
        $donDueDate = $pixParamsDecoded->date;
        $donDescript = $pixParamsDecoded->title;
        $transactionId = base64_encode($pixParamsDecoded->transactionId);
        $donationId = base64_encode($pixParamsDecoded->donationId);

        $qrCodeDesc = esc_html__('PIX Key', 'payment-gateway-pix-for-givewp');
        $notice = esc_html__('Pix copy and paste, click on the code below to copy:', 'payment-gateway-pix-for-givewp');
        $copyMsg = esc_html__('Copied!', 'payment-gateway-pix-for-givewp');
        $dueDateMsg = esc_html__(' Due Date: ', 'payment-gateway-pix-for-givewp');
        $currencyTxt = esc_html__('R$ 0,00', 'payment-gateway-pix-for-givewp');

        $dueDateMsg .= $donDueDate;

        // Use a generic nonce that works with REST API
        $nonce_action = 'pgpf_pix_status_check';
        $created_nonce = wp_create_nonce($nonce_action);

        $pixPageGlobals = array(
            'don_value' => $donValue,
            'donationId' => $donationId,
            'page_url' => home_url(),
            'status_check_nonce' => $created_nonce
        );

        wp_localize_script('lkn-pix-page-script-js', 'pixPageGlobals', $pixPageGlobals);

        // Now it's a PHP template, so we can include it directly
        $filePath = plugin_dir_url(__FILE__) . 'assets/icons/share.svg';

        // Start output buffering to capture the template output
        ob_start();
        include plugin_dir_path(__FILE__) . 'views/pix-new-template.php';
        $html = ob_get_clean();
        
        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $html;
    } else {
        throw new Exception("Erro", 1);
    }
} catch (Exception $err) {
    // Enqueue CSS for error message
    wp_enqueue_style("lkn-pix-page-css", PGPFG_PIX_PLUGIN_URL . "Public/css/lkn-pgpf-give-paghiper-public.css", array(), PGPFG_PIX_PLUGIN_VERSION);
    
    // Include error template
    include plugin_dir_path(__FILE__) . 'views/pix-error-template.php';
}
