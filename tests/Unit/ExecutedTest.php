<?php

use Develupers\Executed\Executed;

beforeEach(function () {
    // Reset $_SERVER before each test
    $this->originalServer = $_SERVER;
});

afterEach(function () {
    // Restore original $_SERVER after each test
    $_SERVER = $this->originalServer;
});

describe('byStandardCommand', function () {
    it('returns true when running standard PHP command', function () {
        $_SERVER['argv'][0] = 'Standard input code';

        expect(Executed::byStandardCommand())->toBeTrue();
    });

    it('returns false when not running standard PHP command', function () {
        $_SERVER['argv'][0] = 'artisan';

        expect(Executed::byStandardCommand())->toBeFalse();
    });

    it('returns false when argv is not set', function () {
        unset($_SERVER['argv']);

        expect(Executed::byStandardCommand())->toBeFalse();
    });
});

describe('byVendorScript', function () {
    it('returns true when script is in vendor directory', function () {
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/vendor/bin/phpunit';

        expect(Executed::byVendorScript())->toBeTrue();
    });

    it('returns false when script is not in vendor directory', function () {
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/artisan';

        expect(Executed::byVendorScript())->toBeFalse();
    });

    it('returns false when SCRIPT_FILENAME is not set', function () {
        unset($_SERVER['SCRIPT_FILENAME']);

        expect(Executed::byVendorScript())->toBeFalse();
    });

    test('detects various vendor paths', function ($path, $expected) {
        $_SERVER['SCRIPT_FILENAME'] = $path;

        expect(Executed::byVendorScript())->toBe($expected);
    })->with([
        ['vendor/phpunit/phpunit/phpunit', true],
        ['/app/vendor/bin/pest', true],
        ['./vendor/orchestra/testbench', true],
        ['/usr/local/bin/composer', false],
        ['artisan', false],
    ]);
});

describe('byComposerCommand', function () {
    it('returns true when running composer command', function () {
        $_SERVER['COMPOSER_BINARY'] = '/usr/local/bin/composer';

        expect(Executed::byComposerCommand())->toBeTrue();
    });

    it('returns false when not running composer command', function () {
        $_SERVER['COMPOSER_BINARY'] = '/usr/local/bin/php';

        expect(Executed::byComposerCommand())->toBeFalse();
    });

    it('returns false when COMPOSER_BINARY is not set', function () {
        unset($_SERVER['COMPOSER_BINARY']);

        expect(Executed::byComposerCommand())->toBeFalse();
    });

    test('detects various composer paths', function ($binary) {
        $_SERVER['COMPOSER_BINARY'] = $binary;

        expect(Executed::byComposerCommand())->toBeTrue();
    })->with([
        'composer',
        'composer.phar',
        '/usr/bin/composer',
        './composer',
        'C:\ProgramData\ComposerSetup\bin\composer.bat',
    ]);
});

describe('byArtisanCommand', function () {
    it('returns true when running artisan command in CLI', function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'artisan';

        expect(Executed::byArtisanCommand())->toBeTrue();
    });

    it('returns false when not running artisan command', function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'phpunit';

        expect(Executed::byArtisanCommand())->toBeFalse();
    });

    it('returns false when argv is not set', function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        unset($_SERVER['argv']);

        expect(Executed::byArtisanCommand())->toBeFalse();
    });
});

describe('getArtisanCommand', function () {
    it('returns the artisan command', function () {
        $_SERVER['argv'][1] = 'migrate:fresh';

        expect(Executed::getArtisanCommand())->toBe('migrate:fresh');
    });

    it('returns empty string when command is not set', function () {
        unset($_SERVER['argv'][1]);

        expect(Executed::getArtisanCommand())->toBe('');
    });

    test('returns various artisan commands', function ($command) {
        $_SERVER['argv'][1] = $command;

        expect(Executed::getArtisanCommand())->toBe($command);
    })->with([
        'migrate',
        'migrate:fresh',
        'cache:clear',
        'queue:work',
        'horizon',
        'schedule:run',
        'tinker',
    ]);
});

describe('checkArtisanCommand', function () {
    beforeEach(function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'artisan';
    });

    it('returns true for matching command prefix', function () {
        $_SERVER['argv'][1] = 'migrate:fresh';

        expect(Executed::checkArtisanCommand('migrate'))->toBeTrue();
    });

    it('returns true for exact command match with name', function () {
        $_SERVER['argv'][1] = 'migrate:fresh';

        expect(Executed::checkArtisanCommand('migrate', 'fresh'))->toBeTrue();
    });

    it('returns false for non-matching command', function () {
        $_SERVER['argv'][1] = 'cache:clear';

        expect(Executed::checkArtisanCommand('migrate'))->toBeFalse();
    });

    it('returns false when not running artisan', function () {
        $_SERVER['argv'][0] = 'phpunit';
        $_SERVER['argv'][1] = 'migrate:fresh';

        expect(Executed::checkArtisanCommand('migrate'))->toBeFalse();
    });

    test('checks various command combinations', function ($argv1, $command, $name, $expected) {
        $_SERVER['argv'][1] = $argv1;

        expect(Executed::checkArtisanCommand($command, $name))->toBe($expected);
    })->with([
        ['queue:work', 'queue', 'work', true],
        ['queue:work', 'queue', 'listen', false],
        ['queue:work', 'queue', null, true],
        ['horizon:work', 'horizon', null, true],
        ['cache:clear', 'cache', 'clear', true],
        ['optimize:clear', 'optimize', null, true],
    ]);
});

describe('byPackageCommand', function () {
    beforeEach(function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'artisan';
    });

    it('returns true for package commands', function () {
        $_SERVER['argv'][1] = 'package:discover';

        expect(Executed::byPackageCommand())->toBeTrue();
    });

    it('returns true for specific package command', function () {
        $_SERVER['argv'][1] = 'package:discover';

        expect(Executed::byPackageCommand('discover'))->toBeTrue();
    });

    it('returns false for non-package commands', function () {
        $_SERVER['argv'][1] = 'migrate:fresh';

        expect(Executed::byPackageCommand())->toBeFalse();
    });
});

describe('byCacheCommand', function () {
    beforeEach(function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'artisan';
    });

    test('detects various cache commands', function ($command) {
        $_SERVER['argv'][1] = $command;

        expect(Executed::byCacheCommand())->toBeTrue();
    })->with([
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear',
        'event:clear',
        'config:cache',
        'route:cache',
        'view:cache',
        'event:cache',
        'optimize',
    ]);

    it('returns false for non-cache commands', function () {
        $_SERVER['argv'][1] = 'migrate:fresh';

        expect(Executed::byCacheCommand())->toBeFalse();
    });
});

describe('bySchedulerCommand', function () {
    beforeEach(function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'artisan';
    });

    it('returns true for scheduler commands', function () {
        $_SERVER['argv'][1] = 'scheduler:run';

        expect(Executed::bySchedulerCommand())->toBeTrue();
    });

    it('returns true for specific scheduler command', function () {
        $_SERVER['argv'][1] = 'scheduler:run';

        expect(Executed::bySchedulerCommand('run'))->toBeTrue();
    });

    it('returns false for non-scheduler commands', function () {
        $_SERVER['argv'][1] = 'queue:work';

        expect(Executed::bySchedulerCommand())->toBeFalse();
    });
});

describe('byHorizonCommand', function () {
    beforeEach(function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'artisan';
    });

    it('returns true for horizon commands', function () {
        $_SERVER['argv'][1] = 'horizon:work';

        expect(Executed::byHorizonCommand())->toBeTrue();
    });

    it('returns true for specific horizon command', function () {
        $_SERVER['argv'][1] = 'horizon:work';

        expect(Executed::byHorizonCommand('work'))->toBeTrue();
    });

    it('returns false for non-horizon commands', function () {
        $_SERVER['argv'][1] = 'queue:work';

        expect(Executed::byHorizonCommand())->toBeFalse();
    });

    test('detects various horizon commands', function ($command) {
        $_SERVER['argv'][1] = 'horizon:'.$command;

        expect(Executed::byHorizonCommand($command))->toBeTrue();
    })->with([
        'work',
        'pause',
        'continue',
        'status',
        'terminate',
    ]);
});

describe('byQueueCommand', function () {
    beforeEach(function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'artisan';
    });

    it('returns true for queue commands', function () {
        $_SERVER['argv'][1] = 'queue:work';

        expect(Executed::byQueueCommand())->toBeTrue();
    });

    it('returns true for specific queue command', function () {
        $_SERVER['argv'][1] = 'queue:work';

        expect(Executed::byQueueCommand('work'))->toBeTrue();
    });

    it('returns false for non-queue commands', function () {
        $_SERVER['argv'][1] = 'cache:clear';

        expect(Executed::byQueueCommand())->toBeFalse();
    });

    test('detects various queue commands', function ($command) {
        $_SERVER['argv'][1] = 'queue:'.$command;

        expect(Executed::byQueueCommand($command))->toBeTrue();
    })->with([
        'work',
        'listen',
        'restart',
        'failed',
        'retry',
        'flush',
    ]);
});

describe('byMailCommand', function () {
    beforeEach(function () {
        if (! defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $_SERVER['argv'][0] = 'artisan';
    });

    it('returns true for mail commands', function () {
        $_SERVER['argv'][1] = 'mail:send';

        expect(Executed::byMailCommand())->toBeTrue();
    });

    it('returns false for non-mail commands', function () {
        $_SERVER['argv'][1] = 'queue:work';

        expect(Executed::byMailCommand())->toBeFalse();
    });

    test('detects various mail command patterns', function ($command) {
        $_SERVER['argv'][1] = $command;

        expect(Executed::byMailCommand())->toBeTrue();
    })->with([
        'mail',
        'mail:send',
        'mail:resend',
        'mail:test',
    ]);
});
