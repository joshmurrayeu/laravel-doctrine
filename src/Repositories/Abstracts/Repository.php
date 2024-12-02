<?php

declare(strict_types=1);

namespace LaravelDoctrine\Repositories\Abstracts;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use LaravelDoctrine\Entities\Abstracts\Entity;
use LaravelDoctrine\Filters\SoftDeleteFilter;

/**
 * @template T of Entity
 */
abstract class Repository extends EntityRepository
{
    /**
     * @return T
     */
    public function fetchOne(int $id, bool $disableSoftDeleteFilter = false): Entity
    {
        $entityManager = $this->getEntityManager();

        $filters = $entityManager->getFilters();
        $isSoftDeletesEnabled = $filters->isEnabled(SoftDeleteFilter::NAME);

        if ($isSoftDeletesEnabled && $disableSoftDeleteFilter) {
            $filters->disable(SoftDeleteFilter::NAME);
        }

        $result = $this->find($id);

        if ($isSoftDeletesEnabled && $disableSoftDeleteFilter) {
            $filters->enable(SoftDeleteFilter::NAME);
        }

        return $result;
    }

    /**
     * @return T[]
     */
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

        $query->andWhere($part)
            ->setParameter($low, $value[0])
            ->setParameter($high, $value[1]);
    }

    protected function applyFilters(QueryBuilder $query, array $filters): void
    {
        $expr = $query->expr();

        foreach ($filters as $field => $value) {
            $aliases = $query->getAllAliases();
            $alias = $aliases[0];

            // Did the filter contain an alias?
            if (str_contains($field, '.')) {
                // Yes, we have an alias... possible.. maybe...
                $fieldParts = explode('.', $field);

                if (in_array($fieldParts[0], $aliases)) {
                    $alias = $fieldParts[0];
                    $field = end($fieldParts);
                }
            }

            $qualifiedName = "{$alias}.{$field}";
            $parameterName = ":{$field}Values";

            // By default, we just want to use an equals check.
            $part = $expr->eq($qualifiedName, $parameterName);

            if (is_string($value)) {
                // If $value is a string with commas, explode the string to an array AND run a SQL `e.field IN ()`.
                if (str_contains($value, ',')) {
                    $value = explode(',', $value);
                    $part = $expr->in($qualifiedName, $parameterName);

                    $query->andWhere($part)
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
            }

            $query->andWhere($part)
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

    public function getCount(array $filters = []): int
    {
        $query = $this->createQueryBuilder('e')
            ->select('COUNT(e)');

        if (!empty($filters)) {
            $this->applyFilters($query, $filters);
        }

        return $query->getQuery()
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
