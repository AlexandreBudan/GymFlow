<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, VideosExercise>
     */
    #[ORM\OneToMany(targetEntity: VideosExercise::class, mappedBy: 'creator', orphanRemoval: true)]
    private Collection $videosExercises;

    /**
     * @var Collection<int, CommentsExercise>
     */
    #[ORM\OneToMany(targetEntity: CommentsExercise::class, mappedBy: 'creator', orphanRemoval: true)]
    private Collection $commentsExercises;

    /**
     * @var Collection<int, Gym>
     */
    #[ORM\ManyToMany(targetEntity: Gym::class, inversedBy: 'users')]
    private Collection $gymsFav;

    /**
     * @var Collection<int, Exercise>
     */
    #[ORM\ManyToMany(targetEntity: Exercise::class)]
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

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
