<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'amountToPayByUser', 'date', 'comment', 'accessCode', 'ownerId', 'status'
    ];

    public function groupOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, "ownerId", "id");
    }

    /**
     * @return BelongsToMany
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, "group_user", "group_id", "user_id")->withPivot('user_id')->withTimestamps();
    }


    public function getAmountOfParticipants()
    {
        return $this->participants->count();
    }
    public function groupUser(): HasMany
    {
        return $this->hasMany(GroupUser::class);
    }
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

}
