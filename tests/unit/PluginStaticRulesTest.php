<?php
/**
 * Regras estáticas que o Plugin Check oficial cobra e que já causaram bug real.
 *
 * Estes testes são unitários de validação de código (scan de arquivos), não
 * sobem o WordPress: eles travam o comportamento esperado do fonte.
 *
 * @package PGPFGForGivewp
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PluginStaticRulesTest extends TestCase
{
    private const TEXT_DOMAIN = 'payment-gateway-pix-for-givewp';

    /**
     * Caminho real (sem "..") — sem realpath() o filtro de exclusão de /tests/
     * casaria com todos os arquivos e a varredura passaria vazia.
     */
    private static function pluginDir(): string
    {
        $dir = realpath(__DIR__ . '/../..');

        if (false === $dir) {
            self::fail('Raiz do plugin não resolvida.');
        }

        return $dir;
    }

    /**
     * @return array<string, string>
     */
    private static function phpFiles(): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::pluginDir(), FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $path = $file->getPathname();

            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            if (str_contains($path, '/vendor/') || str_contains($path, '/node_modules/') || str_contains($path, '/tests/')) {
                continue;
            }

            $files[$path] = (string) file_get_contents($path);
        }

        return $files;
    }

    public function testNoTranslationUsesProTextDomain(): void
    {
        $offenders = [];

        foreach (self::phpFiles() as $path => $contents) {
            if (preg_match_all("/__\(\s*[^,]+,\s*'payment-gateway-pix-for-givewp-pro'\s*\)/", $contents, $m)) {
                foreach ($m[0] as $hit) {
                    $offenders[] = basename($path) . ': ' . $hit;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Text domain 'payment-gateway-pix-for-givewp-pro' não existe no plugin free:\n" . implode("\n", $offenders)
        );
    }

    public function testReadmeDeclaresCurrentWordPressAsTested(): void
    {
        $readme = (string) file_get_contents(self::pluginDir() . '/README.txt');

        $this->assertSame(
            1,
            preg_match('/^Tested up to:\s*([0-9.]+)\s*$/m', $readme, $m),
            'README.txt precisa declarar "Tested up to".'
        );

        $this->assertTrue(
            version_compare($m[1], '7.1', '>='),
            "Tested up to está em {$m[1]}; o WordPress atual é 7.1.5."
        );
    }

    /**
     * Todo caminho de asset/template montado com PGPFG_PIX_PLUGIN_DIR|URL precisa
     * existir de verdade — no Linux a checagem é case-sensitive.
     */
    public function testEveryAssetPathResolvesOnDisk(): void
    {
        $pattern = "/PGPFG_PIX_PLUGIN_(?:DIR|URL)\s*\.\s*'([^']+)'/";

        $missing = [];

        foreach (self::phpFiles() as $path => $contents) {
            if (! preg_match_all($pattern, $contents, $m)) {
                continue;
            }

            foreach ($m[1] as $relative) {
                $absolute = self::pluginDir() . '/' . ltrim($relative, '/');
                if ('logs/' === $relative || is_dir($absolute)) {
                    continue;
                }
                if (! file_exists($absolute)) {
                    $missing[] = basename($path) . " -> {$relative}";
                }
            }
        }

        $this->assertSame([], $missing, "Caminhos inexistentes:\n" . implode("\n", $missing));
    }

    public function testAdminScreenDetectionIsAnnotatedForNonceRule(): void
    {
        $admin = (string) file_get_contents(self::pluginDir() . '/Admin/PGPFGForGivewpAdmin.php');

        $this->assertGreaterThanOrEqual(
            2,
            substr_count($admin, 'phpcs:ignore WordPress.Security.NonceVerification.Recommended'),
            'As leituras de $_GET que só escolhem enqueue precisam da anotação phpcs:ignore justificada.'
        );
    }

    public function testPaghiperPageThrowsGlobalException(): void
    {
        $page = (string) file_get_contents(self::pluginDir() . '/Public/PGPFGForPaghiperPixPage.php');

        $this->assertStringNotContainsString(
            'throw new Exception(',
            $page,
            'Dentro do namespace, "Exception" resolve para Pgpfg\\...\\Exception (inexistente). Use \\Exception.'
        );
    }
}
