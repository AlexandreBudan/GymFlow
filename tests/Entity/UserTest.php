<?php

namespace App\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    use Trait\EntityTestTrait;

    public function testEmailIsNotBlankAndValid()
    {
        $user = $this->getUser();
        $user->setEmail('');

        $violations = $this->getUniqueEntityValidator()->validate($user);
        $this->assertGreaterThan(0, count($violations), 'Email should not be blank');

        $user->setEmail('invalid-email');
        $violations = $this->getUniqueEntityValidator()->validate($user);
        $this->assertGreaterThan(0, count($violations), 'Email should be valid');

        $user->setEmail('valid@example.com');
        $violations = $this->getUniqueEntityValidator()->validate($user);
        $this->assertEquals(0, count($violations), 'Email should be valid');
    }

    public function testPasswordIsNotBlankAndLengthValid()
    {
        $user = $this->getUser();
        $user->setPassword('');

        $violations = $this->getUniqueEntityValidator()->validate($user);
        $this->assertGreaterThan(0, count($violations), 'Password should not be blank');

        $user->setPassword('short');
        $violations = $this->getUniqueEntityValidator()->validate($user);
        $this->assertGreaterThan(0, count($violations), 'Password should be at least 8 characters long');

        $user->setPassword('validpassword');
        $violations = $this->getUniqueEntityValidator()->validate($user);
        $this->assertEquals(0, count($violations), 'Password should be valid');
    }

    public function testGetRoles()
    {
        $user = $this->getUser();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles); // ROLE_USER should always be included
        $this->assertContains('ROLE_ADMIN', $roles);

        $user->setRoles([]);
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles); // ROLE_USER should always be included
        $this->assertCount(1, $roles);
    }

    public function testSetUserDetail()
    {
        $user = $this->getUser();
        $userDetail = $this->getUserDetail();
        $user->setUserDetail($userDetail);

        $violations = $this->getUniqueEntityValidator()->validate($user);
        $this->assertEquals(0, count($violations), 'User should be associated with UserDetail correctly');
    }

    public function testGetUserIdentifier()
    {
        $user = $this->getUser();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }

    public function testPseudoIsNotBlankAndValid()
    {
        $userDetail = $this->getUserDetail();
        $userDetail->setPseudo('');

        $violations = $this->getUniqueEntityValidator()->validate($userDetail);
        $this->assertGreaterThan(0, count($violations), 'Pseudo should not be blank');

        $userDetail->setPseudo('a');
        $violations = $this->getUniqueEntityValidator()->validate($userDetail);
        $this->assertGreaterThan(0, count($violations), 'Pseudo should be at least 3 characters long');

        $userDetail->setPseudo('validpseudo');
        $violations = $this->getUniqueEntityValidator()->validate($userDetail);
        $this->assertEquals(0, count($violations), 'Pseudo should be valid');
    }

    public function testUniquePseudoConstraint()
    {
        $user1 = $this->getUserDetail()->setPseudo('TestUser' . uniqid());
        $user2 = $this->getUserDetail()->setPseudo($user1->getPseudo());

        $user1->initializeTimestamps();
        $user2->initializeTimestamps();

        self::bootKernel();
        $entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user1);
        $entityManager->flush();

        // On valide le second utilisateur qui a le même pseudo
        $violations = $this->getUniqueEntityValidator()->validate($user2);
        $this->assertGreaterThan(0, count($violations), 'Pseudo should be unique');
    }


    public function testUserAuthRelation()
    {
        $userDetail = $this->getUserDetail();
        $user = $this->getUser();
        $userDetail->setUserAuth($user);

        $violations = $this->getUniqueEntityValidator()->validate($userDetail);
        $this->assertEquals(0, count($violations), 'UserAuth relation should be set correctly');
        $this->assertEquals($userDetail, $user->getUserDetail());
    }

    public function testAddGymsFav()
    {
        $userDetail = $this->getUserDetail();
        $gym = $this->getGym();
        
        $userDetail->addGymsFav($gym);
        $this->assertTrue($userDetail->getGymsFav()->contains($gym), 'Gym should be added to user details');

        $userDetail->removeGymsFav($gym);
        $this->assertFalse($userDetail->getGymsFav()->contains($gym), 'Gym should be removed from user details');
    }

    public function testAddLikedExercise()
    {
        $userDetail = $this->getUserDetail();
        $exercise = $this->getExercise();

        $userDetail->addLikedExercise($exercise);
        $this->assertTrue($userDetail->getLikedExercises()->contains($exercise), 'Exercise should be added to user details');

        $userDetail->removeLikedExercise($exercise);
        $this->assertFalse($userDetail->getLikedExercises()->contains($exercise), 'Exercise should be removed from user details');
    }

    public function testAddVideosExercise()
    {
        $userDetail = $this->getUserDetail();
        $videosExercise = $this->getVideosExercise();
        $videosExercise->setCreator($userDetail);

        $userDetail->addVideosExercise($videosExercise);
        $this->assertTrue($userDetail->getVideosExercises()->contains($videosExercise), 'Video Exercise should be added to user details');

        $userDetail->removeVideosExercise($videosExercise);
        $this->assertFalse($userDetail->getVideosExercises()->contains($videosExercise), 'Video Exercise should be removed from user details');
    }

    public function testAddCommentsExercise()
    {
        $userDetail = $this->getUserDetail();
        $commentsExercise = $this->getCommentsExercise();
        $commentsExercise->setCreator($userDetail);

        $userDetail->addCommentsExercise($commentsExercise);
        $this->assertTrue($userDetail->getCommentsExercises()->contains($commentsExercise), 'Comment Exercise should be added to user details');

        $userDetail->removeCommentsExercise($commentsExercise);
        $this->assertFalse($userDetail->getCommentsExercises()->contains($commentsExercise), 'Comment Exercise should be removed from user details');
    }
}
