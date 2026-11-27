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
