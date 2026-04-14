<?php

declare(strict_types=1);

namespace Imarc\Cascade\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Imarc\Cascade\CascadeSoftDeletes;

class Category extends Model
{
    use CascadeSoftDeletes, SoftDeletes;

    protected $guarded = [];

    /** @var list<string> */
    protected array $cascadeSoftDeletes = ['posts'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
