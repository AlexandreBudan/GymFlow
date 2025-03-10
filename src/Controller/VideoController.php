<?php

namespace App\Controller;

use App\Entity\Exercise;
use App\Entity\VideosExercise;
use App\Repository\VideosExerciseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OA;

#[Route('/api/video', name: 'api_video_')]
#[OA\Tag(name: 'Video')]
#[OA\Response(
    response: 400,
    description: 'Bad request'
)]
#[OA\Response(
    response: 401,
    description: 'Unauthorized'
)]
final class VideoController extends AbstractController
{
    private TagAwareCacheInterface $cache;
    private EntityManagerInterface $em;

    public function __construct(TagAwareCacheInterface $cache, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->em->getFilters()->enable('active_filter');
        $this->cache = $cache;
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Successful created response',
        content: null
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            example: [
                "title" => "example",
                "url" => "example",
                "description" => "example"
            ]
        )
    )]
    /**
     * Create Video
     *
     * @param Exercise $exercise
     * @param Request $request
     * @param VideosExerciseRepository $videoExerciseRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse|null
     */
    public function createVideo(Exercise $exercise, Request $request, VideosExerciseRepository $videoExerciseRepository, SerializerInterface $serializer): JsonResponse
    {
        $video = $serializer->deserialize(
            $request->getContent(),
            VideosExercise::class, 
            'json'
        );

        $video->setExercise($exercise);
        $video->initializeTimestamps();

        $videoExerciseRepository->save($video, true);
        $this->cache->invalidateTags(['exerciseCache', 'exercise_' . $exercise->getId()]);
        return new JsonResponse(null, Response::HTTP_CREATED, ["Video" => $video], true);
    }

    #[Route('/{video}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: VideosExercise::class, groups: ['getOneVideo'])
    )]
    /**
     * Get One Video of a Exercise in detail
     *
     * @param VideosExercise $video
     * @param SerializerInterface $serializer
     * @return JsonResponse|null
     */
    public function getVideoInfos(VideosExercise $video, SerializerInterface $serializer): JsonResponse
    {
        $idCache = "getVideoInfos" . $video->getId();
        $cacheRet = $this->cache->get($idCache, function (ItemInterface $item) use ($video, $serializer) {
            $item->tag('video_' . $video->getId());

            return $serializer->serialize(
                $video,
                'json',
                ['groups' => ['getOneVideo']]
            );
        });

        return new JsonResponse($cacheRet, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{video}', name: 'delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'Successful deleted response',
        content: null
    )]
    /**
     * Delete Video
     *
     * @param VideosExercise $video
     * @param VideosExerciseRepository $videoExerciseRepository
     * @return JsonResponse|null
     */
    public function deleteVideo(VideosExercise $video, VideosExerciseRepository $videoExerciseRepository): JsonResponse
    {
        $video->setStatus("inactive");
        $video->updateTimestamp();
        $videoExerciseRepository->save($video, true);
        $this->cache->invalidateTags(['video_' . $video->getId(), 'exercise_' . $video->getExercise()->getId()]);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}
