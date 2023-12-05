<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    Payment_Gateway_Pix_For_Givewp
 * @subpackage Payment_Gateway_Pix_For_Givewp/public/partials
 */
?>

<!--  TODO: Adicionar reatividade de tela -->
<link rel="stylesheet" href="<?php esc_attr_e(PAYMENT_GATEWAY_PIX_PLUGIN_URL) ?>public/css/payment-gateway-pix-for-givewp-public.css">
<div class='form-donation'>
    <p>
        <img src='https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=<?php esc_attr_e(urlencode($args['qr'])); ?>' alt='QR Code for <?php esc_attr_e($args['qr']); ?>'/>
    </p>
    <h4>Chave Pix:</h4>
    <p style='word-wrap: break-word;'>
        <?php esc_attr_e($args['qr']); ?>
    </p>
    <p><button type='button' class="copy-button" onclick="navigator.clipboard.writeText('<?php esc_attr_e($args['qr']); ?>')">Copiar a Chave</button></p>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
