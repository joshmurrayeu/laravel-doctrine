<?php

declare(strict_types=1);

namespace LaravelDoctrine\Repositories\Abstracts;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use LaravelDoctrine\Entities\Abstracts\Entity;

/**
 * @template T of Entity
 */
abstract class Repository extends EntityRepository
{
    public function fetchAll(
        int $page,
        int $rows,
        array $filters = [],
        array $sorts = [],
    ): array {
        $start = ($page - 1) * $rows;

        $query = $this->createQueryBuilder('e')
            ->setMaxResults($rows)
            ->setFirstResult($start);

        if (!empty($filters)) {
            $this->applyFilters($query, $filters);
        }

        if (!empty($sorts)) {
            $this->applySorts($query, $sorts);
        }

        return $query->getQuery()
            ->getResult();
    }

    protected function betweenQueryPart(
        string $operator,
        Expr $expr,
        string $qualifiedName,
        string $parameterName,
        mixed $value,
        QueryBuilder $query,
    ): void {
        $value = explode($operator, $value);

        $low = "{$parameterName}Low";
        $high = "{$parameterName}High";

        $part = $expr->between($qualifiedName, $low, $high);

        $query->where($part)
            ->setParameter($low, $value[0])
            ->setParameter($high, $value[1]);
    }

    protected function applyFilters(QueryBuilder $query, array $filters): void
    {
        $expr = $query->expr();

        foreach ($filters as $field => $value) {
            $qualifiedName = "e.{$field}";
            $parameterName = ":{$field}Values";

            // By default, we just want to use an equals check.
            $part = $expr->eq($qualifiedName, $parameterName);

            // If $value is a string with commas, explode the string to an array AND run a SQL `e.field IN ()`.
            if (str_contains($value, ',')) {
                $value = explode(',', $value);
                $part = $expr->in($qualifiedName, $parameterName);

                $query->where($part)
                    ->setParameter($parameterName, $value);

                continue;
            }

            if (str_contains($value, '>')) {
                $this->betweenQueryPart('>', $expr, $qualifiedName, $parameterName, $value, $query);

                continue;
            }

            if (str_contains($value, '<')) {
                $this->betweenQueryPart('<', $expr, $qualifiedName, $parameterName, $value, $query);

                continue;
            }

            $query->where($part)
                ->setParameter($parameterName, $value);
        }
    }

    protected function applySorts(QueryBuilder $query, array $sorts): void
    {
        $tableAlias = $query->getRootAliases()[0];

        foreach ($sorts as $field) {
            $tempField = ltrim($field, '-');

            if ($tempField !== $field) {
                $query->addOrderBy("{$tableAlias}.{$tempField}", 'DESC');
                continue;
            }

            $query->addOrderBy("{$tableAlias}.$field", 'ASC');
        }
    }

    public function getCount(): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param T $entity
     *
     * @return T
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
     * @param T $entity
     */
    public function delete(
        Entity $entity,
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
