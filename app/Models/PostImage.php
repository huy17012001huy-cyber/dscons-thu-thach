<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImage extends Model
{
    protected $fillable = ['post_id', 'path', 'order_index'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
