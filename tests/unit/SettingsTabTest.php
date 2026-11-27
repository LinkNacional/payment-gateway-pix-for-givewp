<?php
/**
 * A aba "Settings" precisa existir no menu do plugin FREE, ao lado de
 * "Pro Settings", e conter apenas as opções globais:
 *   - Environment  (todos os gateways)
 *   - Habilitar Logs (todos os gateways)
 *
 * A "Depuração Avançada (console)" é recurso exclusivo do PRO.
 *
 * @package PGPFGForGivewp
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SettingsTabTest extends TestCase
{
    private static function admin(): string
    {
        $path = realpath(dirname(dirname(__DIR__)) . '/Admin/PGPFGForGivewpAdmin.php');

        if (false === $path) {
            self::fail('Admin do plugin FREE não encontrado.');
        }

        return (string) file_get_contents($path);
    }

    private static function js(): string
    {
        $path = realpath(dirname(dirname(__DIR__)) . '/Admin/js/PGPFGForGivewpAdminSettings.js');

        if (false === $path) {
            self::fail('JS de settings não encontrado.');
        }

        return (string) file_get_contents($path);
    }

    public function testMenuHasSettingsTabRightAfterPaghiper(): void
    {
        $src = self::admin();

        $this->assertStringContainsString('<li id="2">Settings</li>', $src);

        $paghiper = strpos($src, '<li id="1">PagHiper</li>');
        $settings = strpos($src, '<li id="2">Settings</li>');
        $pro = strpos($src, '<li id="3">Pro Settings</li>');

        $this->assertNotFalse($paghiper);
        $this->assertNotFalse($settings);
        $this->assertNotFalse($pro);
        $this->assertTrue($paghiper < $settings && $settings < $pro, 'Ordem: PagHiper, Settings, Pro Settings.');
    }

    public function testSettingsSectionHasEnvironmentAndLogs(): void
    {
        $src = self::admin();

        $title = strpos($src, "'id' => 'lkn-payment-pix-settings'");
        $this->assertNotFalse($title, 'Seção Settings não encontrada.');

        $env = strpos($src, "'id' => 'lkn-payment-pix-environment'");
        $logs = strpos($src, "'id' => 'lkn-payment-pix-logs'");

        $this->assertNotFalse($env, 'Campo Environment não encontrado.');
        $this->assertNotFalse($logs, 'Campo Habilitar Logs não encontrado.');
        $this->assertGreaterThan($title, $env, 'Environment precisa vir depois do título Settings.');
        $this->assertGreaterThan($title, $logs, 'Habilitar Logs precisa vir depois do título Settings.');
    }

    public function testSettingsSectionDoesNotContainConsoleDebug(): void
    {
        $src = self::admin();

        $this->assertStringNotContainsString(
            "'id' => 'lkn-payment-pix-debug'",
            $src,
            'Depuração Avançada é exclusiva do PRO e não pode existir no FREE.'
        );
    }

    public function testSettingsSectionComesBeforeProSettingsGate(): void
    {
        $src = self::admin();

        $settingsTitle = strpos($src, "'id' => 'lkn-payment-pix-settings'");
        $proGate = strpos($src, 'if (!$pro_plugin_active)');

        $this->assertNotFalse($proGate);
        $this->assertLessThan($proGate, $settingsTitle, 'A seção Settings fica fora do bloco do PRO.');
    }

    public function testJsGatesProFromIndexThree(): void
    {
        $js = self::js();

        $this->assertStringContainsString(
            'for (let i = 3; i < lkn_PGPFG_settings.length; i++)',
            $js,
            'O gate de licença começa no índice 3 (Pro Settings), preservando Settings.'
        );
    }
}
