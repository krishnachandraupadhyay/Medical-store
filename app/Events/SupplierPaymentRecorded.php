<?php

namespace App\Events;

use App\Models\StorePayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierPaymentRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public StorePayment $payment) {}
}
