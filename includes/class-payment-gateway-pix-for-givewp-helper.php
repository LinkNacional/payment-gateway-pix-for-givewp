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
        $logPath = PAYMENT_GATEWAY_PIX_PLUGIN_DIR . '/logs' . '/' . date('d.m.Y-H.i.s') . '.log';
        error_log($message, 3, $logPath);
        chmod($logPath, 0600);
        update_option('payment_gateway_for_givewp_last_log', $logPath);
    }

    /*
     * Deletes log files older than 15 days.
     */
    public static function deleteOldLogs(): void
    {
        $logsPath = PAYMENT_GATEWAY_PIX_PLUGIN_DIR . '/../logs';
        foreach (scandir($logsPath) as $logFilename) {
            if ('.' !== $logFilename && '..' !== $logFilename && 'index.php' !== $logFilename) {
                $logDate = explode('-', $logFilename)[0];
                $logDate = explode('.', $logDate);
                $logDay = $logDate[0];
                $logMonth = $logDate[1];
                $logYear = $logDate[2];
                $logDate = $logYear . '-' . $logMonth . '-' . $logDay;
                $logDate = new DateTime($logDate);
                $now = new DateTime(date('Y-m-d'));
                $interval = $logDate->diff($now);
                $logAge = $interval->format('%a');
                if ($logAge >= 15) {
                    unlink($logsPath . '/' . $logFilename);
                }
            }
        }
    }
}
