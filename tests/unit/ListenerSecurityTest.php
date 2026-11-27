<?php
/**
 * Regras de segurança do listener PagHiper e do polling AJAX.
 *
 * Testes de validação estática do fonte: travam as invariantes que, quando
 * violadas, permitem forjar confirmação de pagamento sem dinheiro.
 *
 * @package PGPFGForGivewp
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ListenerSecurityTest extends TestCase
{
    private static function pluginDir(): string
    {
        $dir = realpath(__DIR__ . '/../..');

        if (false === $dir) {
            self::fail('Raiz do plugin não resolvida.');
        }

        return $dir;
    }

    private static function main(): string
    {
        return (string) file_get_contents(self::pluginDir() . '/Includes/PGPFGForGivewp.php');
    }

    private static function abstractGateway(): string
    {
        return (string) file_get_contents(self::pluginDir() . '/Public/PGPFGGatewayPaghiperAbstractPayment.php');
    }

    /**
     * Sem a rota registrada o PagHiper recebe 404 e a doação nunca confirma.
     */
    public function testListenerRouteIsRegistered(): void
    {
        $source = self::main() . self::abstractGateway();

        $this->assertMatchesRegularExpression(
            "/register_rest_route\s*\(\s*'lkn-pgpf-give-paghiper-listener\/v1'\s*,\s*'\/notification'/",
            $source,
            'A rota /lkn-pgpf-give-paghiper-listener/v1/notification precisa ser registrada.'
        );
    }

    /**
     * A apiKey tem de vir da configuração do site. Aceitar a do corpo permite
     * que qualquer um poste uma notificação com a própria conta.
     */
    public function testListenerNeverTrustsRequestApiKey(): void
    {
        $source = self::abstractGateway();

        $this->assertStringNotContainsString(
            "\$params['apiKey']",
            $source,
            'A apiKey da notificação não pode ser lida do corpo da requisição.'
        );

        $listener = $this->methodBody($source, 'listener');

        $this->assertStringContainsString(
            "\$configs['apiKey']",
            $listener,
            'O listener deve usar a apiKey configurada no site.'
        );
    }

    /**
     * transaction_id recebido deve bater com o que foi gravado na criação,
     * senão uma notificação de outra doação altera esta.
     */
    public function testListenerBindsNotificationToStoredTransaction(): void
    {
        $listener = $this->methodBody(self::abstractGateway(), 'listener');

        $this->assertMatchesRegularExpression(
            '/lkn_pgpf_give_paghiper_response|lkn_pgpf_give_paghiper_transaction/',
            $listener,
            'O listener precisa ler o meta gravado na criação para comparar a transação.'
        );

        $this->assertMatchesRegularExpression(
            '/transactionId\s*!==|transaction_id\s*!==|\$storedTransaction/',
            $listener,
            'O listener precisa comparar a transação recebida com a armazenada.'
        );
    }

    /**
     * Doação inexistente ou de outro gateway não pode seguir adiante.
     */
    public function testListenerRejectsUnknownDonation(): void
    {
        $listener = $this->methodBody(self::abstractGateway(), 'listener');

        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*!?\s*\$donation\b/',
            $listener,
            'O listener precisa barrar quando a doação não é encontrada.'
        );

        $this->assertMatchesRegularExpression(
            '/lkn-pgpf-give-paghiper-(pix|slip)/',
            $listener,
            'O listener precisa validar que a doação pertence a um gateway PagHiper.'
        );
    }

    /**
     * Doação já concluída não pode voltar para failed/pending.
     */
    public function testListenerDoesNotRegressCompletedDonation(): void
    {
        $listener = $this->methodBody(self::abstractGateway(), 'listener');

        $this->assertMatchesRegularExpression(
            '/DonationStatus::COMPLETE\(\)/',
            $listener,
            'O listener ainda precisa concluir doações pagas.'
        );

        $this->assertMatchesRegularExpression(
            '/isComplete\(\)|=== *[\'"]complete[\'"]|getValue\(\) *=== *[\'"]complete[\'"]/',
            $listener,
            'O listener precisa ignorar notificações que rebaixariam uma doação já completa.'
        );
    }

    /**
     * O AJAX só pode confirmar a transação que pertence à doação informada.
     */
    public function testAjaxPollingBindsTransactionToDonation(): void
    {
        $main = self::main();
        $start = strpos($main, 'function get_paghipder_pix_status');

        $this->assertNotFalse($start, 'Handler AJAX não encontrado.');
        $body = substr($main, (int) $start, 4200);

        $this->assertStringContainsString(
            'lkn_pgpf_give_paghiper_response',
            $body,
            'O AJAX precisa ler o meta da doação para comparar a transação.'
        );

        $this->assertMatchesRegularExpression(
            '/transaction.*!==|!==.*transaction|hash_equals/',
            $body,
            'O AJAX precisa comparar a transação recebida com a armazenada na doação.'
        );
    }

    /**
     * PagHiper envia JSON. get_body_params() volta vazio nesse caso, entao o
     * listener precisa aceitar tambem get_json_params() — senao toda
     * notificacao real morre antes de ser validada.
     */
    public function testListenerAcceptsJsonPayload(): void
    {
        $listener = $this->methodBody(self::abstractGateway(), 'listener');

        $this->assertStringContainsString(
            'get_json_params',
            $listener,
            'O listener precisa ler o corpo JSON enviado pelo PagHiper.'
        );
    }

    private function methodBody(string $source, string $method): string
    {
        $pos = strpos($source, 'function ' . $method . '(');
        $this->assertNotFalse($pos, "Método {$method} não encontrado.");

        $slice = substr($source, (int) $pos);

        // Recorta até o próximo método público, para não capturar o resto da classe.
        $next = preg_match('/\n    (public|protected|private)\s+(static\s+)?function\s+/', $slice, $m, PREG_OFFSET_CAPTURE, 10);

        return $next ? substr($slice, 0, (int) $m[0][1]) : $slice;
    }
}
