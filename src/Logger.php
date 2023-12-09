<?php
namespace App;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

final class Logger extends AbstractLogger
{
    private static bool $lastPersistant = true;

    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (!self::$lastPersistant) {
            fwrite(STDOUT, "\033[u"); // Restore saved cursor position
            fwrite(STDOUT, "\033[2K"); // Erase line
        }

        if ($level !== LogLevel::INFO) {
            fwrite(STDOUT, "\033[s"); // Save cursor position
        }

        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
                fwrite(STDOUT, $message . PHP_EOL);
                fwrite(STDOUT, 'Press ENTER key to continue...' . PHP_EOL);
                $stdin = fopen('php://stdin', 'r');
                fgets($stdin);
                fwrite(STDOUT, PHP_EOL);
                unset($stdin);
                break;

            case LogLevel::WARNING:
            case LogLevel::NOTICE:
            case LogLevel::DEBUG:
                fwrite(STDOUT, $message . PHP_EOL);
                break;

            case LogLevel::INFO:
                fwrite(STDOUT, $message);
                break;
        }

        self::$lastPersistant = ($level !== LogLevel::INFO);
    }

    function interpolate($message, array $context = array())
    {
        $replace = array();
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }
}
