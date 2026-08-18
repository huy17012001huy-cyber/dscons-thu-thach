<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PowerSymbol extends Model {
    protected $fillable = ['user_id','pillar','level','fragments'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function getEmoji(): string {
        return match($this->pillar) {
            'offer'=>'🔥','traffic'=>'✨','conversion'=>'🎯','delivery'=>'⚙️','continuity'=>'🔗',default=>'●',
        };
    }
    public function fragmentsForNextLevel(): int {
        $l = $this->level + 1;
        return $l * 10 + ($l - 1) * 15;
    }
}
