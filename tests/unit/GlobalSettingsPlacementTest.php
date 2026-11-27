<?php
/**
 * Garante que as opções globais (Environment e Console Debugging) vivem no
 * plugin FREE, fora de qualquer bloco condicionado à presença do PRO.
 *
 * Esses dois recursos valem para todos os gateways (free e pro), então precisam
 * existir sempre na tela de Settings. Antes desta correção eles só apareciam no
 * FREE quando o PRO estava inativo — exatamente o contrário do esperado.
 *
 * @package PGPFGForGivewp
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GlobalSettingsPlacementTest extends TestCase
{
    private const FREE_ADMIN = '/payment-gateway-pix-for-givewp/Admin/PGPFGForGivewpAdmin.php';
    private const PRO_ADMIN = '/payment-gateway-pix-for-givewp-pro/Admin/PGPFGProAdmin.php';
    private const PRO_RESOLVER = '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProSettings.php';

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

    public function testEnvironmentFieldIsNotGatedBehindPro(): void
    {
        $src = self::read(self::FREE_ADMIN);

        $field = strpos($src, "'id' => 'lkn-payment-pix-environment'");
        $gate = strpos($src, 'if (!$pro_plugin_active)');

        $this->assertNotFalse($field, 'Campo Environment não encontrado no FREE.');
        $this->assertNotFalse($gate, 'Bloco condicional do PRO não encontrado.');
        $this->assertLessThan(
            $gate,
            $field,
            'Environment precisa estar fora do bloco if (!$pro_plugin_active).'
        );
    }

    public function testConsoleDebugFieldDoesNotExistInFree(): void
    {
        $src = self::read(self::FREE_ADMIN);

        $this->assertFalse(
            strpos($src, "'id' => 'lkn-payment-pix-debug'"),
            'Depuração Avançada é exclusiva do PRO e não pode existir no FREE.'
        );
    }

    public function testProNoLongerDefinesEnvironmentField(): void
    {
        $this->assertStringNotContainsString(
            "'id' => 'lkn-payment-pix-pro-environment'",
            self::read(self::PRO_ADMIN),
            'O PRO não deve mais registrar o campo Environment.'
        );
    }

    public function testProStillDefinesDebugField(): void
    {
        $this->assertStringContainsString(
            "'id' => 'lkn-payment-pix-pro-debug'",
            self::read(self::PRO_ADMIN),
            'Depuração Avançada é exclusiva do PRO e deve ser registrada lá.'
        );
    }

    public function testProReadsSharedOptionsThroughResolver(): void
    {
        $this->assertFileExists(self::pluginsDir() . self::PRO_RESOLVER);

        $files = [
            '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProApi.php',
            '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProHelper.php',
            '/payment-gateway-pix-for-givewp-pro/Includes/PGPFGProRequests.php',
            '/payment-gateway-pix-for-givewp-pro/Public/PGPFGProPixForBB.php',
            '/payment-gateway-pix-for-givewp-pro/Public/PGPFGProPixForCielo.php',
            '/payment-gateway-pix-for-givewp-pro/Public/PGPFGProPixForErede.php',
        ];

        foreach ($files as $relative) {
            $src = self::read($relative);
            $this->assertStringNotContainsString(
                "'lkn-payment-pix-pro-environment'",
                $src,
                $relative . ' ainda lê a chave antiga de environment.'
            );
            $this->assertStringNotContainsString(
                '"lkn-payment-pix-pro-environment"',
                $src,
                $relative . ' ainda lê a chave antiga de environment.'
            );
            $this->assertStringNotContainsString(
                "'lkn-payment-pix-pro-debug'",
                $src,
                $relative . ' ainda lê a chave antiga de debug.'
            );
            $this->assertStringNotContainsString(
                '"lkn-payment-pix-pro-debug"',
                $src,
                $relative . ' ainda lê a chave antiga de debug.'
            );
        }
    }

    public function testResolverPrefersSharedKey(): void
    {
        require_once self::pluginsDir() . self::PRO_RESOLVER;

        GiveOptionStub::$values = [
            'lkn-payment-pix-environment' => 'sandbox',
            'lkn-payment-pix-pro-environment' => 'app',
        ];

        $this->assertSame('sandbox', \Lkn\PGPFGPro\Includes\PGPFGProSettings::environment());
    }

    public function testResolverFallsBackToLegacyKey(): void
    {
        require_once self::pluginsDir() . self::PRO_RESOLVER;

        GiveOptionStub::$values = [
            'lkn-payment-pix-environment' => '',
            'lkn-payment-pix-pro-environment' => 'app',
        ];

        $this->assertSame('app', \Lkn\PGPFGPro\Includes\PGPFGProSettings::environment());
    }

    public function testResolverDefaultsToSandbox(): void
    {
        require_once self::pluginsDir() . self::PRO_RESOLVER;

        GiveOptionStub::$values = [];

        $this->assertSame('sandbox', \Lkn\PGPFGPro\Includes\PGPFGProSettings::environment());
        $this->assertFalse(\Lkn\PGPFGPro\Includes\PGPFGProSettings::consoleDebug());
    }
}
