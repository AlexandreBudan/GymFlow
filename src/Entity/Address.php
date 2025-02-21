<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    use Trait\StatisticsPropertiesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: "uuid", unique: true)]
    #[OA\Property(type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000")]
    #[Groups(['getAllGyms'])]
    private ?Uuid $id = null;

    #[ORM\OneToOne(inversedBy: 'address', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Gym est obligatoire.")]
    private ?Gym $gym = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "L'adresse ne peut pas dépasser 255 caractères.")]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le code postal est obligatoire.")]
    #[Assert\Length(max: 20, maxMessage: "Le code postal ne peut pas dépasser 20 caractères.")]
    #[Assert\Regex(pattern: "/^\d{2,5}(-\d{3,4})?$/", message: "Le code postal doit être valide.")]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllGyms'])]
    #[Assert\NotBlank(message: "La ville est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "La ville ne peut pas dépasser 255 caractères.")]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllGyms'])]
    #[Assert\NotBlank(message: "Le pays est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "Le pays ne peut pas dépasser 255 caractères.")]
    private ?string $country = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7)]
    #[Assert\NotBlank(message: "La latitude est obligatoire.")]
    #[Assert\Type(type: "numeric", message: "La latitude doit être un nombre.")]
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: "La latitude doit être comprise entre -90 et 90.")]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7)]
    #[Assert\NotBlank(message: "La longitude est obligatoire.")]
    #[Assert\Type(type: "numeric", message: "La longitude doit être un nombre.")]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: "La longitude doit être comprise entre -180 et 180.")]
    private ?string $longitude = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getGym(): ?Gym
    {
        return $this->gym;
    }

    public function setGym(Gym $gym): static
    {
        $this->gym = $gym;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }
}
