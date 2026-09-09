<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Cache key for all settings.
     */
    public const CACHE_KEY = 'system_settings_all';

    /**
     * Retrieve a setting value by key with optional fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Retrieve all settings as key => casted_value array (cached).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                return Setting::all()->mapWithKeys(function (Setting $setting) {
                    return [$setting->key => $setting->casted_value];
                })->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Get settings grouped by group name as Collection.
     */
    public function getAllGrouped(): Collection
    {
        return Setting::with('updatedBy')->get()->groupBy('group');
    }

    /**
     * Get settings models for a specific group.
     */
    public function getGroup(string $group): Collection
    {
        return Setting::with('updatedBy')->where('group', $group)->get();
    }

    /**
     * Set/update a single setting.
     */
    public function set(string $key, mixed $value, ?int $userId = null): Setting
    {
        $setting = Setting::firstOrNew(['key' => $key]);

        // Format value based on type
        if ($setting->type === 'boolean') {
            $value = $value ? '1' : '0';
        } elseif ($setting->type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }

        $setting->value = $value !== null ? (string) $value : null;

        if ($userId) {
            $setting->updated_by = $userId;
        }

        $setting->save();

        $this->clearCache();

        return $setting;
    }

    /**
     * Set/update multiple settings in batch.
     *
     * @param  array<string, mixed>  $settings
     */
    public function setMany(array $settings, ?int $userId = null): void
    {
        $existing = Setting::whereIn('key', array_keys($settings))->get()->keyBy('key');

        foreach ($settings as $key => $value) {
            if ($existing->has($key)) {
                $setting = $existing->get($key);
                if ($setting->type === 'boolean') {
                    $setting->value = $value ? '1' : '0';
                } elseif ($setting->type === 'json' && is_array($value)) {
                    $setting->value = json_encode($value);
                } else {
                    $setting->value = $value !== null ? (string) $value : null;
                }

                if ($userId) {
                    $setting->updated_by = $userId;
                }
                $setting->save();
            } else {
                Setting::create([
                    'key' => $key,
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'),
                    'group' => 'general',
                    'updated_by' => $userId,
                ]);
            }
        }

        $this->clearCache();
    }

    /**
     * Clear cached settings.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
