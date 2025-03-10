<?php

namespace App\Entity;

use App\Repository\ExerciseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExerciseRepository::class)]
class Exercise
{
    use Trait\StatisticsPropertiesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: "uuid", unique: true)]
    #[OA\Property(type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000")]
    #[Groups(['getOneUser', 'getOneZone', 'getOneExercise'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getOneUser', 'getOneZone', 'getOneExercise'])]
    #[Assert\NotBlank(message: "Le nom de l'exercice est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom de l'exercice ne peut pas dépasser 255 caractères."
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getOneUser', 'getOneZone', 'getOneExercise'])]
    #[Assert\NotBlank(message: "La description de l'exercice est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description de l'exercice ne peut pas dépasser 255 caractères."
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'exercises')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getOneUser', 'getOneExercise'])]
    #[Assert\NotNull(message: "La zone est obligatoire pour l'exercice.")]
    private ?Zone $zone = null;

    /**
     * @var Collection<int, VideosExercise>
     */
    #[ORM\OneToMany(targetEntity: VideosExercise::class, mappedBy: 'exercise', orphanRemoval: true)]
    #[Groups(['getOneExercise'])]
    private Collection $videosExercises;

    /**
     * @var Collection<int, CommentsExercise>
     */
    #[ORM\OneToMany(targetEntity: CommentsExercise::class, mappedBy: 'exercise', orphanRemoval: true)]
    #[Groups(['getOneExercise'])]
    private Collection $commentsExercises;

    public function __construct()
    {
        $this->videosExercises = new ArrayCollection();
        $this->commentsExercises = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * @return Collection<int, VideosExercise>
     */
    public function getVideosExercises(): Collection
    {
        return $this->videosExercises;
    }

    public function addVideosExercise(VideosExercise $videosExercise): static
    {
        if (!$this->videosExercises->contains($videosExercise)) {
            $this->videosExercises->add($videosExercise);
            $videosExercise->setExercise($this);
        }

        return $this;
    }

    public function removeVideosExercise(VideosExercise $videosExercise): static
    {
        if ($this->videosExercises->removeElement($videosExercise)) {
            // set the owning side to null (unless already changed)
            if ($videosExercise->getExercise() === $this) {
                $videosExercise->setExercise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentsExercise>
     */
    public function getCommentsExercises(): Collection
    {
        return $this->commentsExercises;
    }

    public function addCommentsExercise(CommentsExercise $commentsExercise): static
    {
        if (!$this->commentsExercises->contains($commentsExercise)) {
            $this->commentsExercises->add($commentsExercise);
            $commentsExercise->setExercise($this);
        }

        return $this;
    }

    public function removeCommentsExercise(CommentsExercise $commentsExercise): static
    {
        if ($this->commentsExercises->removeElement($commentsExercise)) {
            // set the owning side to null (unless already changed)
            if ($commentsExercise->getExercise() === $this) {
                $commentsExercise->setExercise(null);
            }
        }

        return $this;
    }
}
