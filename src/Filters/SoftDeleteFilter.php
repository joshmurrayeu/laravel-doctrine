<?php

declare(strict_types=1);

namespace LaravelDoctrine\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use LaravelDoctrine\Entities\Concerns\CanBeSoftDeleted;
use LaravelDoctrine\Filters\Abstracts\SQLFilter;

class SoftDeleteFilter extends SQLFilter
{
    public const NAME = 'soft-delete';

    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        // Check if the entity implements the LocalAware interface
        if (!$targetEntity->reflClass->implementsInterface(CanBeSoftDeleted::class)) {
            return '';
        }

        return $this->getExpressionBuilder()->isNull("{$targetTableAlias}.deleted_at");
    }
}
