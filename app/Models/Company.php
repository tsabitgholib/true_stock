<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Infrastructure\Traits\Loggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use Loggable, SoftDeletes;
    protected $fillable = ['name', 'code', 'address'];

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }
}
