<?php

namespace Hexavel\Traits;

use Illuminate\Support\Facades\Artisan;

trait CacheTrait
{
    /**
     * Migrate the database before each scenario.
     *
     * @beforeScenario
     */
    public function cacheClear()
    {
        Artisan::call('cache:clear');
    }
}