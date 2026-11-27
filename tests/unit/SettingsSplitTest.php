<?php
/**
 * Divisão das opções globais entre FREE e PRO.
 *
 *   FREE / aba Settings : Environment + Habilitar Logs
 *   PRO  / Pro Settings : Depuração Avançada (console)
 *
 * O log transacional é um recurso compartilhado: o campo vive no FREE e o PRO
 * consome o mesmo valor por PGPFGProSettings::logsEnabled().
 *
 * @package PGPFGForGivewp
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SettingsSplitTest extends TestCase
{
    private static function pluginsDir(): string
    {
        $dir = realpath(dirname(dirname(dirname(__DIR__))));

        if (false === $dir) {
            self::fail('Diretório de plugins não resolvido.');
        }

        return $dir;
    }

    private static function read(string $relative): string
    {
        $path = self::pluginsDir() . $relative;

        if (! is_file($path)) {
            self::fail('Arquivo ausente: ' . $relative);
        }

        return (string) file_get_contents($path);
    }

    public function testProAdminHasAdvancedDebugAndNoOwnLogsField(): void
    {
        $src = self::read('/payment-gateway-pix-for-givewp-pro/Admin/PGPFGProAdmin.php');

        $this->assertStringContainsString(
            "'id' => 'lkn-payment-pix-pro-debug'",
            $src,
            'Depuração Avançada precisa existir no PRO.'
        );

        $this->assertStringNotContainsString(
            "'id' => 'lkn-payment-pix-pro-logs'",
            $src,
            'O PRO não deve registrar campo próprio de logs; o toggle vive no FREE.'
        );
    }

    public function testProLogReadsSharedKey(): void
    {
        $src = self::read('/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProLog.php');

        $this->assertStringNotContainsString(
            'lkn-payment-pix-pro-logs',
            $src,
            'PGPFGProLog ainda lê a chave antiga de logs.'
        );

        $this->assertStringContainsString(
            'logsEnabled()',
            $src,
            'PGPFGProLog deve consultar PGPFGProSettings::logsEnabled().'
        );
    }

    public function testResolverReadsAdvancedDebugFromProKey(): void
    {
        require_once self::pluginsDir() . '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProSettings.php';

        GiveOptionStub::$values = ['lkn-payment-pix-pro-debug' => 'enabled'];

        $this->assertTrue(
            \Lkn\PGPFGPro\Includes\PGPFGProSettings::consoleDebug(),
            'consoleDebug deve ler lkn-payment-pix-pro-debug.'
        );
    }

    public function testResolverFallsBackToLegacyDebugKey(): void
    {
        require_once self::pluginsDir() . '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProSettings.php';

        GiveOptionStub::$values = [
            'lkn-payment-pix-pro-debug' => '',
            'lkn-payment-pix-debug' => 'enabled',
        ];

        $this->assertTrue(
            \Lkn\PGPFGPro\Includes\PGPFGProSettings::consoleDebug(),
            'consoleDebug deve cair na chave antiga do FREE quando a do PRO estiver vazia.'
        );
    }

    public function testResolverLogsEnabledReadsSharedKey(): void
    {
        require_once self::pluginsDir() . '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProSettings.php';

        GiveOptionStub::$values = [
            'lkn-payment-pix-logs' => 'enabled',
            'lkn-payment-pix-pro-logs' => 'disabled',
        ];

        $this->assertTrue(
            \Lkn\PGPFGPro\Includes\PGPFGProSettings::logsEnabled(),
            'logsEnabled deve priorizar a chave compartilhada.'
        );
    }

    public function testResolverLogsEnabledFallsBackToLegacy(): void
    {
        require_once self::pluginsDir() . '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProSettings.php';

        GiveOptionStub::$values = [
            'lkn-payment-pix-logs' => '',
            'lkn-payment-pix-pro-logs' => 'enabled',
        ];

        $this->assertTrue(
            \Lkn\PGPFGPro\Includes\PGPFGProSettings::logsEnabled(),
            'logsEnabled deve cair na chave antiga do PRO.'
        );
    }

    public function testResolverLogsDefaultsToFalse(): void
    {
        require_once self::pluginsDir() . '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProSettings.php';

        GiveOptionStub::$values = [];

        $this->assertFalse(\Lkn\PGPFGPro\Includes\PGPFGProSettings::logsEnabled());
    }
}
