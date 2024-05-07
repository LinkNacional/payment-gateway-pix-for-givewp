<?php

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

/**
 * @inheritDoc
 */
final class PixHelperClass
{
    /**
     * Makes a .log file for each donation.
     *
     * @param string $message
     * @param mixed $configs
     */
    public static function log(string $message): void
    {
        if(give_get_option('lkn-payment-pix-log-setting') === 'disabled') {
            return;
        }

        $logPath = PAYMENT_GATEWAY_PIX_PLUGIN_DIR . 'includes/logs/' . gmdate('d.m.Y-H.i.s') . '.log';

        error_log($message, 3, $logPath);

        give_update_option('payment_gateway_for_givewp_last_log', $logPath);
        give_update_option('payment_gateway_for_givewp_last_log_url', PAYMENT_GATEWAY_PIX_PLUGIN_URL . 'logs/' . gmdate('d.m.Y-H.i.s') . '.log');
    }

    /*
     * Deletes log files older than 15 days.
     */
    public static function delete_old_logs(): void
    {
        $logsPath = PAYMENT_GATEWAY_PIX_PLUGIN_DIR . 'includes/logs';
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
