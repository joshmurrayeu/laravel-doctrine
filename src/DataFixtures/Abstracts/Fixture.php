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
            $this->preHandle();
            $this->handle();
        } catch (ConstraintNotMetException $exception) {
            return;
        }
    }

    protected function preHandle(): void
    {
    }

    abstract protected function handle(): void;

    public function exitUnlessOnLocal(): void
    {
        if (!$this->onlyRunOnLocal()) {
            throw new WrongEnvironmentException();
        }
    }

    public function onlyRunOnLocal(): bool
    {
        return $this->environment('local', 'dev');
    }

    public function environment(string ...$environments): bool
    {
        return in_array(env('APP_ENV'), $environments);
    }
}
