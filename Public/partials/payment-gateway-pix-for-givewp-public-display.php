<?php

if ( ! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.linknacional.com.br
 * @since      1.0.0
 *
 * @package    PGPFGForGivewp
 * @subpackage PGPFGForGivewp/public/partials
 */
?>

<input
    type="hidden"
    id="pix_type"
    value="<?php echo esc_attr($args['pixType']) ?>"
/>
<input
    type="hidden"
    id="pix_key"
    value="<?php echo esc_attr($args['pixKey']) ?>"
/>
<input
    type="hidden"
    id="pix_name"
    value="<?php echo esc_attr($args['pixName']) ?>"
/>
<input
    type="hidden"
    id="pix_city"
    value="<?php echo esc_attr($args['pixCity']) ?>"
/>
<input
    type="hidden"
    id="pix_id"
    value="<?php echo esc_attr($args['pixId']) ?>"
/>
<input
    type="hidden"
    name="gatewayData[pix-payment-gateway-id]"
    value="pix"
/>

<div id="lkn-pix-form-donation">
    <?php ($args['isFormEnabled']) ? give_default_cc_address_fields($args['formId']) . '<br/>' : '' ; ?>
    <legend>
        <?php esc_html_e('Pix Key:', 'payment-gateway-pix-for-givewp')?>
    </legend>
    <div class='pix-container'>
        <p id='qr'>
            <?php esc_html_e('Loading...', 'payment-gateway-pix-for-givewp') ?>
        </p>
        <br />
        <p id='pix'></p>
        <p
            id='copy-pix'
            style="display: none;"
        >
            <button
                id="toggle-viewing"
                type="button"
                title="Mostrar Pix"
            >
                <span
                    id="show"
                    class="material-symbols-outlined"
                    style="display: none;"
                >visibility_off</span>
                <span
                    id="hide"
                    class="material-symbols-outlined"
                >visibility</span>
            </button>
            <button
                id="copy-button"
                type="button"
                title="Copiar Pix"
            >
                <span class="material-symbols-outlined">content_copy</span>
            </button>
        </p>
    </div>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->