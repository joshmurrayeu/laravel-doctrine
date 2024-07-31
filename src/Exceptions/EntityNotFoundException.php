<?php

declare(strict_types=1);

namespace LaravelDoctrine\Exceptions;

use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Arr;

use function count;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class EntityNotFoundException extends RecordsNotFoundException
{
    /**
     * @var class-string<TModel>
     */
    protected $entity;

    /**
     * @var array<int, int|string>
     */
    protected $ids;

    /**
     * Set the affected Eloquent model and instance ids.
     *
     * @param class-string<TModel>              $entity
     * @param int|string|array<int, int|string> $ids
     *
     * @return $this
     */
    public function setEntity(string $entity, array|int|string $ids = []): static
    {
        $this->entity = $entity;
        $this->ids = Arr::wrap($ids);

        $this->message = "No query results for entity [{$entity}]";

        if (count($this->ids) > 0) {
            $this->message .= ' ' . implode(', ', $this->ids);
        } else {
            $this->message .= '.';
        }

        return $this;
    }

    /**
     * Get the affected Eloquent model.
     *
     * @return class-string<TModel>
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the affected Eloquent model IDs.
     *
     * @return array<int, int|string>
     */
    public function getIds()
    {
        return $this->ids;
    }
}
