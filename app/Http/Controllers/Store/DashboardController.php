<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Store Owner placeholder dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $store = $user->store;

        return view('store.dashboard', compact('user', 'store'));
    }
}
