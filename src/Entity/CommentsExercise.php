<?php

namespace App\Entity;

use App\Repository\CommentsExerciseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentsExerciseRepository::class)]
class CommentsExercise
{
    use Trait\StatisticsPropertiesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: "uuid", unique: true)]
    #[OA\Property(type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000")]
    #[Groups(['getOneExercise', 'getOneComment'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'commentsExercises')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'exercice est obligatoire.")]
    private ?Exercise $exercise = null;

    #[ORM\ManyToOne(inversedBy: 'commentsExercises')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le créateur du commentaire est obligatoire.")]
    #[Groups(['getOneExercise', 'getOneComment'])]
    private ?UserDetail $creator = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide.")]
    #[Assert\Length(
        min: 5, minMessage: "Le commentaire doit contenir au moins 5 caractères.",
        max: 2000, maxMessage: "Le commentaire ne peut pas dépasser 2000 caractères."
    )]
    #[Groups(['getOneExercise', 'getOneComment'])]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 1, max: 5,
        notInRangeMessage: "La note doit être comprise entre 1 et 5."
    )]
    #[Groups(['getOneExercise', 'getOneComment'])]
    private ?int $grade = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getExercise(): ?Exercise
    {
        return $this->exercise;
    }

    public function setExercise(?Exercise $exercise): static
    {
        $this->exercise = $exercise;

        return $this;
    }

    public function getCreator(): ?UserDetail
    {
        return $this->creator;
    }

    public function setCreator(?UserDetail $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(?int $grade): static
    {
        $this->grade = $grade;

        return $this;
    }
}
