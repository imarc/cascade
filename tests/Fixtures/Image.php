<?php

declare(strict_types=1);

namespace Imarc\Cascade\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
