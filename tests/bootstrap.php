<?php
/**
 * PHPUnit bootstrap — Payment Gateway Pix for GiveWP.
 *
 * Os testes unitários deste repo validam código puro (helpers) e as regras
 * estáticas que o Plugin Check oficial cobra. Não sobem o WordPress inteiro:
 * bastam as constantes e o autoloader do plugin.
 *
 * @package PGPFGForGivewp
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoload)) {
    fwrite(STDERR, "[PHPUnit] vendor/autoload.php ausente. Rode composer install.\n");
    exit(1);
}

require_once $autoload;

/**
 * Stub do give_get_option() para os testes de resolvedor, sem subir o WordPress.
 * Centralizado aqui porque dois arquivos de teste definindo a mesma função
 * fazem o primeiro carregado vencer e o valor do stub nunca chegar no outro.
 */
if (! function_exists('give_get_option')) {
    function give_get_option($key, $default = '')
    {
        return \GiveOptionStub::$values[$key] ?? $default;
    }
}

/**
 * @internal
 */
final class GiveOptionStub
{
    /** @var array<string, mixed> */
    public static array $values = [];
}
