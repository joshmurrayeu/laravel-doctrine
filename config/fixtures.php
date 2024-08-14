<?php

declare(strict_types=1);

return [
    'enabled' => in_array(config('app.env'), ['local', 'testing', 'staging']),
];
