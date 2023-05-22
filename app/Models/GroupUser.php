<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupUser extends Model
{
    use HasFactory;
    protected $table = 'group_user';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'group_id', 'user_id', 'created_at', 'updated_at'
    ];

    public function inGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function isUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isMember(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
}

