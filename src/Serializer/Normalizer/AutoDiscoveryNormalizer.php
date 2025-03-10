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
use ArrayObject;
use App\Entity\CommentsExercise as Comment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AutoDiscoveryNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        $normalized = $this->normalizer->normalize($data, $format, $context);

        if ($data instanceof User) {
            $normalized['_links'] = [
                'get' => $this->urlGenerator->generate('api_user_get', ['user' => $data->getId()]),
                'post' => $this->urlGenerator->generate('api_user_create'),
                'patch' => $this->urlGenerator->generate('api_user_update', ['user' => $data->getId()]),
                'delete' => $this->urlGenerator->generate('api_user_delete', ['user' => $data->getId()]),
            ];
        }

        if ($data instanceof Comment) {
            $normalized['_links'] = [
                'get' => $this->urlGenerator->generate('api_comment_get', ['comment' => $data->getId()]),
                'post' => $this->urlGenerator->generate('api_comment_create'),
                'delete' => $this->urlGenerator->generate('api_comment_delete', ['comment' => $data->getId()]),
            ];
        }

        if ($data instanceof Exercise) {
            $normalized['_links'] = [
                'get' => $this->urlGenerator->generate('api_exercise_get', ['exercise' => $data->getId()]),
                'post' => $this->urlGenerator->generate('api_exercise_create'),
                'patch' => $this->urlGenerator->generate('api_exercise_update', ['exercise' => $data->getId()]),
                'delete' => $this->urlGenerator->generate('api_exercise_delete', ['exercise' => $data->getId()]),
            ];
        }

        if ($data instanceof Gym) {
            $normalized['_links'] = [
                'get' => $this->urlGenerator->generate('api_gym_get', ['gym' => $data->getId()]),
                'get_all' => $this->urlGenerator->generate('api_gym_get_all'),
                'post' => $this->urlGenerator->generate('api_gym_create'),
                'patch' => $this->urlGenerator->generate('api_gym_update', ['gym' => $data->getId()]),
                'delete' => $this->urlGenerator->generate('api_gym_delete', ['gym' => $data->getId()]),
            ];
        }

        if ($data instanceof VideosExercise) {
            $normalized['_links'] = [
                'get' => $this->urlGenerator->generate('api_vide_get', ['video' => $data->getId()]),
                'post' => $this->urlGenerator->generate('api_video_create'),
                'delete' => $this->urlGenerator->generate('api_video_delete', ['video' => $data->getId()]),
            ];
        }

        if ($data instanceof Zone) {
            $normalized['_links'] = [
                'get' => $this->urlGenerator->generate('api_zone_get', ['zone' => $data->getId()]),
                'post' => $this->urlGenerator->generate('api_zone_create'),
                'patch' => $this->urlGenerator->generate('api_zone_update', ['zone' => $data->getId()]),
                'delete' => $this->urlGenerator->generate('api_zone_delete', ['zone' => $data->getId()]),
            ];
        }

        return $normalized;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return ($data instanceof User || $data instanceof Comment || $data instanceof Exercise || $data instanceof Gym || $data instanceof Zone || $data instanceof VideosExercise) && $format === 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            User::class => true,
            Comment::class => true,
            Exercise::class => true,
            Gym::class => true,
            Zone::class => true,
            VideosExercise::class => true,
        ];
    }
}
