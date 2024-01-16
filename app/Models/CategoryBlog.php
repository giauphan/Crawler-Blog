<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryBlog extends Model
{
    protected $guarded = [];
    public function post(): BelongsTo
    {
        return $this->belongsTo(CategoryBlog::class);
    }
}
