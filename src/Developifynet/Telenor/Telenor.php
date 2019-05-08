<?php

namespace Developifynet\Telenor;

use Illuminate\Support\Facades\Facade;

class Telenor extends Facade
{
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'telenor';
    }
}