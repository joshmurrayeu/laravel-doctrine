<?php

declare(strict_types=1);

namespace LaravelDoctrine\Validation;

use App\Miscellaneous\Exceptions\NotYetImplementedException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Validation\DatabasePresenceVerifierInterface;
use LaravelDoctrine\Repositories\Abstracts\Repository;

class DoctrinePresenceVerifier implements DatabasePresenceVerifierInterface
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = []): int
    {
        /** @var Repository $repository */
        $repository = $this->entityManager->getRepository($collection);
        $builder = $repository->createQueryBuilder('e');

        $query = $builder
            ->select('COUNT(e.id) as count')
            ->where("e.{$column} = :value")
            ->setParameter('value', $value)
        ;

        if (!empty($excludeId)) {
            $query = $query->andWhere('e.id != :id')
                ->setParameter('id', $excludeId)
            ;
        }

        return $query->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getMultiCount($collection, $column, array $values, array $extra = [])
    {
        throw new NotYetImplementedException();
    }

    public function setConnection($connection): void
    {
    }
}
