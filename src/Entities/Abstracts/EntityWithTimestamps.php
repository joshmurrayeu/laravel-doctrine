<?php

declare(strict_types=1);

namespace LaravelDoctrine\Entities\Abstracts;

use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Entities\Concerns\HasTimestamps;
use LaravelDoctrine\Entities\Traits\Timestamps;

#[ORM\MappedSuperclass]
abstract class EntityWithTimestamps extends Entity implements HasTimestamps
{
    use Timestamps;
}
