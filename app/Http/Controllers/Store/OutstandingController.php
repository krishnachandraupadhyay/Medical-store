<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\OutstandingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutstandingController extends Controller
{
    public function __construct(
        protected OutstandingService $outstandingService
    ) {}

    public function customers(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $search = $request->input('search');
        $customers = $this->outstandingService->getPaginatedCustomerOutstanding($store, $search, 15);
        $totalOutstanding = $this->outstandingService->getTotalCustomerOutstanding($store);

        return view('store.outstanding.customers', compact('store', 'customers', 'totalOutstanding', 'search'));
    }

    public function suppliers(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $search = $request->input('search');
        $suppliers = $this->outstandingService->getPaginatedSupplierOutstanding($store, $search, 15);
        $totalOutstanding = $this->outstandingService->getTotalSupplierOutstanding($store);

        return view('store.outstanding.suppliers', compact('store', 'suppliers', 'totalOutstanding', 'search'));
    }
}
