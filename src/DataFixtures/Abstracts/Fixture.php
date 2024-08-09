<?php

declare(strict_types=1);

namespace LaravelDoctrine\DataFixtures\Abstracts;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use LaravelDoctrine\Exceptions\DataFixtures\Abstracts\ConstraintNotMetException;
use LaravelDoctrine\Exceptions\DataFixtures\WrongEnvironmentException;

abstract class Fixture extends AbstractFixture implements OrderedFixtureInterface
{
    protected Generator $faker;
    protected ObjectManager $manager;

    public function __construct(Factory $factory)
    {
        $this->faker = $factory::create('en_GB');
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        try {
            $this->handle();
        } catch (ConstraintNotMetException $exception) {
            return;
        }
    }

    abstract protected function handle(): void;

    public function onlyRunOnLocal(): void
    {
        if (!in_array(env('APP_ENV'), ['local', 'dev'])) {
            throw new WrongEnvironmentException();
        }
    }
}
