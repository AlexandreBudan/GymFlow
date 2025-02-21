<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;

class ExerciseTest extends TestCase
{
    use Trait\EntityTestTrait;

    public function testNameIsNotBlankAndLengthValid()
    {
        $exercise = $this->getExercise();
        $exercise->setName('');

        $violations = $this->getValidator()->validate($exercise);
        $this->assertGreaterThan(0, count($violations), 'Name should not be blank');

        $exercise->setName(str_repeat('A', 256)); // Too long
        $violations = $this->getValidator()->validate($exercise);
        $this->assertGreaterThan(0, count($violations), 'Name should not exceed 255 characters');
    }

    public function testDescriptionIsNotBlankAndLengthValid()
    {
        $exercise = $this->getExercise();
        $exercise->setDescription('');

        $violations = $this->getValidator()->validate($exercise);
        $this->assertGreaterThan(0, count($violations), 'Description should not be blank');

        $exercise->setDescription(str_repeat('A', 256)); // Too long
        $violations = $this->getValidator()->validate($exercise);
        $this->assertGreaterThan(0, count($violations), 'Description should not exceed 255 characters');
    }

    public function testZoneIsNotNull()
    {
        $exercise = $this->getExercise();
        $exercise->setZone(null);

        $violations = $this->getValidator()->validate($exercise);

        $this->assertGreaterThan(0, count($violations), 'Zone should not be null');
    }

    public function testVideosExercisesAssociation()
    {
        $exercise = $this->getExercise();
        $zone = $this->getZone();
        $exercise->setZone($zone);

        $videosExercise = $this->getVideosExercise();
        $exercise->addVideosExercise($videosExercise);

        $violations = $this->getValidator()->validate($exercise);
        $this->assertEquals(0, count($violations), 'VideosExercises should be associated correctly');
    }

    public function testCommentsExercisesAssociation()
    {
        $exercise = $this->getExercise();
        $zone = $this->getZone();
        $exercise->setZone($zone);

        $commentsExercise =  $this->getCommentsExercise();
        $exercise->addCommentsExercise($commentsExercise);

        $violations = $this->getValidator()->validate($exercise);
        $this->assertEquals(0, count($violations), 'CommentsExercises should be associated correctly');
    }
}
