<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Setting\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Known boolean setting keys grouped by group name.
     *
     * @var array<string, array<string>>
     */
    protected array $booleanKeys = [
        'system' => [
            'maintenance_mode',
            'enable_store_registration',
            'enable_store_owner_creation',
        ],
        'security' => [
            'require_strong_password',
            'login_attempt_protection',
            'enable_audit_logging',
        ],
        'notification' => [
            'enable_in_app_notifications',
            'enable_email_notifications',
            'enable_sms_notifications',
            'enable_whatsapp_notifications',
        ],
        'subscription' => [
            'allow_trial',
            'allow_new_subscriptions',
        ],
    ];

    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Display the platform settings interface.
     */
    public function index(Request $request): View
    {
        $validTabs = ['general', 'branding', 'contact', 'system', 'security', 'notification', 'subscription'];
        $activeTab = $request->get('tab', 'general');

        if (! in_array($activeTab, $validTabs, true)) {
            $activeTab = 'general';
        }

        $allSettings = Setting::with('updatedBy')->get()->keyBy('key');
        $values = $this->settingService->all();

        return view('super-admin.settings.index', [
            'activeTab' => $activeTab,
            'allSettings' => $allSettings,
            'values' => $values,
        ]);
    }

    /**
     * Update settings for a specific group.
     */
    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $group = $request->validated('group');
        $validated = $request->validated();
        $userId = $request->user()?->id;

        $updates = [];

        // Handle File uploads for Branding
        if ($group === 'branding') {
            $fileKeys = ['app_logo', 'app_favicon', 'login_logo'];

            foreach ($fileKeys as $fileKey) {
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    // Store file in public disk under branding directory
                    $path = $file->store('branding', 'public');
                    $updates[$fileKey] = $path;
                }
            }
        }

        // Handle Booleans for groups with checkboxes
        if (isset($this->booleanKeys[$group])) {
            foreach ($this->booleanKeys[$group] as $boolKey) {
                $updates[$boolKey] = $request->boolean($boolKey) ? '1' : '0';
            }
        }

        // Handle all other validated keys
        foreach ($validated as $key => $value) {
            if ($key === 'group' || isset($updates[$key])) {
                continue;
            }

            if (! in_array($key, ['app_logo', 'app_favicon', 'login_logo'], true)) {
                $updates[$key] = $value;
            }
        }

        // Save batch
        if (! empty($updates)) {
            $this->settingService->setMany($updates, $userId);
        }

        return redirect()->route('super-admin.settings.index', ['tab' => $group])
            ->with('success', ucfirst($group).' settings have been updated successfully.');
    }
}
