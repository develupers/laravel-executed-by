<?php

use Develupers\Executed\ExecutedServiceProvider;
use Develupers\Executed\Facades\Executed as ExecutedFacade;

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('main class uses only PHP built-ins')
    ->expect('Develupers\Executed\Executed')
    ->toUseNothing()
    ->ignoring(['str_contains', 'str_starts_with', 'defined']);

arch('facade is properly structured')
    ->expect(ExecutedFacade::class)
    ->toExtend(Illuminate\Support\Facades\Facade::class)
    ->toHaveMethod('getFacadeAccessor');

arch('service provider extends correct base class')
    ->expect(ExecutedServiceProvider::class)
    ->toExtend(Spatie\LaravelPackageTools\PackageServiceProvider::class);

arch('no dependencies on external services')
    ->expect('Develupers\Executed')
    ->not->toUse(['Http', 'DB', 'Cache', 'Redis', 'Storage']);

arch('production code does not use test classes')
    ->expect('Develupers\Executed')
    ->not->toUse('Develupers\Executed\Tests');

arch('no global functions are used except PHP built-ins')
    ->expect('Develupers\Executed\Executed')
    ->toUseNothing()
    ->ignoring([
        'str_contains',
        'str_starts_with',
        'defined',
        'isset',
        'unset',
    ]);

arch('facade only uses its target class')
    ->expect('Develupers\Executed\Facades')
    ->toOnlyUse([
        'Illuminate\Support\Facades\Facade',
        'Develupers\Executed\Executed',
    ]);

arch('tests are properly organized')
    ->expect('Tests\Unit')
    ->toHavePrefix('Tests\Unit')
    ->and('Tests\Feature')
    ->toHavePrefix('Tests\Feature');
