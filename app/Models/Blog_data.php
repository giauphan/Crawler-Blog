<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog_data extends Model
{
    use HasFactory;
    protected $table = 'BlogData';
    protected $fillable = [
        'title',
        'content',
        'source',
        'SimilarityPercentage',
    ];
}
