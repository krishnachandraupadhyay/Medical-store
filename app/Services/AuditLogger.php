<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Keys that should never be stored in audit logs.
     *
     * @var list<string>
     */
    protected static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'api_key',
        'secret',
        'card_number',
        'cvv',
        'cvc',
        'access_token',
        'refresh_token',
    ];

    /**
     * Record an administrative audit log entry.
     */
    public static function log(
        string|AuditAction $action,
        string|AuditModule $module,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null
    ): ?AuditLog {
        // Respect global audit logging setting (enabled by default unless explicitly 0/false)
        $isEnabled = setting('enable_audit_logging', true);
        if ($isEnabled === false || $isEnabled === 0 || $isEnabled === '0') {
            return null;
        }

        try {
            $actionValue = $action instanceof AuditAction ? $action->value : $action;
            $moduleValue = $module instanceof AuditModule ? $module->value : $module;

            $sanitizedOld = $oldValues ? self::sanitizeValues($oldValues) : null;
            $sanitizedNew = $newValues ? self::sanitizeValues($newValues) : null;

            $userId = $user?->id ?? auth()->id();
            $ipAddress = request()?->ip();
            $userAgent = request()?->userAgent();

            return AuditLog::create([
                'user_id' => $userId,
                'action' => $actionValue,
                'module' => $moduleValue,
                'description' => $description,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->getKey(),
                'old_values' => $sanitizedOld,
                'new_values' => $sanitizedNew,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to write audit log: '.$e->getMessage(), [
                'action' => $action,
                'module' => $module,
                'description' => $description,
            ]);

            return null;
        }
    }

    /**
     * Sanitize array values to strip sensitive information.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected static function sanitizeValues(array $values): array
    {
        $sanitized = [];

        foreach ($values as $key => $value) {
            $lowerKey = strtolower((string) $key);

            // Strip sensitive credentials
            if (in_array($lowerKey, self::$sensitiveKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeValues($value);
            } elseif (is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $sanitized[$key] = self::sanitizeValues($value->toArray());
                } else {
                    $sanitized[$key] = (string) $value;
                }
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
