<?php

declare(strict_types=1);

namespace LaravelDoctrine\Loaders;

use Doctrine\Migrations\Configuration\EntityManager\EntityManagerLoader as EntityManagerLoaderContract;
use Doctrine\ORM\EntityManagerInterface;

class EntityManagerLoader implements EntityManagerLoaderContract
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    public function getEntityManager(?string $name = null): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
