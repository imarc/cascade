<?php

declare(strict_types=1);

namespace Imarc\Cascade\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HardComment extends Model
{
    protected $guarded = [];

    protected $table = 'hard_comments';

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
