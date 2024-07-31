<?php

declare(strict_types=1);

namespace LaravelDoctrine\NamingStrategies;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Illuminate\Support\Str;

use const CASE_LOWER;

class UnderscorePluralNamingStrategy extends UnderscoreNamingStrategy
{
    public function __construct(protected Str $str, int $case = CASE_LOWER)
    {
        parent::__construct($case);
    }

    public function classToTableName(string $className): string
    {
        $tableName = parent::classToTableName($className);

        $parts = explode('_', $tableName);
        $end = array_pop($parts);

        $parts[] = $this->str->plural($end);

        return implode('_', $parts);
    }
}
