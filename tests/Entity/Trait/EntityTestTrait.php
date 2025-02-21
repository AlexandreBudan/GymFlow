<?php

namespace App\Tests\Entity\Trait;

use App\Entity\Address;
use App\Entity\CommentsExercise;
use App\Entity\Exercise;
use App\Entity\Gym;
use App\Entity\User;
use App\Entity\Zone;
use App\Entity\UserDetail;
use App\Entity\VideosExercise;
use Lcobucci\JWT\Validation\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait EntityTestTrait
{
    // Fonction pour obtenir un validateur
    public function getValidator() : ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    // Fonction pour obtenir un validateur pour les champ UniqueEntity
    protected function getUniqueEntityValidator(): ValidatorInterface
    {
        self::bootKernel();
        return static::getContainer()->get(ValidatorInterface::class);
    }

    // Fonction pour obtenir une entité User avec des valeurs de base
    public function getUser(): User
    {
        $user = new User();
        $user->setEmail('test_email_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user->setUserDetail($this->getUserDetail());
        return $user;
    }

    // Fonction pour obtenir une entité Address avec des valeurs de base
    public function getAddress(): Address
    {
        $address = new Address();
        $address->setAddress('Rue de la Paix');
        $address->setCity('Paris');
        $address->setPostalCode('69530');
        $address->setCountry('France');
        $address->setLatitude(4.1234567);
        $address->setLongitude(2.1234567);
        $address->setGym($this->getGym());
        return $address;
    }

    // Fonction pour obtenir une entité UserDetail avec des valeurs de base
    public function getUserDetail(): UserDetail
    {
        $userDetail = new UserDetail();
        $userDetail->setPseudo('test_pseudo_' . uniqid());
        return $userDetail;
    }

    // Fonction pour obtenir une entité Zone avec des valeurs de base
    public function getZone(): Zone
    {
        $zone = new Zone();
        $zone->setName('Zone test ' . uniqid());
        $zone->setGym($this->getGym());
        return $zone;
    }

    // Fonction pour obtenir une entité Exercise avec des valeurs de base
    public function getExercise(): Exercise
    {
        $exercise = new Exercise();
        $exercise->setName('Exercise test ' . uniqid());
        $exercise->setDescription('Description de l\'exercice');
        $exercise->setZone($this->getZone());
        return $exercise;
    }

    // Fonction pour obtenir une entité VideosExercise avec des valeurs de base
    public function getVideosExercise(): VideosExercise
    {
        $videosExercise = new VideosExercise();
        $videosExercise->setTitle('Titre de vidéo ' . uniqid());
        $videosExercise->setUrl('https://www.example.com/video.mp4');
        $videosExercise->setDescription('Description de la vidéo');
        $videosExercise->setCreator($this->getUserDetail());
        $videosExercise->setExercise($this->getExercise());
        return $videosExercise;
    }

    // Fonction pour obtenir une entité CommentsExercise avec des valeurs de base
    public function getCommentsExercise(): CommentsExercise
    {
        $commentsExercise = new CommentsExercise();
        $commentsExercise->setComment('Contenu du commentaire');
        $commentsExercise->setCreator($this->getUserDetail());
        $commentsExercise->setExercise($this->getExercise());
        return $commentsExercise;
    }

    // Fonction pour obtenir une Gym (ajoutez des données fictives pour les tests)
    public function getGym()
    {
        $gym = new Gym();
        $gym->setName('Gym Test');
        return $gym;
    }
}
