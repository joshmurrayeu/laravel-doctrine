<?php

declare(strict_types=1);

namespace LaravelDoctrine\Repositories\Abstracts;

use Doctrine\ORM\EntityRepository;
use LaravelDoctrine\Entities\Abstracts\Entity;

/**
 * @template T of \LaravelDoctrine\Entities\Abstracts\Entity
 */
abstract class Repository extends EntityRepository
{
    /**
     * @return Entity
     */
    public function fetchAll(int $page, int $rows): array
    {
        $start = ($page - 1) * $rows;

        return $this->createQueryBuilder('e')
            ->setMaxResults($rows)
            ->setFirstResult($start)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getCount(): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param Entity $entity
     *
     * @return Entity
     */
    public function store(Entity $entity, bool $flush = true): Entity
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($entity);

        if ($flush === true) {
            $entityManager->flush();
        }

        return $entity;
    }

    /**
     * @param Entity $entity
     */
    public function delete(
        \LaravelDoctrine\Entities\Abstracts\Entity $entity,
        bool $flush = true,
    ): void {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($entity);

        if ($flush === true) {
            $entityManager->flush();
        }
    }

    public function flush(): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->flush();
    }
}
