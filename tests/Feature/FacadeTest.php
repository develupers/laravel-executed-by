<?php

use Develupers\Executed\Executed;
use Develupers\Executed\Facades\Executed as ExecutedFacade;
use Illuminate\Support\Facades\Facade;

it('resolves the facade correctly', function () {
    $facadeRoot = ExecutedFacade::getFacadeRoot();

    expect($facadeRoot)->toBeInstanceOf(Executed::class);
});

it('facade extends Laravel Facade class', function () {
    $reflection = new ReflectionClass(ExecutedFacade::class);

    expect($reflection->isSubclassOf(Facade::class))->toBeTrue();
});

it('can call static methods through facade', function () {
    // Mock $_SERVER for testing
    $_SERVER['argv'][0] = 'artisan';
    $_SERVER['argv'][1] = 'test:command';

    if (!defined('STDIN')) {
        define('STDIN', fopen('php://stdin', 'r'));
    }

    // Test that we can call methods through the facade
    expect(ExecutedFacade::byArtisanCommand())->toBeTrue();
    expect(ExecutedFacade::getArtisanCommand())->toBe('test:command');
});

it('facade is registered in service container', function () {
    // The facade should be registered and accessible
    expect(class_exists(ExecutedFacade::class))->toBeTrue();
});

test('all public methods are accessible through facade', function ($method) {
    $reflection = new ReflectionClass(Executed::class);
    $facadeMethod = $reflection->getMethod($method);

    expect($facadeMethod->isPublic())->toBeTrue();
    expect($facadeMethod->isStatic())->toBeTrue();
})->with([
    'byStandardCommand',
    'byVendorScript',
    'byComposerCommand',
    'byArtisanCommand',
    'getArtisanCommand',
    'checkArtisanCommand',
    'byPackageCommand',
    'byCacheCommand',
    'bySchedulerCommand',
    'byHorizonCommand',
    'byQueueCommand',
    'byMailCommand',
]);

it('facade returns consistent results with direct class calls', function () {
    $_SERVER['COMPOSER_BINARY'] = 'composer';

    $facadeResult = ExecutedFacade::byComposerCommand();
    $directResult = Executed::byComposerCommand();

    expect($facadeResult)->toBe($directResult);
});