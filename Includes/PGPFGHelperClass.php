<?php

namespace Pgpfg\PGPFGForGivewp\Includes;

use DateTime;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentPending;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Log\LogFactory;

/**
 * @inheritDoc
 */
final class PGPFGHelperClass
{
    /**
     * Adiciona link "Ver Changelogs" na linha de metadados do plugin
     * 
     * @param array $plugin_meta Array de links de metadados do plugin
     * @param string $plugin_file Caminho do arquivo principal do plugin
     * @return array Array modificado com o novo link
     */
    public static function add_changelog_link($plugin_meta, $plugin_file)
    {
        // Early return se não for nosso plugin
        if (plugin_basename(PGPFG_PIX_PLUGIN_FILE) !== $plugin_file) {
            return $plugin_meta;
        }

        $changelog_link = '<a href="https://br.wordpress.org/plugins/payment-gateway-pix-for-givewp/#developers" target="_blank">' . __('Ver Changelogs', 'payment-gateway-pix-for-givewp') . '</a>';

        // Procura pela posição do "Ver detalhes" e insere imediatamente após
        foreach ($plugin_meta as $index => $meta) {
            if (
                strpos($meta, 'plugin-install.php') !== false &&
                (strpos($meta, 'Ver detalhes') !== false || strpos($meta, 'View details') !== false)
            ) {

                // Divide e junta: antes + Ver detalhes + Ver Changelogs + depois
                $before_details = array_slice($plugin_meta, 0, $index + 1);
                $after_details = array_slice($plugin_meta, $index + 1);
                return array_merge($before_details, [$changelog_link], $after_details);
            }
        }

        // Fallback: se não encontrou "Ver detalhes", adiciona antes do último elemento
        array_splice($plugin_meta, -1, 0, $changelog_link);
        return $plugin_meta;
    }

    /**
     * Makes a .log file for each donation.
     *
     * @param string $message
     * @param mixed $configs
     */
    public static function log($logType, $data): void
    {
        if (give_get_option('lkn-payment-pix-log-setting') === 'disabled') {
            return;
        }

        $logFactory = new LogFactory();
        $log = $logFactory->make(
            $logType,
            'Payment Gateway Pix for GiveWP Log',
            'Payment Gateway Pix for GiveWP Log',
            'Payment Gateway Pix for GiveWP',
            $data
        );
        $log->save();
    }

    /*
     * Deletes log files older than 15 days.
     */
    public static function delete_old_logs(): void
    {
        $logsPath = __DIR__ . '/logs';
        foreach (scandir($logsPath) as $logFilename) {
            if ('.' !== $logFilename && '..' !== $logFilename && 'index.php' !== $logFilename) {
                $logDate = explode('-', $logFilename)[0];
                $logDate = explode('.', $logDate);
                $logDay = $logDate[0];
                $logMonth = $logDate[1];
                $logYear = $logDate[2];
                $logDate = $logYear . '-' . $logMonth . '-' . $logDay;
                $logDate = new DateTime($logDate);
                $now = new DateTime(gmdate('Y-m-d'));
                $interval = $logDate->diff($now);
                $logAge = $interval->format('%a');
                if ($logAge >= 15) {
                    wp_delete_file($logsPath . '/' . $logFilename);
                }
            }
        }
    }
}
