<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'name',
        'dpi',
        'role',
        'qr_code',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }
}
