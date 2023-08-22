<?php

namespace Northplay\NorthplayApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Northplay\NorthplayApi\NorthplayApi
 */
class NorthplayApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Northplay\NorthplayApi\NorthplayApi::class;
    }
}
