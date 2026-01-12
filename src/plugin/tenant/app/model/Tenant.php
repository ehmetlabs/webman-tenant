<?php

declare(strict_types=1);

namespace plugin\tenant\app\model;

use support\Model;

class Tenant extends Model
{
    /**
     * @var string
     */
    protected $table = 'tenants';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $casts = [
        'status' => 'int',
        'is_system' => 'bool',
    ];

    public static function resolveId(string $key): ?int
    {
        $query = static::query();
        if (\ctype_digit($key)) {
            $query->where('id', (int) $key);
        } else {
            $query->where('slug', $key);
        }
        $tenant = $query->first();
        if (!$tenant || 1 !== (int) $tenant->status) {
            return null;
        }

        return (int) $tenant->id;
    }
}
