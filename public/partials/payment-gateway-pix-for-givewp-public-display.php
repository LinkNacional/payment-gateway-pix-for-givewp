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

<!--  TODO: Insert form import -->
<!--  TODO: Adicionar reatividade de tela -->
<div style='text-align: center; max-width: 500px;'>
    <p>
        <img src='https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=<?php esc_attr_e(urlencode($args['qr'])); ?>' alt='QR Code for <?php esc_attr_e($args['qr']); ?>'/>
    </p>
    <h3>Chave Pix:</h3>
    <p>
        <?php esc_attr_e($args['qr']); ?>
        <button onclick="navigator.clipboard.writeText('<?php esc_attr_e($args['qr']); ?>')">Copy Pix Code</button>
    </p>
    <p>Payment Gateway Pix for GiveWP</p>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
