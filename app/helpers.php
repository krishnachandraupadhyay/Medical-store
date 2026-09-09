<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we assume you want to set an array of values.
     *
     * @param  string|array<string, mixed>|null  $key
     * @param  mixed  $default
     * @return mixed|SettingService
     */
    function setting($key = null, $default = null)
    {
        $service = app(SettingService::class);

        if (is_null($key)) {
            return $service;
        }

        if (is_array($key)) {
            $service->setMany($key, auth()->id());

            return null;
        }

        return $service->get($key, $default);
    }
}
