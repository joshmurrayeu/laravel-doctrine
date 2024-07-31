<?php

declare(strict_types=1);

namespace LaravelDoctrine\Entities\Abstracts;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class Entity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
