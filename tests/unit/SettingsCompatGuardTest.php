<?php
/**
 * Guard de compatibilidade: FREE novo + PRO antigo.
 *
 * Depois da unificação, o FREE grava Environment e Logs nas chaves novas
 * (lkn-payment-pix-environment / -logs). Um PRO ainda não atualizado continua
 * lendo as chaves antigas (-pro-environment / -pro-logs) e ignoraria a escolha
 * feita na tela nova — inclusive "Produção", que viraria sandbox silencioso.
 *
 * O guard é só de leitura: mapeia a chave antiga para a nova via filtro
 * give_get_option_{$key}. Nunca escreve em give_settings.
 *
 * @package PGPFGForGivewp
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SettingsCompatGuardTest extends TestCase
{
    private const COMPAT = '/payment-gateway-pix-for-givewp/Includes/PGPFGForGivewpSettingsCompat.php';

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

    public function testCompatClassExists(): void
    {
        $this->assertFileExists(
            self::pluginsDir() . self::COMPAT,
            'O guard de compatibilidade precisa existir no FREE.'
        );
    }

    public function testCompatBridgesBothLegacyKeys(): void
    {
        $src = self::read(self::COMPAT);

        $this->assertStringContainsString(
            "'lkn-payment-pix-pro-environment' => 'lkn-payment-pix-environment'",
            $src,
            'Chave antiga de ambiente precisa apontar para a nova.'
        );

        $this->assertStringContainsString(
            "'lkn-payment-pix-pro-logs' => 'lkn-payment-pix-logs'",
            $src,
            'Chave antiga de logs precisa apontar para a nova.'
        );
    }

    public function testCompatClassDoesNotWriteSettings(): void
    {
        $src = self::read(self::COMPAT);

        foreach (array('give_update_option', 'update_option', 'update_option_', 'give_delete_option') as $writer) {
            $this->assertStringNotContainsString(
                $writer,
                $src,
                'Guard de compatibilidade não pode escrever em give_settings: ' . $writer
            );
        }
    }

    public function testCurrentKeyWinsOverLegacyValue(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array(
            'lkn-payment-pix-environment' => 'app',
            'lkn-payment-pix-pro-environment' => 'sandbox',
        );

        $this->assertSame(
            'app',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::resolveLegacy(
                'sandbox',
                'lkn-payment-pix-environment'
            ),
            'O valor escolhido na chave nova precisa vencer o legado.'
        );
    }

    public function testLegacyValueSurvivesWhenCurrentIsEmpty(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array(
            'lkn-payment-pix-environment' => '',
            'lkn-payment-pix-pro-environment' => 'app',
        );

        $this->assertSame(
            'app',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::resolveLegacy(
                'app',
                'lkn-payment-pix-environment'
            ),
            'Instalação antiga sem chave nova precisa continuar lendo o valor legado.'
        );
    }

    public function testCompatIsRegisteredOnAdminHooks(): void
    {
        $src = self::read('/payment-gateway-pix-for-givewp/Includes/PGPFGForGivewp.php');

        $this->assertStringContainsString(
            'PGPFGForGivewpSettingsCompat::register()',
            $src,
            'O guard precisa ser registrado no boot do plugin.'
        );
    }
}
