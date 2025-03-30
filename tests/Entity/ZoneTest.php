<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;

class ZoneTest extends TestCase
{
    use Trait\EntityTestTrait;

    public function testNameIsValid()
    {
        $zone = $this->getZone();
        $zone->setName('');

        $violations = $this->getValidator()->validate($zone);
        $this->assertGreaterThan(0, count($violations), 'Zone name should not be blank');

        $zone->setName('T');
        $violations = $this->getValidator()->validate($zone);
        $this->assertGreaterThan(0, count($violations), 'Zone name should be at least 3 characters long');

        $zone->setName('Valid Zone');
        $violations = $this->getValidator()->validate($zone);
        $this->assertEquals(0, count($violations), 'Zone name should be valid');
    }

    public function testGymRelation()
    {
        $zone = $this->getZone();
        $gym = $this->getGym();

        $zone->setGym($gym);
        $violations = $this->getValidator()->validate($zone);
        $this->assertEquals(0, count($violations), 'Gym relation should be set correctly');
        $this->assertEquals($gym, $zone->getGym());
    }

    public function testExercisesRelation()
    {
        $zone = $this->getZone();
        $exercise = $this->getExercise();

        $zone->addExercise($exercise);
        $violations = $this->getValidator()->validate($zone);
        $this->assertEquals(0, count($violations), 'Exercise relation should be set correctly');
        $this->assertTrue($zone->getExercises()->contains($exercise));
    }

    public function testAddAndRemoveExercise()
    {
        $zone = $this->getZone();
        $exercise = $this->getExercise();

        // Test add exercise
        $zone->addExercise($exercise);
        $this->assertTrue($zone->getExercises()->contains($exercise));

        // Test remove exercise
        $zone->removeExercise($exercise);
        $this->assertFalse($zone->getExercises()->contains($exercise));
    }

    public function testSetAndGetMethods()
    {
        $zone = $this->getZone();

        // Test Name
        $zone->setName('Test Zone');
        $this->assertEquals('Test Zone', $zone->getName());

        // Test Gym
        $gym = $this->getGym();
        $zone->setGym($gym);
        $this->assertEquals($gym, $zone->getGym());
    }
}
