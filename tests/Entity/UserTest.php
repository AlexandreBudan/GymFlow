<?php

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends TestCase
{
    private function getValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testEmailIsValid(): void
    {
        $user = new User();
        $user->setEmail("test@example.com");

        $validator = $this->getValidator();
        $errors = $validator->validate($user);

        $this->assertCount(0, $errors);
    }

    public function testInvalidEmail(): void
    {
        $user = new User();
        $user->setEmail("invalid-email");

        $validator = $this->getValidator();
        $errors = $validator->validate($user);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testRolesDefaultToUser(): void
    {
        $user = new User();
        $this->assertContains("ROLE_USER", $user->getRoles());
    }

    public function testRolesCanBeSet(): void
    {
        $user = new User();
        $user->setRoles(["ROLE_ADMIN"]);
        $this->assertContains("ROLE_ADMIN", $user->getRoles());
    }

    public function testPassword(): void
    {
        $user = new User();
        $user->setPassword("password");
        $this->assertEquals("password", $user->getPassword());
    }

    public function testUserDetail(): void
    {
        $user = new User();
        $this->assertNull($user->getUserDetail());
    }
}
