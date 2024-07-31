<?php

declare(strict_types=1);

namespace LaravelDoctrine\Filters\Abstracts;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\ORM\Query\Filter\SQLFilter as BaseSQLFilter;

abstract class SQLFilter extends BaseSQLFilter
{
    private ExpressionBuilder $expressionBuilder;

    public function getExpressionBuilder(): ExpressionBuilder
    {
        if (!isset($this->expressionBuilder)) {
            $this->expressionBuilder = new ExpressionBuilder($this->getConnection());
        }

        return $this->expressionBuilder;
    }
}
