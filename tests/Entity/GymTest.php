<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;

class GymTest extends TestCase
{
    use Trait\EntityTestTrait;

    public function testNameIsNotBlankAndLengthValid()
    {
        $gym = $this->getGym();
        $gym->setName('');

        $violations = $this->getValidator()->validate($gym);
        $this->assertGreaterThan(0, count($violations), 'Name should not be blank');

        $gym->setName(str_repeat('A', 256)); // Too long
        $violations = $this->getValidator()->validate($gym);
        $this->assertGreaterThan(0, count($violations), 'Name should not exceed 255 characters');

        $gym->setName('Valid Gym Name');
        $violations = $this->getValidator()->validate($gym);
        $this->assertEquals(0, count($violations), 'Name should be valid');
    }

    public function testAddressIsValid()
    {
        $gym = $this->getGym();
        $address = $this->getAddress();
        $gym->setAddress($address);

        $violations = $this->getValidator()->validate($gym);
        $this->assertEquals(0, count($violations), 'Address should be valid');
    }

    public function testAddZone()
    {
        $gym = $this->getGym();
        $zone = $this->getZone();
        $gym->addZone($zone);

        $violations = $this->getValidator()->validate($gym);
        $this->assertEquals(0, count($violations), 'Gym should be associated with zone correctly');
    }

    public function testRemoveZone()
    {
        $gym = $this->getGym();
        $zone = $this->getZone();
        $gym->addZone($zone);

        $gym->removeZone($zone);

        $violations = $this->getValidator()->validate($gym);
        $this->assertEquals(0, count($violations), 'Gym should have zone removed correctly');
    }

    public function testAddUser()
    {
        $gym = $this->getGym();
        $user = $this->getUserDetail();
        $gym->addUser($user);

        $violations = $this->getValidator()->validate($gym);
        $this->assertEquals(0, count($violations), 'Gym should be associated with user correctly');
    }

    public function testRemoveUser()
    {
        $gym = $this->getGym();
        $user = $this->getUserDetail();
        $gym->addUser($user);

        $gym->removeUser($user);

        $violations = $this->getValidator()->validate($gym);
        $this->assertEquals(0, count($violations), 'Gym should have user removed correctly');
    }
}
