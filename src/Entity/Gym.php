<?php

namespace App\Entity;

use App\Repository\GymRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GymRepository::class)]
class Gym
{
    use Trait\StatisticsPropertiesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: "uuid", unique: true)]
    #[OA\Property(type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000")]
    #[Groups(['getOneUser', 'getAllGyms', 'getOneGym', 'getOneZone'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getOneUser', 'getAllGyms', 'getOneGym', 'getOneZone'])]
    #[Assert\NotBlank(message: "Le nom de la salle de sport est obligatoire.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le nom de la salle de sport doit contenir au moins 3 caractères.",
        max: 255,
        maxMessage: "Le nom de la salle de sport ne peut pas dépasser 255 caractères."
    )]
    private ?string $name = null;

    #[ORM\OneToOne(mappedBy: 'gym', cascade: ['persist', 'remove'])]
    #[Groups(['getAllGyms', 'getOneGym'])]
    #[Assert\Valid]
    private ?Address $address = null;

    /**
     * @var Collection<int, Zone>
     */
    #[ORM\OneToMany(targetEntity: Zone::class, mappedBy: 'gym', orphanRemoval: true)]
    #[Groups(['getOneGym'])]
    private Collection $zones;

    /**
     * @var Collection<int, UserDetail>
     */
    #[ORM\ManyToMany(targetEntity: UserDetail::class, mappedBy: 'gymsFav')]
    private Collection $users;

    public function __construct()
    {
        $this->zones = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        // set the owning side of the relation if necessary
        if ($address->getGym() !== $this) {
            $address->setGym($this);
        }

        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Zone>
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $zone): static
    {
        if (!$this->zones->contains($zone)) {
            $this->zones->add($zone);
            $zone->setGym($this);
        }

        return $this;
    }

    public function removeZone(Zone $zone): static
    {
        if ($this->zones->removeElement($zone)) {
            // set the owning side to null (unless already changed)
            if ($zone->getGym() === $this) {
                $zone->setGym(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserDetail>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserDetail $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addGymsFav($this);
        }

        return $this;
    }

    public function removeUser(UserDetail $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeGymsFav($this);
        }

        return $this;
    }
}
