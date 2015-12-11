<?php

namespace Hexavel\Traits;

use Illuminate\Support\Facades\Artisan;

trait DatabaseTrait
{
    /**
     * Migrate the database before each scenario.
     *
     * @beforeScenario
     */
    public function migrate()
    {
        Artisan::call('migrate:reset');
        Artisan::call('migrate');
    }
}