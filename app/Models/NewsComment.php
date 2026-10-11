<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_article_id',
        'character_id',
        'content',
    ];

    /**
     * Get the article that this comment belongs to.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(NewsArticle::class, 'news_article_id');
    }

    /**
     * Get the character who posted this comment.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
