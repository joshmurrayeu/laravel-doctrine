<?php

declare(strict_types=1);

namespace LaravelDoctrine\Entities\Concerns;

use DateTimeInterface;

interface CanBeSoftDeleted
{
    public function getDeletedAt(): ?DateTimeInterface;

    public function setDeletedAt(?DateTimeInterface $carbon): static;
}
