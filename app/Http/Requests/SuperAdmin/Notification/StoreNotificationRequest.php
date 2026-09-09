<?php

namespace App\Http\Requests\SuperAdmin\Notification;

use App\Enums\NotificationPriority;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(NotificationType::class)],
            'priority' => ['required', Rule::enum(NotificationPriority::class)],
            'target_type' => ['required', Rule::enum(NotificationTargetType::class)],
            'store_id' => [
                'nullable',
                Rule::requiredIf($this->input('target_type') === NotificationTargetType::SPECIFIC_STORE->value),
                'exists:stores,id',
            ],
            'delivery_mode' => ['required', 'string', 'in:now,schedule,draft'],
            'scheduled_at' => [
                'nullable',
                Rule::requiredIf($this->input('delivery_mode') === 'schedule'),
                'date',
                'after:now',
            ],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Notification title is required.',
            'message.required' => 'Notification message content is required.',
            'type.required' => 'Please select a valid notification type.',
            'priority.required' => 'Please select a priority level.',
            'target_type.required' => 'Please select a target audience.',
            'store_id.required_if' => 'Please select a specific store when targeting a single store.',
            'store_id.exists' => 'The selected store does not exist.',
            'scheduled_at.required_if' => 'Please specify a scheduled date and time when choosing Schedule Later.',
            'scheduled_at.after' => 'The scheduled time must be a date and time in the future.',
        ];
    }
}
