<?php

declare(strict_types=1);

namespace LaravelDoctrine\Validation;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Validation\DatabasePresenceVerifierInterface;
use LaravelDoctrine\Filters\SoftDeleteFilter;
use LaravelDoctrine\Repositories\Abstracts\Repository;
use RuntimeException;

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
            ->setParameter('value', $value);

        if (!empty($excludeId)) {
            $query = $query->andWhere('e.id != :id')
                ->setParameter('id', $excludeId);
        }

        $expr = $query->expr();

        $increment = 0;
        $shouldDisableSoftDeleteFilter = false;

        foreach ($extra as $column => $value) {
            $increment++;

            $qualifiedColumn = "e.{$column}";
            $operator = '=';
            $parameterName = ":value{$increment}";

            if ($column === 'deletedAt') {
                $shouldDisableSoftDeleteFilter = true;

                $tempValue = strtoupper($value);

                if (str_contains($tempValue, 'IS') && str_contains($tempValue, 'NULL')) {
                    if (str_contains($tempValue, 'NOT')) {
                        $part = $expr->isNotNull($qualifiedColumn);
                    } else {
                        $part = $expr->isNull($qualifiedColumn);
                    }

                    $query->andWhere($part);

                    continue;
                }
            }

            $query->andWhere(implode(' ', [$qualifiedColumn, $operator, $parameterName]))
                ->setParameter($parameterName, $value);
        }

        if ($shouldDisableSoftDeleteFilter === true) {
            $this->entityManager->getFilters()->disable(SoftDeleteFilter::NAME);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        if ($shouldDisableSoftDeleteFilter === true) {
            $this->entityManager->getFilters()->enable(SoftDeleteFilter::NAME);
        }

        return $result;
    }

    public function getMultiCount($collection, $column, array $values, array $extra = [])
    {
        throw new RuntimeException();
    }

    public function setConnection($connection): void
    {
    }
}
