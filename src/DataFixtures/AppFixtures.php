<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\User;
use App\Entity\Gym;
use App\Entity\Zone;
use App\Entity\Exercise;
use App\Entity\CommentsExercise;
use App\Entity\UserDetail;
use App\Entity\VideosExercise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $faker;
    private $users = [];
    private $gyms = [];
    private $zones = [];
    private $exercises = [];
    private $comments = [];

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $this->loadGym($manager);
        }

        foreach ($this->gyms as $gym) {
            for ($j = 0; $j < 5; $j++) {
                $this->loadZone($manager, $gym);
            }
        }

        foreach ($this->zones as $zone) {
            for ($k = 0; $k < 3; $k++) {
                $this->loadExercise($manager, $zone);
            }
        }

        $this->loadAdminUser($manager);

        for ($i = 0; $i < 10; $i++) {
            $this->loadUser($manager);
        }

        foreach ($this->exercises as $exercise) {
            for ($v = 0; $v < 2; $v++) {
                $this->loadExerciseVideo($manager, $exercise);
            }
        }
        
        foreach ($this->exercises as $exercise) {
            for ($c = 0; $c < 5; $c++) {
                $this->loadExerciseComment($manager, $exercise);
            }
        }

        $manager->flush();
    }

    private function loadAdminUser(ObjectManager $manager)
    {
        $user = new User();
        $userDetail = new UserDetail();
        $userDetail->setPseudo('admin');
        $user->setEmail('admin');
        $user->setPassword(password_hash('admin', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN']);
        for ($i = 0; $i < random_int(1, 2); $i++) {
            $gym = $this->faker->randomElement($this->gyms);

            if (!in_array($gym, $userDetail->getGymsFav()->toArray())) {
                $userDetail->addGymsFav($gym);
            }
        }
        for ($j = 0; $j < random_int(0, 3) ; $j++) { 
            $exercise = $this->faker->randomElement($this->exercises);

            if (!in_array($exercise, $userDetail->getLikedExercises()->toArray())) {
                $userDetail->addLikedExercise($exercise);
            }
        }

        $userDetail->initializeTimestamps();
        $user->setUserDetail($userDetail);

        $manager->persist($userDetail);
        $manager->persist($user);
        $this->users[] = $user;
    }

    private function loadUser(ObjectManager $manager)
    {
        
        $user = new User();
        $userDetail = new UserDetail();
        $name = $this->faker->name();
        $userDetail->setPseudo($name);
        $user->setEmail($this->faker->unique()->safeEmail());
        $user->setPassword(password_hash($name, PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_USER']);
        for ($i = 0; $i < random_int(1, 2); $i++) {
            $gym = $this->faker->randomElement($this->gyms);

            if (!in_array($gym, $userDetail->getGymsFav()->toArray())) {
                $userDetail->addGymsFav($gym);
            }
        }
        for ($j = 0; $j < random_int(0, 3) ; $j++) { 
            $exercise = $this->faker->randomElement($this->exercises);

            if (!in_array($exercise, $userDetail->getLikedExercises()->toArray())) {
                $userDetail->addLikedExercise($exercise);
            }
        }

        $userDetail->initializeTimestamps();
        $user->setUserDetail($userDetail);

        $manager->persist($userDetail);
        $manager->persist($user);
        $this->users[] = $user;
    }

    private function loadGym(ObjectManager $manager)
    {
        $gym = new Gym();
        $gym->setName($this->faker->company());
        $gym->initializeTimestamps();

        $manager->persist($gym);
        $this->gyms[] = $gym;

        // Création de l'adresse de la salle
        $address = new Address();
        $address->setGym($gym);
        $address->setAddress($this->faker->address());
        $address->setCity($this->faker->city());
        $address->setPostalCode($this->faker->postcode());
        $address->setCountry($this->faker->country());
        $address->setLatitude($this->faker->latitude());
        $address->setLongitude($this->faker->longitude());

        $address->initializeTimestamps();

        $manager->persist($address);
    }

    private function loadZone(ObjectManager $manager, Gym $gym)
    {
        $zone = new Zone();
        $zone->setName($this->faker->word());
        $zone->setGym($gym);

        $zone->initializeTimestamps();

        $manager->persist($zone);
        $this->zones[] = $zone;
    }

    private function loadExercise(ObjectManager $manager, Zone $zone)
    {
        $exercise = new Exercise();
        $exercise->setName($this->faker->word() . " Workout");
        $exercise->setDescription($this->faker->sentence(10));
        $exercise->setZone($zone);

        $exercise->initializeTimestamps();

        $manager->persist($exercise);
        $this->exercises[] = $exercise;
    }

    private function loadExerciseVideo(ObjectManager $manager, Exercise $exercise)
    {
        $video = new VideosExercise();
        $video->setExercise($exercise);
        $video->setCreator($this->faker->randomElement($this->users));
        $video->setTitle($this->faker->sentence(3));
        $video->setUrl("https://www.youtube.com/watch?v=" . $this->faker->regexify('[A-Za-z0-9]{11}'));
        $video->setDescription($this->faker->sentence(15));
        
        $video->initializeTimestamps();

        $manager->persist($video);
    }

    private function loadExerciseComment(ObjectManager $manager, Exercise $exercise)
    {
        $user = $this->faker->randomElement($this->users);

        $comment = new CommentsExercise();
        $comment->setExercise($exercise);
        $comment->setCreator($this->faker->randomElement($this->users));
        $comment->setComment($this->faker->paragraph(2));
        $comment->setGrade($this->faker->numberBetween(1, 5));

        $comment->initializeTimestamps();

        $manager->persist($comment);
        $this->comments[] = $comment;
    }
}
