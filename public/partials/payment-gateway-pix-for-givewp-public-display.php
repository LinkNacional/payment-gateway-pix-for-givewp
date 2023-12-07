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

<link
    rel="stylesheet"
    href="<?php esc_attr_e(PAYMENT_GATEWAY_PIX_PLUGIN_URL) ?>public/css/payment-gateway-pix-for-givewp-public.css"
>
<div class='form-donation'>
    <?php
        echo "<h3>Detalhes de Cobrança</h3>";
($args['isFormEnabled']) ? give_default_cc_address_fields($args['formId']) : '' ;
?>
    <h3>Chave Pix:</h3>
    <p id='qr'>
    </p>
    <p
        id='pix'
        style='word-wrap: break-word;'
    >
    </p>
    <p id='copy-pix'>        
    </p>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->