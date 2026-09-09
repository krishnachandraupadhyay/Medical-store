<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a paginated listing of system audit logs.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $module = $request->get('module');
        $action = $request->get('action');
        $userId = $request->get('user_id');
        $datePreset = $request->get('date_preset');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Apply Date Presets if supplied
        if ($datePreset === 'today') {
            $dateFrom = Carbon::today()->toDateString();
            $dateTo = Carbon::today()->toDateString();
        } elseif ($datePreset === 'yesterday') {
            $dateFrom = Carbon::yesterday()->toDateString();
            $dateTo = Carbon::yesterday()->toDateString();
        } elseif ($datePreset === '7_days') {
            $dateFrom = Carbon::today()->subDays(6)->toDateString();
            $dateTo = Carbon::today()->toDateString();
        } elseif ($datePreset === '30_days') {
            $dateFrom = Carbon::today()->subDays(29)->toDateString();
            $dateTo = Carbon::today()->toDateString();
        }

        $query = AuditLog::with('user')->latest('id');

        // Search Filter
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('subject_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Module Filter
        if (! empty($module) && in_array($module, AuditModule::values(), true)) {
            $query->where('module', $module);
        }

        // Action Filter
        if (! empty($action) && in_array($action, AuditAction::values(), true)) {
            $query->where('action', $action);
        }

        // User Filter
        if (! empty($userId)) {
            $query->where('user_id', (int) $userId);
        }

        // Date Range Filter
        if (! empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (! empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $query->paginate(20)->withQueryString();

        // High-level Metrics
        $metrics = [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::whereDate('created_at', Carbon::today())->count(),
            'active_admins' => AuditLog::distinct('user_id')->whereNotNull('user_id')->count('user_id'),
            'top_module' => AuditLog::selectRaw('module, count(*) as count')
                ->groupBy('module')
                ->orderByDesc('count')
                ->first()?->module?->label() ?? 'None',
        ];

        // Filter options
        $admins = User::where('role', UserRole::SUPER_ADMIN)->orderBy('name')->get(['id', 'name', 'email']);
        $modules = AuditModule::cases();
        $actions = AuditAction::cases();

        return view('super-admin.audit-logs.index', compact(
            'auditLogs',
            'metrics',
            'admins',
            'modules',
            'actions',
            'search',
            'module',
            'action',
            'userId',
            'datePreset',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display detailed information and before/after diff for a specific audit log record.
     */
    public function show(AuditLog $auditLog): View
    {
        $auditLog->load(['user', 'subject']);

        return view('super-admin.audit-logs.show', compact('auditLog'));
    }
}
