<?php

use App\Entity\Gym;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GymTest extends TestCase
{
    private function getGym(): Gym
    {
        return (new Gym())
            ->setName('example');
    }

    private function getValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testNameisValid(): void
    {
        $gym = $this->getGym();
        $validator = $this->getValidator();
        $errors = $validator->validate($gym);

        $this->assertCount(0, $errors);
    }

    public function testNameisNotValid(): void
    {
        $gym = $this->getGym();
        $gym->setName('');
        $validator = $this->getValidator();
        $errors = $validator->validate($gym);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testNameisNotValid2(): void
    {
        $gym = $this->getGym();
        $gym->setName('ex');
        $validator = $this->getValidator();
        $errors = $validator->validate($gym);

        $this->assertGreaterThan(0, count($errors));
    }
}