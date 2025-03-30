<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    use Trait\EntityTestTrait;

    public function testAddressIsNotBlankAndLengthValid()
    {
        $address = $this->getAddress();
        $address->setAddress('');
        
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'Address should not be blank');

        $address->setAddress(str_repeat('A', 256)); // Too long
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'Address should not exceed 255 characters');
    }

    public function testPostalCodeIsValid()
    {
        $address = $this->getAddress();
        
        // Invalid postal code
        $address->setPostalCode('123456');
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'Postal code should be valid');
        
        // Valid postal code
        $address->setPostalCode('69530');
        $violations = $this->getValidator()->validate($address);
        $this->assertEquals(0, count($violations), 'Postal code should be valid and respect the format');
    }

    public function testCityIsNotBlankAndLengthValid()
    {
        $address = $this->getAddress();
        $address->setCity('');
        
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'City should not be blank');
        
        $address->setCity(str_repeat('A', 256)); // Too long
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'City should not exceed 255 characters');
    }

    public function testCountryIsNotBlankAndLengthValid()
    {
        $address = $this->getAddress();
        $address->setCountry('');
        
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'Country should not be blank');
        
        $address->setCountry(str_repeat('A', 256)); // Too long
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'Country should not exceed 255 characters');
    }

    public function testLatitudeIsValid()
    {
        $address = $this->getAddress();
        
        // Invalid latitude
        $address->setLatitude(100);
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'Latitude should be between -90 and 90');
        
        // Valid latitude
        $address->setLatitude(42.1234567);
        $violations = $this->getValidator()->validate($address);
        $this->assertEquals(0, count($violations), 'Latitude should be a valid number between -90 and 90');
    }

    public function testLongitudeIsValid()
    {
        $address = $this->getAddress();
        
        // Invalid longitude
        $address->setLongitude(200);
        $violations = $this->getValidator()->validate($address);
        $this->assertGreaterThan(0, count($violations), 'Longitude should be between -180 and 180');
        
        // Valid longitude
        $address->setLongitude(-42.1234567);
        $violations = $this->getValidator()->validate($address);
        $this->assertEquals(0, count($violations), 'Longitude should be a valid number between -180 and 180');
    }
}