<?php

namespace App\Entity;

use App\Repository\UserDetailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserDetailRepository::class)]
#[UniqueEntity(fields: ['pseudo'], message: "Ce pseudo est déjà pris.")]
class UserDetail
{
    use Trait\StatisticsPropertiesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: "uuid", unique: true)]
    #[OA\Property(type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000")]
    #[Groups(['getOneUser', 'getOneExercise', 'getOneVideo', 'getOneComment'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Le pseudo est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le pseudo doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le pseudo ne peut pas contenir plus de {{ limit }} caractères."
    )]
    #[Groups(['getOneUser', 'getOneExercise', 'getOneVideo', 'getOneComment'])]
    private ?string $pseudo = null;

    #[ORM\OneToOne(mappedBy: 'userDetail', cascade: ['persist', 'remove'])]
    private ?User $userAuth = null;

    #[ORM\OneToMany(targetEntity: VideosExercise::class, mappedBy: 'creator', orphanRemoval: true)]
    private Collection $videosExercises;

    #[ORM\OneToMany(targetEntity: CommentsExercise::class, mappedBy: 'creator', orphanRemoval: true)]
    private Collection $commentsExercises;

    #[ORM\ManyToMany(targetEntity: Gym::class, inversedBy: 'users')]
    #[Groups(['getOneUser'])]
    private Collection $gymsFav;

    #[ORM\ManyToMany(targetEntity: Exercise::class)]
    #[Groups(['getOneUser'])]
    private Collection $likedExercises;

    public function __construct()
    {
        $this->videosExercises = new ArrayCollection();
        $this->commentsExercises = new ArrayCollection();
        $this->gymsFav = new ArrayCollection();
        $this->likedExercises = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getUserAuth(): ?User
    {
        return $this->userAuth;
    }

    public function setUserAuth(?User $userAuth): static
    {
        // unset the owning side of the relation if necessary
        if ($userAuth === null && $this->userAuth !== null) {
            $this->userAuth->setUserDetail(null);
        }

        // set the owning side of the relation if necessary
        if ($userAuth !== null && $userAuth->getUserDetail() !== $this) {
            $userAuth->setUserDetail($this);
        }

        $this->userAuth = $userAuth;

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
            $videosExercise->setCreator($this);
        }

        return $this;
    }

    public function removeVideosExercise(VideosExercise $videosExercise): static
    {
        if ($this->videosExercises->removeElement($videosExercise)) {
            // set the owning side to null (unless already changed)
            if ($videosExercise->getCreator() === $this) {
                $videosExercise->setCreator(null);
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
            $commentsExercise->setCreator($this);
        }

        return $this;
    }

    public function removeCommentsExercise(CommentsExercise $commentsExercise): static
    {
        if ($this->commentsExercises->removeElement($commentsExercise)) {
            // set the owning side to null (unless already changed)
            if ($commentsExercise->getCreator() === $this) {
                $commentsExercise->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Gym>
     */
    public function getGymsFav(): Collection
    {
        return $this->gymsFav;
    }

    public function addGymsFav(Gym $gymsFav): static
    {
        if (!$this->gymsFav->contains($gymsFav)) {
            $this->gymsFav->add($gymsFav);
        }

        return $this;
    }

    public function removeGymsFav(Gym $gymsFav): static
    {
        $this->gymsFav->removeElement($gymsFav);

        return $this;
    }

    /**
     * @return Collection<int, Exercise>
     */
    public function getLikedExercises(): Collection
    {
        return $this->likedExercises;
    }

    public function addLikedExercise(Exercise $likedExercise): static
    {
        if (!$this->likedExercises->contains($likedExercise)) {
            $this->likedExercises->add($likedExercise);
        }

        return $this;
    }

    public function removeLikedExercise(Exercise $likedExercise): static
    {
        $this->likedExercises->removeElement($likedExercise);

        return $this;
    }
}
