<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasBrand;

    protected $fillable = ['user_one_id', 'user_two_id', 'last_message_at', 'brand_id', 'conversation_type', 'contact_request_id'];

    protected $casts = ['last_message_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    /** @return BelongsTo<User, $this> */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    /** @return HasMany<DirectMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(DirectMessage::class);
    }

    /** @return HasOne<DirectMessage, $this> */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(DirectMessage::class)->latestOfMany();
    }

    /** @return BelongsTo<RecruitmentContactRequest, $this> */
    public function contactRequest(): BelongsTo
    {
        return $this->belongsTo(RecruitmentContactRequest::class, 'contact_request_id');
    }

    public function getOtherUser(int $myId): User
    {
        $otherUser = $this->user_one_id === $myId ? $this->userTwo : $this->userOne;
        abort_unless($otherUser instanceof User, 404);

        return $otherUser;
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
