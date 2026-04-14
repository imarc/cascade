# Cascade

Cascading soft deletes for Laravel Eloquent. When a parent model is soft-deleted, related models are soft-deleted. When the parent is restored, **all** currently trashed rows for those relations are restored one-by-one so `restored` events run (nested cascades work). This still restores every trashed child for that foreign key, not only rows deleted in the same cascade as the parent.

## Usage

Use Laravel’s `SoftDeletes` and this package’s `CascadesSoftDeletes` on the parent. List relationship method names to cascade:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Imarc\Cascade\CascadesSoftDeletes;

class Post extends Model
{
    use CascadesSoftDeletes, SoftDeletes;

    /** @var list<string> */
    protected array $cascadeSoftDeletes = ['comments'];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

Child models should use `SoftDeletes` if you want them included; non–soft-delete children are skipped on cascade delete.

## Supported relations

`HasMany`, `HasOne`, `MorphMany`, and `MorphOne` are supported. `BelongsTo`, `BelongsToMany`, and other relation types are ignored for cascading.

## Force delete

When the parent is force-deleted, soft-deletable children in the listed relations are force-deleted so orphaned soft-deleted rows are not left behind.

## Install

```bash
composer require imarc/cascade
```

## Tests (from application root)

```bash
php artisan test --testsuite=Cascade
```

Or:

```bash
./vendor/bin/phpunit -c packages/imarc/cascade/phpunit.xml
```
