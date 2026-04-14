<?php

declare(strict_types=1);

namespace Imarc\Cascade\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Imarc\Cascade\CascadesSoftDeletes;

class Post extends Model
{
    use CascadesSoftDeletes, SoftDeletes;

    protected $guarded = [];

    /** @var list<string> */
    protected array $cascadeSoftDeletes = ['comments', 'hardComments', 'images', 'tags'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function hardComments(): HasMany
    {
        return $this->hasMany(HardComment::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
