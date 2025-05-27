<?php

namespace Develupers\Executed\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Develupers\Executed\Executed
 */
class Executed extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Develupers\Executed\Executed::class;
    }
}
