<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\UpdateStoreAccountRequest;
use App\Http\Requests\Store\UpdateStoreLogoRequest;
use App\Http\Requests\Store\UpdateStorePasswordRequest;
use App\Http\Requests\Store\UpdateStoreProfileRequest;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the Store Settings page with store profile, legal info, preferences & account details.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $store = $user->store;

        // Calculate drug license expiry status
        $isExpired = false;
        $isExpiringSoon = false;
        $daysToExpiry = null;

        if ($store->license_expiry_date) {
            $today = Carbon::today();
            $expiryDate = Carbon::parse($store->license_expiry_date)->startOfDay();

            if ($expiryDate->isPast() && ! $expiryDate->isSameDay($today)) {
                $isExpired = true;
                $daysToExpiry = (int) $today->diffInDays($expiryDate, false); // negative number
            } else {
                $daysToExpiry = (int) $today->diffInDays($expiryDate, false);
                if ($daysToExpiry <= 30) {
                    $isExpiringSoon = true;
                }
            }
        }

        return view('store.settings.index', compact(
            'user',
            'store',
            'isExpired',
            'isExpiringSoon',
            'daysToExpiry'
        ));
    }

    /**
     * Update Store Profile, Contact, Address, Legal, and Preferences.
     */
    public function updateProfile(UpdateStoreProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $store = $user->store;

        $oldValues = $store->only([
            'name',
            'store_type',
            'email',
            'mobile',
            'alternate_mobile',
            'address',
            'city',
            'state',
            'pincode',
            'gstin',
            'drug_license_no',
            'license_expiry_date',
            'settings',
        ]);

        // 1. Prepare settings/preferences JSON
        $settings = $store->settings ?? [];
        if ($request->filled('timezone')) {
            $settings['timezone'] = $request->input('timezone');
        }
        if ($request->filled('currency')) {
            $settings['currency'] = $request->input('currency');
        }
        if ($request->filled('date_format')) {
            $settings['date_format'] = $request->input('date_format');
        }
        if ($request->has('invoice_prefix')) {
            $prefix = strtoupper(trim((string) $request->input('invoice_prefix', 'INV')));
            $settings['invoice_prefix'] = empty($prefix) ? 'INV' : $prefix;
        }

        // 2. Update store fields (ignoring code, status, store_id)
        $store->update([
            'name' => $request->input('name'),
            'store_type' => $request->input('store_type'),
            'email' => $request->input('email'),
            'mobile' => $request->input('mobile'),
            'alternate_mobile' => $request->input('alternate_mobile'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'pincode' => $request->input('pincode'),
            'gstin' => $request->input('gstin'),
            'drug_license_no' => $request->input('drug_license_no'),
            'license_expiry_date' => $request->input('license_expiry_date'),
            'settings' => $settings,
        ]);

        $newValues = $store->only([
            'name',
            'store_type',
            'email',
            'mobile',
            'alternate_mobile',
            'address',
            'city',
            'state',
            'pincode',
            'gstin',
            'drug_license_no',
            'license_expiry_date',
            'settings',
        ]);

        // 3. Record Audit Log
        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STORES,
            "Store Owner updated store profile and preferences for {$store->name} ({$store->code})",
            $store,
            $oldValues,
            $newValues,
            $user
        );

        return redirect()->route('store.settings.index')
            ->with('status', 'Store profile updated successfully.');
    }

    /**
     * Upload or update the store logo image.
     */
    public function updateLogo(UpdateStoreLogoRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $store = $user->store;

        $oldLogo = $store->logo;

        // Store new logo in public disk
        $path = $request->file('logo')->store('stores/logos', 'public');

        // Safely prune previous logo if exists
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        $store->update(['logo' => $path]);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STORES,
            "Store Owner updated store logo for {$store->name} ({$store->code})",
            $store,
            ['logo' => $oldLogo],
            ['logo' => $path],
            $user
        );

        return redirect()->route('store.settings.index')
            ->with('status', 'Store logo updated successfully.');
    }

    /**
     * Remove the current store logo.
     */
    public function removeLogo(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $store = $user->store;

        $oldLogo = $store->logo;

        if ($oldLogo) {
            if (Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $store->update(['logo' => null]);

            AuditLogger::log(
                AuditAction::UPDATED,
                AuditModule::STORES,
                "Store Owner removed store logo for {$store->name} ({$store->code})",
                $store,
                ['logo' => $oldLogo],
                ['logo' => null],
                $user
            );
        }

        return redirect()->route('store.settings.index')
            ->with('status', 'Store logo removed successfully.');
    }

    /**
     * Update the authenticated Store Owner's personal account info.
     */
    public function updateAccount(UpdateStoreAccountRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
        ];

        // Update personal fields only (never touching role, store_id, is_active)
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'mobile' => $request->input('mobile'),
        ]);

        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
        ];

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STORE_OWNERS,
            "Store Owner updated personal account profile: {$user->name}",
            $user,
            $oldValues,
            $newValues,
            $user
        );

        return redirect()->route('store.settings.index')
            ->with('status', 'Personal profile updated successfully.');
    }

    /**
     * Update the authenticated Store Owner's password.
     */
    public function updatePassword(UpdateStorePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::AUTH,
            "Store Owner changed account password for {$user->email}",
            $user,
            null,
            null,
            $user
        );

        return redirect()->route('store.settings.index')
            ->with('status', 'Password changed successfully.');
    }
}
