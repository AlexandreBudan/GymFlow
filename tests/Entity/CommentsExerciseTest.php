<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;

class CommentsExerciseTest extends TestCase
{
    use Trait\EntityTestTrait;

    public function testExerciseIsNotNull()
    {
        $commentExercise = $this->getCommentsExercise();
        $commentExercise->setExercise(null);

        $violations = $this->getValidator()->validate($commentExercise);

        $this->assertGreaterThan(0, count($violations), 'Exercise should not be null');
    }

    public function testCreatorIsNotNull()
    {
        $commentExercise = $this->getCommentsExercise();
        $commentExercise->setCreator(null);

        $violations = $this->getValidator()->validate($commentExercise);

        $this->assertGreaterThan(0, count($violations), 'Creator should not be null');
    }

    public function testCommentIsNotBlankAndLengthValid()
    {
        $commentExercise = $this->getCommentsExercise();
        $commentExercise->setComment('');
        
        $violations = $this->getValidator()->validate($commentExercise);
        $this->assertGreaterThan(0, count($violations), 'Comment should not be blank');

        $commentExercise->setComment('A'); // Too short
        $violations = $this->getValidator()->validate($commentExercise);
        $this->assertGreaterThan(0, count($violations), 'Comment should be at least 5 characters');

        $commentExercise->setComment(str_repeat('A', 2001)); // Too long
        $violations = $this->getValidator()->validate($commentExercise);
        $this->assertGreaterThan(0, count($violations), 'Comment should not exceed 2000 characters');
    }

    public function testGradeIsValid()
    {
        $commentExercise = $this->getCommentsExercise();
        
        // Invalid grade (below range)
        $commentExercise->setGrade(0);
        $violations = $this->getValidator()->validate($commentExercise);
        $this->assertGreaterThan(0, count($violations), 'Grade should be between 1 and 5');
        
        // Invalid grade (above range)
        $commentExercise->setGrade(6);
        $violations = $this->getValidator()->validate($commentExercise);
        $this->assertGreaterThan(0, count($violations), 'Grade should be between 1 and 5');
        
        // Valid grade
        $commentExercise->setGrade(3);
        $violations = $this->getValidator()->validate($commentExercise);
        $this->assertEquals(0, count($violations), 'Grade should be between 1 and 5');
    }
}
