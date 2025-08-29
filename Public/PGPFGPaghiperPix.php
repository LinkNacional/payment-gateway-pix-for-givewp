<?php

/**
 * @link      https://www.linknacional.com.br/
 *
 * @package    GivePaghiper
 * @subpackage GivePaghiper/PublicView
 */

namespace Pgpfg\PGPFGForGivewp\PublicView;

use Pgpfg\PGPFGForGivewp\Includes\LknGivePaghiperHelper;

/**
 * @package    GivePaghiper
 * @subpackage GivePaghiper/PublicView
 * @author     Link Nacional
 */
final class PGPFGPaghiperPix extends PGPFGGatewayPaghiperAbstractPayment
{
    public function __construct()
    {
        parent::__construct(
            'lkn-give-paghiper-pix',
            'pix',
            'PagHiper Pix',
            'PagHiper Pix'
        );
    }

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'lkn-give-paghiper-pix';
    }

    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return $this->render_form($formId, $args);
    }

    /**
     * @inheritDoc
     */
    protected function gateway_form(): string
    {
        $configs = LknGivePaghiperHelper::get_configs();

        // Verify if the payment tax is a number, if not a number, set as zero.
        if (is_numeric($configs['pixFee'])) {
            $pixFee = number_format($configs['pixFee'], 2, ',', '');
        } else {
            $pixFee = 0;
        }

        $info = esc_html__('Para pagamentos via Pix é cobrada uma taxa fixa de R$ ', 'payment-gateway-pix-for-givewp');
        $info = $info . $pixFee;

        $cpfCnpj = esc_html__('CPF / CNPJ', 'payment-gateway-pix-for-givewp');
        $cpfCnpjTooltip = esc_html__('Insira um número de CPF ou CNPJ válido', 'payment-gateway-pix-for-givewp');
        $astr = esc_html('*');

        return "
        <div id=\"lkn_give_paghiper_info_wrapper\">
            <h2 id=\"lkn_give_paghiper_info\">{$info}</h2>
        </div>

        <fieldset id=\"lkn_give_paghiper_cpf_cnpj\" class=\"form-row form-row-wide lkn_give_paghiper_hidden\">
            <label for=\"lkn_give_paghiper_primary_document\" class=\"give-label\">
                {$cpfCnpj}
                <span class=\"give-required-indicator\">{$astr}</span>
                <span class=\"give-tooltip hint--top hint--medium hint--bounce\" aria-label=\"$cpfCnpjTooltip\" rel=\"tooltip\"><i class=\"give-icon give-icon-question\"></i></span>
            </label>

            <input type=\"tel\" autocomplete=\"off\" name=\"gatewayData[lkn_give_primary_document]\" id=\"lkn_give_paghiper_cpf_cnpj_input\" class=\"give-input required\" placeholder=\"$cpfCnpj\" required aria-required=\"true\" maxlength=\"20\">
        </fieldset>

		<div id=\"paghiper-pix-div\"></div>";
    }

    /**
     * Enqueu the script for new Give v3.0.0 donation form.
     *
     */
    public function enqueueScript(int $formId): void
    {
        $configs = LknGivePaghiperHelper::get_configs();

        // Verify if the payment tax is a number, if not a number, set as zero.
        if (is_numeric($configs['pixFee'])) {
            $pixFee = number_format($configs['pixFee'], 2, ',', '');
        } else {
            $pixFee = 0;
        }

        $info = esc_html__('Para pagamentos via Pix é cobrada uma taxa fixa de R$ ', 'payment-gateway-pix-for-givewp');
        $info = $info . $pixFee;

        wp_register_script(
            self::id() . 'pix-script',
            plugin_dir_url(__FILE__) . '/js/pix-script.js',
            array('wp-element', 'wp-i18n'),
            PGPFG_PIX_PLUGIN_VERSION,
            true
        );

        $pixGlobals = array(
            'title' => esc_html__('Informação do Pagamento', 'payment-gateway-pix-for-givewp'),
            'cpf_cnpj_label' => esc_html__('CPF / CNPJ', 'payment-gateway-pix-for-givewp'),
            'cpf_cnpj_tooltip' => esc_html__('Insira um número de CPF ou CNPJ válido', 'payment-gateway-pix-for-givewp'),
            'astr_symbol' => esc_html(' *'),
            'pix_fee' => ($pixFee > 0),
            'info' => $info
        );

        wp_localize_script(self::id() . 'pix-script', 'pixGlobals', $pixGlobals);

        wp_enqueue_script(self::id() . 'pix-script');
    }
}
