<?php

namespace Pgpfg\PGPFGForGivewp\Includes;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Guard de compatibilidade das opções globais entre FREE e PRO.
 *
 * Environment, Habilitar Logs e Depuração Avançada moram no FREE, nas chaves:
 *
 *   lkn-payment-pix-environment
 *   lkn-payment-pix-logs
 *   lkn-payment-pix-pro-debug
 *
 * Versões anteriores guardavam o mesmo dado nas chaves `-pro-`. Este guard faz
 * a ponte nos dois sentidos, sem nunca escrever em give_settings:
 *
 * 1. A chave nova herda o valor antigo até o usuário salvar a tela, então o
 *    upgrade não muda comportamento e o campo abre no que estava escolhido.
 * 2. A consulta à chave antiga devolve o valor efetivo da nova, para um PRO
 *    ainda não atualizado não ignorar a escolha feita na tela nova.
 * 3. A tela de settings do Give lê give_settings direto — não passa por
 *    give_get_option() — então precisa de um filtro próprio.
 *
 * Salvar o campo marca a escolha como explícita. Sem essa marca, um valor igual
 * ao default seria indistinguível de "nunca escolhido": o usuário não
 * conseguiria desligar um log que estava ligado antes do upgrade.
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
     * Default de cada campo, conforme registrado na tela.
     *
     * @var array<string, string>
     */
    private const FIELD_DEFAULTS = array(
        'lkn-payment-pix-environment' => 'sandbox',
        'lkn-payment-pix-logs' => 'disabled',
        'lkn-payment-pix-pro-debug' => 'disabled',
    );

    /**
     * Option que registra quais campos o usuário já salvou.
     */
    private const EXPLICIT_OPTION = 'lkn_payment_pix_explicit_settings';

    /**
     * Chaves em resolução, para cortar a recursão entre os filtros.
     *
     * @var array<string, bool>
     */
    private static array $resolving = array();

    /**
     * Registra os filtros de leitura e o hook que marca a escolha do usuário.
     */
    public static function register(): void
    {
        foreach (self::LEGACY_KEYS as $legacy => $current) {
            // PRO antigo consulta a chave antiga.
            add_filter(
                'give_get_option_' . $legacy,
                static function ($value, $key = '', $default = '') use ($current) {
                    return self::resolveLegacy($value, $current);
                },
                10,
                3
            );

            // Chave nova herda o valor antigo até o usuário salvar a tela.
            add_filter(
                'give_get_option_' . $current,
                static function ($value, $key = '', $default = '') use ($current) {
                    return self::resolveInherited($value, $current);
                },
                10,
                3
            );
        }

        // A tela de settings do Give não usa give_get_option().
        add_filter('give_admin_field_get_value', array(self::class, 'renderValue'), 10, 4);

        // Marca a escolha quando o campo vem no POST da tela de settings.
        // O Give passa $rawValue = null para campo que não foi enviado, e o
        // hook give_save_settings_give_settings não serve aqui: ele recebe o
        // array já mesclado com o valor antigo, então todo campo pareceria
        // salvo em qualquer gravação.
        foreach (array_keys(self::FIELD_DEFAULTS) as $key) {
            add_filter(
                'give_admin_settings_sanitize_option_' . $key,
                array(self::class, 'recordExplicitChoice'),
                10,
                3
            );
        }
    }

    /**
     * Valor efetivo da chave antiga: espelha a nova, com herança aplicada.
     *
     * @param mixed  $legacyValue Valor já resolvido para a chave antiga.
     * @param string $currentKey  Chave nova equivalente.
     *
     * @return mixed
     */
    public static function resolveLegacy($legacyValue, string $currentKey)
    {
        if (! function_exists('give_get_option') || ! empty(self::$resolving[$currentKey])) {
            return $legacyValue;
        }

        self::$resolving[$currentKey] = true;
        $current = give_get_option($currentKey, '');
        unset(self::$resolving[$currentKey]);

        $effective = self::resolveInherited($current, $currentKey);

        if (is_string($effective) && '' !== $effective) {
            return $effective;
        }

        return $legacyValue;
    }

    /**
     * A chave nova herda o valor antigo até o usuário salvar a tela.
     *
     * @param mixed  $currentValue Valor atual da chave nova.
     * @param string $currentKey   Chave nova.
     *
     * @return mixed
     */
    public static function resolveInherited($currentValue, string $currentKey)
    {
        $legacyKey = self::legacyKeyFor($currentKey);

        if (null === $legacyKey) {
            return $currentValue;
        }

        // Escolha explícita sempre vence.
        if (self::isExplicit($currentKey)) {
            return $currentValue;
        }

        // Valor diferente do default só pode ter vindo de uma escolha.
        $default = self::FIELD_DEFAULTS[$currentKey] ?? '';
        if (is_string($currentValue) && '' !== $currentValue && $currentValue !== $default) {
            return $currentValue;
        }

        if (! function_exists('give_get_option') || ! empty(self::$resolving[$currentKey])) {
            return $currentValue;
        }

        self::$resolving[$currentKey] = true;
        $legacyValue = give_get_option($legacyKey, '');
        unset(self::$resolving[$currentKey]);

        if (is_string($legacyValue) && '' !== $legacyValue) {
            return $legacyValue;
        }

        return $currentValue;
    }

    /**
     * Valor exibido no campo da tela de settings.
     *
     * @param mixed  $value      Valor lido de give_settings.
     * @param string $optionName Nome do option do Give.
     * @param string $fieldId    Id do campo.
     * @param mixed  $default    Default do campo.
     *
     * @return mixed
     */
    public static function renderValue($value, $optionName = '', $fieldId = '', $default = false)
    {
        $fieldId = (string) $fieldId;

        if (null === self::legacyKeyFor($fieldId)) {
            return $value;
        }

        return self::resolveInherited($value, $fieldId);
    }

    /**
     * Marca a escolha quando o campo realmente veio no POST.
     *
     * @param mixed $value    Valor já sanitizado.
     * @param mixed $option   Definição do campo.
     * @param mixed $rawValue Valor cru do POST; null quando o campo não foi enviado.
     *
     * @return mixed
     */
    public static function recordExplicitChoice($value, $option = array(), $rawValue = null)
    {
        if (null === $rawValue) {
            return $value;
        }

        $id = is_array($option) && isset($option['id']) ? (string) $option['id'] : '';

        if ('' !== $id && isset(self::FIELD_DEFAULTS[$id])) {
            self::markExplicit(array($id => true));
        }

        return $value;
    }

    /**
     * O usuário já salvou este campo?
     */
    private static function isExplicit(string $key): bool
    {
        if (! function_exists('get_option')) {
            return false;
        }

        $saved = get_option(self::EXPLICIT_OPTION, array());

        return is_array($saved) && ! empty($saved[$key]);
    }

    /**
     * Registra a escolha do usuário para os campos informados.
     *
     * @param array<string, bool> $keys Campos salvos.
     */
    private static function markExplicit(array $keys): void
    {
        if (! function_exists('get_option') || ! function_exists('update_option')) {
            return;
        }

        $saved = get_option(self::EXPLICIT_OPTION, array());
        $saved = is_array($saved) ? $saved : array();

        $changed = false;

        foreach (array_keys($keys) as $key) {
            if (empty($saved[$key])) {
                $saved[$key] = true;
                $changed = true;
            }
        }

        if ($changed) {
            update_option(self::EXPLICIT_OPTION, $saved, false);
        }
    }

    /**
     * Chave antiga correspondente a uma chave nova.
     */
    private static function legacyKeyFor(string $currentKey): ?string
    {
        $legacyKey = array_search($currentKey, self::LEGACY_KEYS, true);

        return false === $legacyKey ? null : (string) $legacyKey;
    }
}
