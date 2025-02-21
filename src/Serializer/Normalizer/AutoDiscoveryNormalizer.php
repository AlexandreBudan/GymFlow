<?php

namespace App\Serializer\Normalizer;

use App\Entity\Address;
use App\Entity\CommentsExercise;
use App\Entity\Exercise;
use App\Entity\Gym;
use App\Entity\User;
use App\Entity\UserDetail;
use App\Entity\VideosExercise;
use App\Entity\Zone;
use PHPStan\PhpDocParser\Ast\Comment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AutoDiscoveryNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $className = (new \ReflectionClass($object))->getShortName();
        $className = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        $data["_links"] = [
            "get" => [
                "method" => ["GET"],
                "path" => $this->urlGenerator->generate("api_" . $className . "_get", ["id" => $object->getId()])
            ],
            "get_all" => [
                "method" => ["GET"],
                "path" => $this->urlGenerator->generate("api_" . $className . "_get_all")
            ],
            "create" => [
                "method" => ["POST"],
                "path" => $this->urlGenerator->generate("api_" . $className . "_create")
            ],
            "update" => [
                "method" => ["PUT"],
                "path" => $this->urlGenerator->generate("api_" . $className . "_update", ["id" => $object->getId()])
            ],
            "delete" => [
                "method" => ["DELETE"],
                "path" => $this->urlGenerator->generate("api_" . $className . "_delete", ["id" => $object->getId()])
            ]
        ];

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return ($data instanceof User || $data instanceof Address || $data instanceof Comment || $data instanceof Exercise || $data instanceof Gym || $data instanceof Zone || $data instanceof CommentsExercise || $data instanceof VideosExercise || $data instanceof UserDetail) && $format === 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            User::class => true,
            Address::class => true,
            Comment::class => true,
            Exercise::class => true,
            Gym::class => true,
            Zone::class => true,
            CommentsExercise::class => true,
            VideosExercise::class => true,
            UserDetail::class => true
        ];
    }
}