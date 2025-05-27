<?php

namespace Develupers\Executed;

class Executed
{

    /**
     * Check if currently running a standard PHP command.
     * Example: php -r "{code}" Laravel Extra Intellisense.
     */
    public static function byStandardCommand(): bool
    {
        return isset($_SERVER['argv'][0]) && $_SERVER['argv'][0] === 'Standard input code';
    }

    /**
     * Check if currently running a vendor script.
     */
    public static function byVendorScript(): bool
    {
        return isset($_SERVER['SCRIPT_FILENAME']) && str_contains($_SERVER['SCRIPT_FILENAME'], 'vendor/');
    }

    /**
     * Check if currently running composer commands
     */
    public static function byComposerCommand(): bool
    {
        return (isset($_SERVER['COMPOSER_BINARY']) && str_contains($_SERVER['COMPOSER_BINARY'], 'composer'));
    }

    /**
     * Check if currently running artisan commands
     */
    public static function byArtisanCommand(): bool
    {
        if (defined('STDIN')) { // Check if running in CLI
            // Check if 'artisan' is the first argument
            return isset($_SERVER['argv'][0]) && $_SERVER['argv'][0] === 'artisan';
        }
        return false;
    }

    /**
     * Get the command
     */
    public static function getArtisanCommand(): string
    {
        return $_SERVER['argv'][1] ?? '';
    }

    /**
     * Check the artisan command
     */
    public static function checkArtisanCommand(string $command, ?string $name = null): bool
    {
        if (!self::byArtisanCommand()) {
            return false;
        }

        if ($name) {
            return self::getArtisanCommand() === $command . ':' . $name;
        }

        return str_starts_with(self::getArtisanCommand(), $command);
    }

    /**
     * Check if currently running package commands
     */
    public static function byPackageCommand(?string $name = null): bool
    {
        return self::checkArtisanCommand('package', $name);
    }

    /**
     * Check if currently running cache commands
     */
    public static function byCacheCommand(): bool
    {
        return self::checkArtisanCommand('cache', 'clear') ||
            self::checkArtisanCommand('config', 'clear') ||
            self::checkArtisanCommand('route', 'clear') ||
            self::checkArtisanCommand('view', 'clear') ||
            self::checkArtisanCommand('event', 'clear') ||
            self::checkArtisanCommand('config', 'cache') ||
            self::checkArtisanCommand('route', 'cache') ||
            self::checkArtisanCommand('view', 'cache') ||
            self::checkArtisanCommand('event', 'cache') ||
            self::checkArtisanCommand('optimize');
    }

    /**
     * Check if currently running scheduler commands
     */
    public static function bySchedulerCommand(?string $name = null): bool
    {
        return self::checkArtisanCommand('scheduler', $name);
    }

    /**
     * Check if currently running horizon commands
     */
    public static function byHorizonCommand(?string $name = null): bool
    {
        return self::checkArtisanCommand('horizon', $name);
    }

    /**
     * Check if currently running queue commands
     */
    public static function byQueueCommand(?string $name = null): bool
    {
        return self::checkArtisanCommand('queue', $name);
    }

    /**
     * Check if currently running mail commands
     */
    public static function byMailCommand(): bool
    {
        return self::checkArtisanCommand('mail');
    }
}
