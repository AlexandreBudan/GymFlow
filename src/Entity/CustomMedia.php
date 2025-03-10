<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CustomMediaRepository;

#[ORM\Entity(repositoryClass: CustomMediaRepository::class)]
#[Vich\Uploadable]
class CustomMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $realName = null;

    #[ORM\Column(length: 255)]
    private ?string $realPath = null;

    #[ORM\Column(length: 255)]
    private ?string $publicPath = null;

    #[Vich\UploadableField(mapping: 'medias', fileNameProperty: "realPath", originalName: "realName")]
    private ?File $media = null;

    public function getMedia(): ?File
    {
        return $this->media;
    }
    public function setMedia(File $media): self
    {
        $this->media = $media;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): static
    {
        $this->realName = $realName;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->realPath;
    }

    public function setRealPath(string $realPath): static
    {
        $this->realPath = $realPath;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): static
    {
        $this->publicPath = $publicPath;

        return $this;
    }
}