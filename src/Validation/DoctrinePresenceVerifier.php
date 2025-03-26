<?php

declare(strict_types=1);

namespace LaravelDoctrine\Validation;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
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
        $filters = $this->entityManager->getFilters();
        $isSoftDeletesEnabled = $filters->isEnabled(SoftDeleteFilter::NAME);

        $includesDeletedAt = in_array('deletedAt', array_keys($extra));

        foreach ($extra as $column => $value) {
            $increment++;

            if ($value instanceof \Closure) {
                // horrible workaround for exists
                $connection = new Connection($query->getEntityManager()->getConnection()->getNativeConnection());
                $builder = new Builder($connection);

                $value($builder);

                foreach ($builder->wheres as $key => $where) {
                    if ($where['type'] !== 'In') {
                        throw new \Exception('Unsure how to handle.');
                    }

                    $query->andWhere(
                        $expr->in("e.{$where['column']}", ":inValues{$key}")
                    )->setParameter("inValues{$key}", $where['values']);
                }

                continue;
            }

            $qualifiedColumn = "e.{$column}";
            $operator = '=';
            $parameterName = ":value{$increment}";

            if ($column === 'deletedAt') {
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

        if ($isSoftDeletesEnabled && $includesDeletedAt) {
            $this->entityManager->getFilters()->disable(SoftDeleteFilter::NAME);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        if ($isSoftDeletesEnabled && $includesDeletedAt) {
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
