<?php
//LknGivePaghiperPixPage PGPFGForPaghiperPixPage

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
        $donationID = base64_encode($pixParamsDecoded->donationId);

        $qrCodeDesc = esc_html__('PIX Key', 'payment-gateway-pix-for-givewp');
        $notice = esc_html__('Pix copy and paste, click on the code below to copy:', 'payment-gateway-pix-for-givewp');
        $copyMsg = esc_html__('Copied!', 'payment-gateway-pix-for-givewp');
        $dueDateMsg = esc_html__(' Due Date: ', 'payment-gateway-pix-for-givewp');
        $currencyTxt = esc_html__('R$ 0,00', 'payment-gateway-pix-for-givewp');

        $dueDateMsg .= $donDueDate;

        $pixPageGlobals = array(
            'don_value' => $donValue,
            'donationId' => $donationID,
            'page_url' => home_url()
        );

        wp_localize_script('lkn-pix-page-script-js', 'pixPageGlobals', $pixPageGlobals);

        $html = file_get_contents(plugin_dir_path(__FILE__) . 'views/pix-new-template.html');
        $filePath = plugin_dir_url(__FILE__) . 'assets/icons/share.svg';

        // Substituir placeholders com as variáveis PHP
        $html = str_replace(
            array(
                '{{donDescript}}',
                '{{donQrCode}}',
                '{{qrCodeDesc}}',
                '{{notice}}',
                '{{donKey}}',
                '{{transactionId}}',
                '{{copyMsg}}',
                '{{currencyTxt}}',
                '{{dueDateMsg}}',
                '{{filePath}}',
                '{{donationId}}'
            ),
            array(
                $donDescript,
                $donQrCode,
                $qrCodeDesc,
                $notice,
                $donKey,
                $transactionId,
                $copyMsg,
                $currencyTxt,
                $dueDateMsg,
                $filePath,
                $donationID
            ),
            $html
        );
        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $html;
    } else {
        throw new Exception("Erro", 1);
    }
} catch (Exception $err) {
    $html = "
    <style>
        #message-box {

            display:flex;
            justify-content:center;


        }
        #message-box p {

            width:20dvw;
               background-color: #ff4c4c; /* Cor de fundo do quadro */
            color: #fff; /* Cor do texto */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
       font-size: 18px;
            font-weight: bold;
            text-align:center;


        }
    </style>
    <div id=\"message-box\">
        <p>Falha na geração do QR Code</p>
    </div>


    <script>
document.addEventListener(\"DOMContentLoaded\", function() {
    // Seleciona o elemento main e o elemento a ser movido
    var mainElement = document.querySelector(\"main\");
    var elementToMove = document.getElementById(\"message-box\");

    // Move o elemento para logo após o main
    if (mainElement && elementToMove) {
        mainElement.insertAdjacentElement(\"afterend\", elementToMove);
    }
});


    </script>


    ";
    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $html;
}
