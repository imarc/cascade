<?php

declare(strict_types=1);

namespace Imarc\Cascade;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

trait CascadesSoftDeletes
{
    public static function bootCascadesSoftDeletes(): void
    {
        static::deleting(function (Model $model): void {
            if ($model->isForceDeleting()) {
                /** @var self $model */
                $model->cascadeForceDeleteToRelations();

                return;
            }

            /** @var self $model */
            $model->cascadeSoftDeleteToRelations();
        });

        static::restored(function (Model $model): void {
            /** @var self $model */
            $model->cascadeRestoreToRelations();
        });
    }

    /**
     * Relationship names (methods on this model) to cascade.
     *
     * @return list<string>
     */
    protected function getCascadeSoftDeleteRelations(): array
    {
        if (! property_exists($this, 'cascadeSoftDeletes')) {
            return [];
        }

        /** @var list<string> */
        return $this->cascadeSoftDeletes;
    }

    protected function cascadeSoftDeleteToRelations(): void
    {
        foreach ($this->getCascadeSoftDeleteRelations() as $name) {
            $relation = $this->resolveCascadeRelation($name);
            if ($relation === null) {
                continue;
            }

            $relation->each(function (Model $related): void {
                if ($this->modelUsesSoftDeletes($related)) {
                    $related->delete();
                }
            });
        }
    }

    protected function cascadeForceDeleteToRelations(): void
    {
        foreach ($this->getCascadeSoftDeleteRelations() as $name) {
            $relation = $this->resolveCascadeRelation($name);
            if ($relation === null) {
                continue;
            }

            $relation->each(function (Model $related): void {
                if ($this->modelUsesSoftDeletes($related)) {
                    $related->forceDelete();
                }
            });
        }
    }

    protected function cascadeRestoreToRelations(): void
    {
        foreach ($this->getCascadeSoftDeleteRelations() as $name) {
            $relation = $this->resolveCascadeRelation($name);
            if ($relation === null) {
                continue;
            }

            if (! $this->modelUsesSoftDeletes($relation->getRelated())) {
                continue;
            }

            $relation->onlyTrashed()->each(function (Model $related): void {
                $related->restore();
            });
        }
    }

    private function resolveCascadeRelation(string $name): ?Relation
    {
        if (! method_exists($this, $name)) {
            return null;
        }

        $relation = $this->{$name}();

        if (! $relation instanceof Relation || ! $this->isSupportedCascadeRelation($relation)) {
            return null;
        }

        return $relation;
    }

    private function isSupportedCascadeRelation(Relation $relation): bool
    {
        return $relation instanceof HasMany
            || $relation instanceof HasOne
            || $relation instanceof MorphMany
            || $relation instanceof MorphOne;
    }

    private function modelUsesSoftDeletes(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }
}
