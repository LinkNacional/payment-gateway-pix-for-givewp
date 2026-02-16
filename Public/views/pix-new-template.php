<?php
    if ( ! defined( 'ABSPATH' ) ) exit;
?>

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"
>
<div id="pix_new_content">
    <div class="container_pix">
        <div class="span_title_container">
            <span class="span_title_text">Instruções</span>
        </div>
        <div class="schedule_instructions_container">
            <span>Dentro do APP do seu banco, leia o QRCode ou copie e cole o código PIX.</span>
            <span>Confirmação de pagamento automática.</span>
        </div>
        <div class="schedule_check_container">
            <span class="schedule_text">Próxima verificação em (Nº de tentativas: 5):</span>
            <span id="timer">0s</span>
        </div>

        <div class="payment_check_container">
            <button
                class="payment_check_button"
                disabled
            >Já paguei o PIX</button>
        </div>
        <span class="payment_check_text">Ao clicar neste botão, verificaremos se o pagamento foi confirmado com
            sucesso.</span>
    </div>
    <div class="container_pix">
        <span class="span_title_value">Total da doação</span>
        <span
            class="span_total_value"
            id="pix_page_currency_text"
        ><?php echo esc_html($currencyTxt); ?></span>
        <span class="span_date"><?php echo esc_html($dueDateMsg); ?></span>
        <div id="copy_container">
            <input
                type="text"
                class="input_copy_code"
                readonly
                style="border: none; background-color: #D9D9D9;"
                value="<?php echo esc_attr($donKey); ?>"
            >
            <input
                type="hidden"
                id="transactionId"
                value="<?php echo esc_attr($transactionId); ?>"
            >

            <input
                type="hidden"
                id="donationId"
                value="<?php echo esc_attr($donationId); ?>"
            >
            <button class="button_copy_code">COPIAR</button>
        </div>
        <div id="pix_page_qr_code">
            <img
                src="<?php echo esc_url($donQrCode); ?>"
                alt="<?php echo esc_attr($qrCodeDesc); ?>"
                class="pix_img"
            >
        </div>
    </div>
    <div class="share_container">
        <button
            class="share_button"
            style="background-color: transparent"
        >
            <img
                src="<?php echo esc_url($filePath); ?>"
                alt="icon"
            >
        </button>
    </div>
</div>