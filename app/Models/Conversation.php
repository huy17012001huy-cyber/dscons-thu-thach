<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Concerns\HasBrand;

class Conversation extends Model
{
    use HasBrand;
    protected $fillable = ['user_one_id', 'user_two_id', 'last_message_at', 'brand_id'];
    protected $casts = ['last_message_at' => 'datetime'];

    public function userOne(): BelongsTo { return $this->belongsTo(User::class, 'user_one_id'); }
    public function userTwo(): BelongsTo { return $this->belongsTo(User::class, 'user_two_id'); }
    public function messages(): HasMany { return $this->hasMany(DirectMessage::class); }
    public function lastMessage(): HasOne { return $this->hasOne(DirectMessage::class)->latestOfMany(); }

    public function getOtherUser(int $myId): User
    {
        return $this->user_one_id === $myId ? $this->userTwo : $this->userOne;
    }

    public function unreadCount(int $userId): int
    {
        return $this->messages()->where('sender_id', '!=', $userId)->whereNull('read_at')->count();
    }

    public static function findOrCreateBetween(int $userA, int $userB): self
    {
        $ids = [min($userA, $userB), max($userA, $userB)];
        return self::firstOrCreate(
            ['user_one_id' => $ids[0], 'user_two_id' => $ids[1]]
        );
    }
}
