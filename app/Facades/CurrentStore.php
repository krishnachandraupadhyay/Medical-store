<?php

namespace App\Facades;

use App\Models\Store;
use App\Services\CurrentStoreContext;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Store|null get()
 * @method static int|null id()
 * @method static bool hasStore()
 * @method static void set(?Store $store)
 * @method static void reset()
 *
 * @see CurrentStoreContext
 */
class CurrentStore extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CurrentStoreContext::class;
    }
}
