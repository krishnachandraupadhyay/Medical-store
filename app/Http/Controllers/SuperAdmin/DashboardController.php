<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Super Admin Dashboard placeholder.
     */
    public function index(): View
    {
        $admin = Auth::user();

        return view('super-admin.dashboard', [
            'admin' => $admin,
        ]);
    }
}
