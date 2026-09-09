<?php

namespace App\Models;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'logo',
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
        'store_type',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StoreStatus::class,
            'license_expiry_date' => 'date',
        ];
    }

    /**
     * Get all users assigned to this store.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'store_id');
    }

    /**
     * Get the store owners associated with this store.
     */
    public function owners(): HasMany
    {
        return $this->hasMany(User::class, 'store_id')->where('role', UserRole::STORE_OWNER);
    }

    /**
     * Check if store is active.
     */
    public function isActive(): bool
    {
        return $this->status === StoreStatus::ACTIVE;
    }

    /**
     * Check if store is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === StoreStatus::SUSPENDED;
    }

    /**
     * Check if store is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === StoreStatus::INACTIVE;
    }

    /**
     * Generate a unique sequential store code (e.g. MED-000001).
     */
    public static function generateUniqueCode(): string
    {
        return DB::transaction(function () {
            // Get the highest numeric ID or existing codes count including soft-deleted
            $latestStore = self::withTrashed()
                ->orderByRaw('CAST(SUBSTRING(code, 5) AS UNSIGNED) DESC, id DESC')
                ->first();

            $nextNumber = 1;

            if ($latestStore && preg_match('/MED-(\d+)/', $latestStore->code, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            } else {
                $maxId = self::withTrashed()->max('id') ?? 0;
                $nextNumber = $maxId + 1;
            }

            do {
                $candidateCode = sprintf('MED-%06d', $nextNumber);
                $exists = self::withTrashed()->where('code', $candidateCode)->exists();
                if ($exists) {
                    $nextNumber++;
                }
            } while ($exists);

            return $candidateCode;
        });
    }
}
