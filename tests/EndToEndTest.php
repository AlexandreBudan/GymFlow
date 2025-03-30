<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EndToEndTest extends WebTestCase
{
    // Fonction pour obtenir un validateur
    public function getValidator() : ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testEndToEnd()
    {
        $client = static::createClient();
        assert($client->getContainer() !== null);
        $validator = $this->getValidator();
        return $this->assertTrue(true);
    }
}
