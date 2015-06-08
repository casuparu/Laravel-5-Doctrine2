<?php

namespace Choi\Doctrine\Facades;

use Illuminate\Support\Facades\Facade;

class EntityManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @throws \RuntimeException
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'choi.doctrine.entitymanager';
    }
}
