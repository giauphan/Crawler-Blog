<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $guarded = [];

    public function CategoryBlog(): BelongsTo
    {
        return $this->belongsTo(CategoryBlog::class);
    }
}
