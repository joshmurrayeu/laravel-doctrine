<?php

declare(strict_types=1);

namespace LaravelDoctrine\Entities\Concerns;

use DateTimeInterface;

interface HasTimestamps
{
    public function getCreatedAt(): DateTimeInterface;

    public function setCreatedAt(DateTimeInterface $carbon): static;

    public function getUpdatedAt(): DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $carbon): static;
}
