<?php

namespace Pgpfg\PGPFGForGivewp\Includes;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Guard de compatibilidade entre FREE novo e PRO antigo.
 *
 * Environment e Habilitar Logs passaram a morar no FREE (aba Settings). Um PRO
 * ainda não atualizado continua lendo as chaves antigas `-pro-` e ignoraria a
 * escolha feita na tela nova — inclusive "Produção", que viraria sandbox
 * silencioso.
 *
 * Este guard só LÊ: mapeia a consulta da chave antiga para a nova através do
 * filtro `give_get_option_{$key}`. Nenhuma escrita em give_settings.
 *
 * @package PGPFGForGivewp
 */
final class PGPFGForGivewpSettingsCompat
{
    /**
     * Chave antiga => chave nova.
     *
     * @var array<string, string>
     */
    private const LEGACY_KEYS = array(
        'lkn-payment-pix-pro-environment' => 'lkn-payment-pix-environment',
        'lkn-payment-pix-pro-logs' => 'lkn-payment-pix-logs',
        'lkn-payment-pix-debug' => 'lkn-payment-pix-pro-debug',
    );

    /**
     * Registra um filtro para cada chave legada.
     */
    public static function register(): void
    {
        foreach (self::LEGACY_KEYS as $legacy => $current) {
            add_filter(
                'give_get_option_' . $legacy,
                static function ($value, $key = '', $default = '') use ($current) {
                    return self::resolveLegacy($value, $current);
                },
                10,
                3
            );
        }
    }

    /**
     * Devolve a chave nova quando ela tem valor; senão mantém o legado.
     *
     * @param mixed  $legacyValue Valor já resolvido para a chave antiga.
     * @param string $currentKey  Chave nova equivalente.
     *
     * @return mixed
     */
    public static function resolveLegacy($legacyValue, string $currentKey)
    {
        if (! function_exists('give_get_option')) {
            return $legacyValue;
        }

        $current = give_get_option($currentKey, '');

        if (is_string($current) && '' !== $current) {
            return $current;
        }

        return $legacyValue;
    }
}
