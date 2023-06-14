<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'group_id', 'user_name' ,'amount', 'date', 'description'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id','id');
    }
    public function groupUser(): HasMany
    {
        return $this->hasMany(GroupUser::class, 'group_id', 'group_id')->with('isUser');
    }
}
