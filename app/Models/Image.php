<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Image extends Model
{
    use HasFactory;
    protected $fillable = [
        'name','path','size','type','user_id','group_id','expense_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id','id');
    }
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'expense_id', 'id');
    }
}
