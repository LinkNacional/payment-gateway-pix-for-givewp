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

<input
    type="hidden"
    id="pix_type"
    value="<?php esc_attr_e($args['pixType']) ?>"
/>
<input
    type="hidden"
    id="pix_key"
    value="<?php esc_attr_e($args['pixKey']) ?>"
/>
<input
    type="hidden"
    id="pix_name"
    value="<?php esc_attr_e($args['pixName']) ?>"
/>
<input
    type="hidden"
    id="pix_city"
    value="<?php esc_attr_e($args['pixCity']) ?>"
/>

<script>
function togglePix() {
    const pix = document.getElementById('pix')
    const hide = document.getElementById('hide')
    const show = document.getElementById('show')

    if (pix.style.display === "none") {
        show.style.display = "none"
        hide.style.display = "block"
        pix.style.display = "block"
    } else {
        show.style.display = "block"
        hide.style.display = "none"
        pix.style.display = "none"
    }
}
</script>

<link rel="stylesheet" href="<?php esc_attr_e(PAYMENT_GATEWAY_PIX_PLUGIN_URL) ?>public/css/payment-gateway-pix-for-givewp-public.css"/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<div id="lkn-pix-form-donation">
    <?php
($args['isFormEnabled']) ? give_default_cc_address_fields($args['formId']) : '' ;
?>
    <br/>
    <legend>Chave Pix:</legend>
    <div class='pix-container'>
        <p id='qr'>Carregando...</p>
        <p id='pix' class="pix-content" style="display: none;"></p>
        <br/>
        <p id='copy-pix'></p>
    </div>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->