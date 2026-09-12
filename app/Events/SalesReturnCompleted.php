<?php

namespace App\Events;

use App\Models\SalesReturn;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesReturnCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public SalesReturn $salesReturn) {}
}
