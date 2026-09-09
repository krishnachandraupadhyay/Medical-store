<?php

namespace App\Http\Requests\SuperAdmin\Setting;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === UserRole::SUPER_ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $group = $this->input('group', 'general');

        $rules = [
            'group' => ['required', 'string', 'in:general,branding,contact,system,security,notification,subscription'],
        ];

        switch ($group) {
            case 'general':
                $rules['app_name'] = ['required', 'string', 'max:100'];
                $rules['app_tagline'] = ['nullable', 'string', 'max:255'];
                $rules['default_timezone'] = ['required', 'string', 'timezone'];
                $rules['default_currency'] = ['required', 'string', 'size:3'];
                $rules['currency_symbol'] = ['required', 'string', 'max:10'];
                $rules['date_format'] = ['required', 'string', 'max:30'];
                $rules['time_format'] = ['required', 'string', 'max:30'];
                break;

            case 'branding':
                $rules['app_logo'] = ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'];
                $rules['app_favicon'] = ['nullable', 'file', 'mimes:ico,png,svg', 'max:1024'];
                $rules['login_logo'] = ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'];
                break;

            case 'contact':
                $rules['support_email'] = ['required', 'email', 'max:255'];
                $rules['support_phone'] = ['nullable', 'string', 'max:50'];
                $rules['company_name'] = ['required', 'string', 'max:255'];
                $rules['company_website'] = ['nullable', 'url', 'max:255'];
                $rules['company_address'] = ['nullable', 'string', 'max:500'];
                break;

            case 'system':
                $rules['maintenance_mode'] = ['nullable', 'boolean'];
                $rules['maintenance_message'] = ['nullable', 'string', 'max:500'];
                $rules['enable_store_registration'] = ['nullable', 'boolean'];
                $rules['enable_store_owner_creation'] = ['nullable', 'boolean'];
                break;

            case 'security':
                $rules['session_timeout'] = ['required', 'integer', 'min:5', 'max:1440'];
                $rules['password_min_length'] = ['required', 'integer', 'min:6', 'max:64'];
                $rules['require_strong_password'] = ['nullable', 'boolean'];
                $rules['login_attempt_protection'] = ['nullable', 'boolean'];
                $rules['enable_audit_logging'] = ['nullable', 'boolean'];
                break;

            case 'notification':
                $rules['enable_in_app_notifications'] = ['nullable', 'boolean'];
                $rules['enable_email_notifications'] = ['nullable', 'boolean'];
                $rules['enable_sms_notifications'] = ['nullable', 'boolean'];
                $rules['enable_whatsapp_notifications'] = ['nullable', 'boolean'];
                break;

            case 'subscription':
                $rules['default_trial_days'] = ['required', 'integer', 'min:0', 'max:365'];
                $rules['allow_trial'] = ['nullable', 'boolean'];
                $rules['allow_new_subscriptions'] = ['nullable', 'boolean'];
                $rules['subscription_expiry_warning_days'] = ['required', 'integer', 'min:1', 'max:60'];
                break;
        }

        return $rules;
    }
}
