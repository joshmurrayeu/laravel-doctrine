<?php

declare(strict_types=1);

namespace LaravelDoctrine\Entities\Traits;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Entities\Concerns\HasTimestamps;

/**
 * @implements \LaravelDoctrine\Entities\Concerns\HasTimestamps
 */
trait SoftDeletes
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $deletedAt = null;

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function isSoftDeleted(): bool
    {
        return $this->getDeletedAt() !== null;
    }
}
