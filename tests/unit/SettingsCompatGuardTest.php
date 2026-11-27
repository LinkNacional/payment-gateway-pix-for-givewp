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

    protected function setUp(): void
    {
        parent::setUp();

        GiveOptionStub::$values = array();
        GiveOptionStub::$settings = array();
        GiveOptionStub::$options = array();
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

        foreach (array('give_update_option', 'give_delete_option') as $writer) {
            $this->assertStringNotContainsString(
                $writer,
                $src,
                'Guard de compatibilidade não pode escrever em give_settings: ' . $writer
            );
        }

        $this->assertStringNotContainsString(
            "update_option('give_settings'",
            $src,
            'Guard não pode reescrever o array give_settings.'
        );

        $this->assertStringNotContainsString(
            'update_option("give_settings"',
            $src,
            'Guard não pode reescrever o array give_settings.'
        );
    }

    /**
     * O hook give_save_settings_give_settings não serve para detectar escolha:
     * ele recebe o array já mesclado com o valor antigo, então todo campo
     * pareceria salvo. O sinal preciso é o filtro por campo, que recebe
     * $rawValue nulo quando o campo não veio no POST.
     */
    public function testRegisterListensToPerFieldSanitizeFilter(): void
    {
        $src = self::read(self::COMPAT);

        $this->assertStringContainsString(
            "'give_admin_settings_sanitize_option_' . \$key",
            $src,
            'O guard precisa escutar o filtro por campo ao salvar.'
        );

        $this->assertStringNotContainsString(
            "add_action('give_save_settings_give_settings'",
            $src,
            'give_save_settings_give_settings recebe o array mesclado e marcaria todo campo.'
        );

        $this->assertStringNotContainsString(
            "'give_update_option'",
            $src,
            'give_update_option não é usado pelo Give ao salvar settings.'
        );
    }

    public function testSavingScreenMarksChoiceAsExplicit(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$options = array();
        GiveOptionStub::$values = array('lkn-payment-pix-pro-logs' => 'enabled');

        // Usuário desliga o log na tela nova.
        \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::recordExplicitChoice(
            'disabled',
            array('id' => 'lkn-payment-pix-logs'),
            'disabled'
        );

        $this->assertSame(
            'disabled',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::resolveInherited(
                'disabled',
                'lkn-payment-pix-logs'
            ),
            'Depois de salvar, a escolha do usuário precisa vencer o legado.'
        );
    }

    public function testUnsavedFieldStillInheritsLegacy(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$options = array();
        GiveOptionStub::$values = array('lkn-payment-pix-pro-logs' => 'enabled');

        $this->assertSame(
            'enabled',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::resolveInherited(
                'disabled',
                'lkn-payment-pix-logs'
            ),
            'Campo nunca salvo precisa herdar o valor antigo.'
        );
    }

    public function testCurrentKeyWinsOverLegacyValue(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array(
            'lkn-payment-pix-environment' => 'app',
            'lkn-payment-pix-pro-environment' => 'sandbox',
        );
        GiveOptionStub::$settings = array('lkn-payment-pix-environment' => 'app');

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

    /**
     * A chave nova nasce herdando o valor da antiga.
     */
    public function testNewKeyInheritsLegacyValueWhenEmpty(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array(
            'lkn-payment-pix-environment' => '',
            'lkn-payment-pix-pro-environment' => 'app',
        );

        $this->assertSame(
            'app',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::resolveInherited(
                '',
                'lkn-payment-pix-environment'
            ),
            'Chave nova vazia precisa herdar o valor configurado na versão anterior.'
        );
    }

    public function testNewKeyKeepsExplicitChoiceOverLegacy(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array(
            'lkn-payment-pix-environment' => 'app',
            'lkn-payment-pix-pro-environment' => 'sandbox',
        );
        GiveOptionStub::$settings = array('lkn-payment-pix-environment' => 'app');

        $this->assertSame(
            'app',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::resolveInherited(
                'app',
                'lkn-payment-pix-environment'
            ),
            'Escolha explícita precisa vencer o legado.'
        );
    }

    /**
     * Valor igual ao default do campo é indistinguível de "nunca escolhido".
     * Numa instalação antiga com logs ligados, a chave nova nasce com o default
     * "disabled" e o guard precisa herdar o "enabled" da versão anterior.
     */
    public function testDefaultLookingValueYieldsToLegacy(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array(
            'lkn-payment-pix-logs' => 'disabled',
            'lkn-payment-pix-pro-logs' => 'enabled',
        );

        $this->assertSame(
            'enabled',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::resolveInherited(
                'disabled',
                'lkn-payment-pix-logs'
            ),
            'Log que estava ligado na versão anterior não pode ser desligado pelo default.'
        );
    }

    /**
     * A tela de settings do Give não lê give_get_option(): lê give_settings
     * direto e passa por give_admin_field_get_value. Sem este guard o select
     * abriria vazio numa instalação antiga.
     */
    public function testSettingsScreenShowsInheritedValue(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array(
            'lkn-payment-pix-logs' => '',
            'lkn-payment-pix-pro-logs' => 'enabled',
        );

        $this->assertSame(
            'enabled',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::renderValue(
                '',
                'give_settings',
                'lkn-payment-pix-logs',
                'disabled'
            ),
            'O campo na tela precisa mostrar o valor herdado, não o default.'
        );
    }

    public function testRenderValueIgnoresUnmappedFields(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array();

        $this->assertSame(
            'algum-valor',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::renderValue(
                'algum-valor',
                'give_settings',
                'campo-de-outro-plugin',
                ''
            ),
            'Campo fora do mapa precisa passar intacto.'
        );
    }

    /**
     * A tela do Give passa o default do campo quando a chave nao existe no
     * array give_settings. Sem distinguir "nao salvo" de "salvo como default",
     * uma instalacao antiga com logs habilitados abriria mostrando "Disable".
     */
    public function testRenderValueInheritsWhenFieldAbsentFromSettings(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array('lkn-payment-pix-pro-logs' => 'enabled');
        GiveOptionStub::$settings = array();

        $this->assertSame(
            'enabled',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::renderValue(
                'disabled',
                'give_settings',
                'lkn-payment-pix-logs',
                'disabled'
            ),
            'Campo ausente do give_settings precisa herdar o valor antigo, mesmo recebendo o default.'
        );
    }

    public function testRenderValueKeepsValueAlreadySaved(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array('lkn-payment-pix-pro-logs' => 'disabled');
        GiveOptionStub::$settings = array('lkn-payment-pix-logs' => 'enabled');

        $this->assertSame(
            'enabled',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::renderValue(
                'enabled',
                'give_settings',
                'lkn-payment-pix-logs',
                'disabled'
            ),
            'Valor ja salvo e diferente do default precisa vencer o legado.'
        );
    }

    public function testRenderValueFallsBackToDefaultWithoutLegacy(): void
    {
        require_once self::pluginsDir() . self::COMPAT;

        GiveOptionStub::$values = array();
        GiveOptionStub::$settings = array();

        $this->assertSame(
            'disabled',
            \Pgpfg\PGPFGForGivewp\Includes\PGPFGForGivewpSettingsCompat::renderValue(
                'disabled',
                'give_settings',
                'lkn-payment-pix-logs',
                'disabled'
            ),
            'Instalacao nova, sem legado, precisa mostrar o default.'
        );
    }

    public function testRegisterHooksBothDirections(): void
    {
        $src = self::read(self::COMPAT);

        $this->assertStringContainsString(
            "'give_get_option_' . \$current",
            $src,
            'Falta o filtro da chave nova herdando o legado.'
        );

        $this->assertStringContainsString(
            "'give_get_option_' . \$legacy",
            $src,
            'Falta o filtro que o PRO antigo consulta.'
        );

        $this->assertStringContainsString(
            'give_admin_field_get_value',
            $src,
            'Falta o filtro que pinta o campo na tela de settings.'
        );
    }
}
