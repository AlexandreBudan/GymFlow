<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;

class VideosExerciseTest extends TestCase
{
    use Trait\EntityTestTrait;

    public function testTitleIsValid()
    {
        $videoExercise = $this->getVideosExercise();
        $videoExercise->setTitle('');

        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertGreaterThan(0, count($violations), 'Title should not be blank');

        $videoExercise->setTitle('T');
        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertGreaterThan(0, count($violations), 'Title should be at least 3 characters long');

        $videoExercise->setTitle('Valid Title');
        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertEquals(0, count($violations), 'Title should be valid');
    }

    public function testUrlIsValid()
    {
        $videoExercise = $this->getVideosExercise();
        $videoExercise->setUrl('');

        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertGreaterThan(0, count($violations), 'URL should not be blank');

        $videoExercise->setUrl('invalid-url');
        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertGreaterThan(0, count($violations), 'URL should be valid');

        $videoExercise->setUrl('https://valid-url.com');
        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertEquals(0, count($violations), 'URL should be valid');
    }

    public function testDescriptionLength()
    {
        $videoExercise = $this->getVideosExercise();
        $videoExercise->setDescription(str_repeat('A', 256));

        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertGreaterThan(0, count($violations), 'Description should not exceed 255 characters');

        $videoExercise->setDescription(str_repeat('A', 255));
        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertEquals(0, count($violations), 'Description length should be valid');
    }

    public function testExerciseRelation()
    {
        $videoExercise = $this->getVideosExercise();
        $exercise = $this->getExercise();

        $videoExercise->setExercise($exercise);
        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertEquals(0, count($violations), 'Exercise relation should be set correctly');
        $this->assertEquals($exercise, $videoExercise->getExercise());
    }

    public function testCreatorRelation()
    {
        $videoExercise = $this->getVideosExercise();
        $userDetail = $this->getUserDetail();

        $videoExercise->setCreator($userDetail);
        $violations = $this->getValidator()->validate($videoExercise);
        $this->assertEquals(0, count($violations), 'Creator relation should be set correctly');
        $this->assertEquals($userDetail, $videoExercise->getCreator());
    }

    public function testSetAndGetMethods()
    {
        $videoExercise = $this->getVideosExercise();

        // Test Title
        $videoExercise->setTitle('Test Title');
        $this->assertEquals('Test Title', $videoExercise->getTitle());

        // Test URL
        $videoExercise->setUrl('https://example.com');
        $this->assertEquals('https://example.com', $videoExercise->getUrl());

        // Test Description
        $videoExercise->setDescription('This is a video description.');
        $this->assertEquals('This is a video description.', $videoExercise->getDescription());
    }
}
