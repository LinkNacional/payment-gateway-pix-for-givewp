<?php

namespace Pgpfg\PGPFGForGivewp\Includes;

use Give\Log\LogFactory;
use DateTime;
use Exception;

// Exit, if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * @see        https://www.linknacional.com.br/
 * @author     Link Nacional
 */
final class PGPFGivePaghiperHelper
{
    /**
     * Check plugin environment and show plugin dependency notice.
     *
     * @since 1.0.0
     *
     * @return bool|null
     */
    final public static function check_environment()
    {
        // Load plugin helper functions.
        if (! function_exists('deactivate_plugins') || ! function_exists('is_plugin_active')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        // Flag to check whether deactivate plugin or not.
        $is_deactivate_plugin = null;

        return true;
    }

    /**
     * Validate the CPF number offline.
     *
     * @param string $cpf
     *
     * @return bool $result
     */
    public static function validate_cpf($cpf)
    {
        // Extract only numbers.
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verify the digits amount.
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verify if is a sequence of repeated numbers. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calculate the value to validade the CPF.
        for ($t = 9; $t < 11; ++$t) {
            for ($d = 0, $c = 0; $c < $t; ++$c) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate the CNPJ number offline.
     *
     * @param string $cnpj
     *
     * @return bool $result
     */
    public static function validate_cnpj($cnpj)
    {
        // Verify if the number was informed.
        if (empty($cnpj)) {
            return false;
        }

        // Eliminate possible masks.
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        $cnpj = str_pad($cnpj, 14, '0', \STR_PAD_LEFT);

        // Verify if the digits number is equal to 14.
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verify all of invalids options below, if is one, return false.
        if (
            '00000000000000' == $cnpj
            || '11111111111111' == $cnpj
            || '22222222222222' == $cnpj
            || '33333333333333' == $cnpj
            || '44444444444444' == $cnpj
            || '55555555555555' == $cnpj
            || '66666666666666' == $cnpj
            || '77777777777777' == $cnpj
            || '88888888888888' == $cnpj
            || '99999999999999' == $cnpj
        ) {
            return false;
        }

        // Calculate the value to validate the CNPJ.
        $j = 5;
        $k = 6;
        $soma1 = 0;
        $soma2 = 0;

        for ($i = 0; $i < 13; ++$i) {
            $j = 1 == $j ? 9 : $j;
            $k = 1 == $k ? 9 : $k;

            $soma2 += ((int) $cnpj[$i] * $k);

            if ($i < 12) {
                $soma1 += ((int) $cnpj[$i] * $j);
            }

            --$k;
            --$j;
        }

        $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
        $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;

        return ($cnpj[12] == $digito1) && ($cnpj[13] == $digito2);
    }

    /**
     * Makes a .log file.
     *
     * @param mixed $message
     */
    public static function regLog($logType, $category, $description, $data, $forceLog = false): void
    {
        if (give_get_option('lkn_pgpf_paghiper_debug') == 'enable' || $forceLog) {
            $logFactory = new LogFactory();
            $log = $logFactory->make(
                $logType,
                $description,
                $category,
                'Give PagHiper',
                $data
            );
            $log->save();
        }
    }

    /**
     * This function centralizes the data in one spot for ease mannagment
     *
     * @since 1.0.0
     *
     * @return array $configs
     */
    final public static function get_configs()
    {
        $configs = array();

        $configs['basePath'] = PGPFG_PIX_PLUGIN_DIR . 'logs/';
        $configs['base'] = $configs['basePath'] . gmdate('d.m.Y-H.i.s') . '.log';

        $configs['debug'] = give_get_option('lkn_pgpf_paghiper_debug', 'nolog');

        $configs['urlBol'] = 'https://api.paghiper.com/';
        $configs['urlPix'] = 'https://pix.paghiper.com/';

        $configs['apiKey'] = trim(give_get_option('lkn_pgpf_paghiper_api_key_setting_field', ''));
        $configs['token'] = trim(give_get_option('lkn_pgpf_paghiper_token_setting_field', ''));
        // Function that validates the module key.
        $configs['moduleKey'] = trim(give_get_option('lkn_pgpf_paghiper_license_setting_field', ''));

        $configs["teste"] = trim(give_get_option("lkn_pgpf_paghiper_select_template_pix"));

        $configs['expDate'] = preg_replace('/\D/', '', give_get_option('lkn_pgpf_paghiper_due_date_setting_field'));
        $configs['bolFee'] = preg_replace('/[^0-9.]/', '', give_get_option('lkn_pgpf_paghiper_fee_bol_setting_field'));
        $configs['pixFee'] = preg_replace('/[^0-9.]/', '', give_get_option('lkn_pgpf_paghiper_fee_pix_setting_field'));
        $configs['description'] = trim(give_get_option('lkn_pgpf_paghiper_desc_setting_field', 'Doação'));

        return $configs;
    }

    /**
     * Function that builds and executes a curl Post.
     *
     * @param array  $header
     * @param array  $body
     * @param string $url
     *
     * @return object $response
     */
    public static function connect_request($header, $body, $url)
    {
        try {
            // Make the request args.
            $args = array(
                'headers' => $header,
                'body' => wp_json_encode($body),
                'timeout' => 10,
                'redirection' => 5,
                'httpversion' => '1.1'
            );

            // Make the request.
            $response = wp_remote_post($url, $args);

            return json_decode(wp_remote_retrieve_body($response), false);
        } catch (Exception $e) {
            // Register log.
            PGPFGivePaghiperHelper::regLog('error', 'curl', 'POST Curl Error', $e->getMessage());

            return array();
        }
    }

    public static function custom_add_donation_meta_field($payment_id): void
    {
        // Recupera um metadado personalizado
        $custom_field_value = give_get_meta($payment_id);
        if (isset($custom_field_value["lkn_pgpf_give_paghiper_response"])) {
            $arr = json_decode($custom_field_value["lkn_pgpf_give_paghiper_response"][0], true);
            $pix_page_url = esc_url($arr["pix_page"]);

?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const adminBox = document.getElementById('give-order-details');
                    let urlQrCode = new URL('<?php echo esc_js($pix_page_url); ?>');
                    params = new URLSearchParams(urlQrCode.search);

                    if (adminBox) {
                        let giveAdminBox = adminBox.getElementsByClassName('give-admin-box-inside');
                        let qrCode = JSON.parse(atob(params.get('pix')));

                        // Crie a nova div
                        var newDiv = document.createElement('div');
                        newDiv.className = 'give-admin-box-inside';

                        // Crie o novo parágrafo
                        var newP = document.createElement('p');
                        newP.innerHTML = '<strong><?php echo esc_js(__('PIX Key:', 'payment-gateway-pix-for-givewp')); ?></strong><br><button pixKey="' + qrCode.key + '"><?php echo esc_js(__('Copy PIX Key', 'payment-gateway-pix-for-givewp')); ?></button>';

                        let button = newP.querySelector('button');
                        button.addEventListener('click', function(event) {
                            event.preventDefault();
                            navigator.clipboard.writeText(button.getAttribute('pixKey'));
                            alert('<?php echo esc_js(__('PIX Key Copied', 'payment-gateway-pix-for-givewp')); ?>');
                        });

                        // Adicione o parágrafo à nova div
                        newDiv.appendChild(newP);

                        // Insira a nova div após o segundo elemento com a classe .give-admin-box-inside
                        giveAdminBox[1].after(newDiv);
                    }
                });
            </script>
<?php
        }
    }

    /**
     * Lista todas as páginas do WordPress para uso em selects.
     * @return array [id => título]
     */
    public static function get_all_pages_for_select(): array
    {
        // Verifica se existe a página "PagHiper Pix"
        $paghiper_page = get_page_by_title('PagHiper Pix');
        if (!$paghiper_page) {
            // Cria a página
            $page_id = wp_insert_post(array(
                'post_title'   => 'PagHiper Pix',
                'post_content' => '[lkn_pgpf_give_paghiper_pix]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ));
            $paghiper_page = get_post($page_id);
        } else {
            if ($paghiper_page->post_content === '[lkn_give_paghiper_pix]') {
                wp_update_post([
                    'ID' => $paghiper_page->ID,
                    'post_content' => '[lkn_pgpf_give_paghiper_pix]'
                ]);
            }
        }

        // Lista todas as páginas
        $pages = get_pages();
        $result = array();
        foreach ($pages as $page) {
            $result[$page->ID] = $page->post_title;
        }
        return $result;
    }

    public static function find_give_receipt_page($form_id) {
        
        $form_settings = give_get_meta($form_id, 'formBuilderSettings', true);

        if(gettype($form_settings) !== 'array') {
            $form_settings = json_decode($form_settings, true);
        }

        if (empty($form_settings['enableReceiptConfirmationPage']) && $form_settings['enableReceiptConfirmationPage'] === false) {
            return false;
        }
        
        $give_success_page = give_get_option('success_page');
        if (empty($give_success_page)) {
            return false;
        }

        $page = get_post($give_success_page);

        if ($page && $page->post_status === 'publish' && has_shortcode($page->post_content, 'give_receipt')) {
            return (int) $give_success_page;
        }

        return false;
    }
}
