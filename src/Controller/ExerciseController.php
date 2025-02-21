<?php

namespace App\Controller;

use App\Entity\Zone;
use App\Entity\Exercise;
use App\Repository\ExerciseRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\SerializerInterface as SerializerInterfaceSymfo;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api/gym/{gym}/zone/{zone}/exercise', name: 'api_exercise_')]
#[OA\Tag(name: 'Exercise')]
#[OA\Response(
    response: 400,
    description: 'Bad request'
)]
#[OA\Response(
    response: 401,
    description: 'Unauthorized'
)]
class ExerciseController extends AbstractController
{

    private TagAwareCacheInterface $cache;
    private EntityManager $em;

    public function __construct(TagAwareCacheInterface $cache, EntityManager $em)
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
                "name" => "example",
                "description" => "example"
            ]
        )
    )]
    /**
     * Create Exercise
     *
     * @param Zone $zone
     * @param Request $request
     * @param ExerciseRepository $exerciseRepository
     * @param SerializerInterfaceSymfo $serializerInterface
     * @return JsonResponse|null
     */
    public function createExercise(Zone $zone, Request $request, ExerciseRepository $exerciseRepository, SerializerInterfaceSymfo $serializerInterface): JsonResponse
    {
        $exercise = $serializerInterface->deserialize(
            $request->getContent(),
            Exercise::class, 
            'json'
        );

        $exercise->setZone($zone);
        $exercise->initializeTimestamps();

        $exerciseRepository->save($exercise, true);
        $this->cache->invalidateTags(['exerciseCache']);
        return new JsonResponse(null, Response::HTTP_CREATED, ["Exercise" => $exercise], true);
    }

    #[Route('/{exercise}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Exercise::class, groups: ['getOneExercise'])
    )]
    /**
     * Get One Exercise of a Zone in detail
     *
     * @param Exercise $exercise
     * @param SerializerInterface $serializer
     * @return JsonResponse|null
     */
    public function getExerciseInfos(Exercise $exercise, SerializerInterface $serializer): JsonResponse
    {
        $idCache = "getExerciseInfos" . $exercise->getId();
        $cacheRet = $this->cache->get($idCache, function (ItemInterface $item) use ($exercise, $serializer) {
            $item->tag('exercise_' . $exercise->getId());

            return $serializer->serialize(
                $exercise,
                'json',
                SerializationContext::create()->setGroups(['getOneExercise'])
            );
        });

        return new JsonResponse($cacheRet, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{exercise}', name: 'update', methods: ['PATCH'])]
    #[OA\Response(
        response: 201,
        description: 'Successful created response',
        content: null
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            example: [
                "name" => "example",
                "description" => "example"
            ]
        )
    )]
    /**
     * Update Exercise
     *
     * @param Exercise $exercise
     * @param Request $request
     * @param ExerciseRepository $exerciseRepository
     * @return JsonResponse|null
     */
    public function updateExercise(Exercise $exercise, Request $request, ExerciseRepository $exerciseRepository, SerializerInterfaceSymfo $serializerInterface): JsonResponse
    {
        $exercise = $serializerInterface->deserialize(
            $request->getContent(),
            Exercise::class,
            'json',
            ['object_to_populate' => $exercise]
        );
        $exercise->updateTimestamps();

        $exerciseRepository->save($exercise, true);
        $this->cache->invalidateTags(['exercise_' . $exercise->getId(), 'zone_' . $exercise->getZone()->getId()]);
        return new JsonResponse(null, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{exercise}', name: 'delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'Successful deleted response',
        content: null
    )]
    /**
     * Delete Exercise
     *
     * @param Exercise $exercise
     * @param ExerciseRepository $exerciseRepository
     * @return JsonResponse|null
     */
    public function deleteExercise(Exercise $exercise, ExerciseRepository $exerciseRepository): JsonResponse
    {
        $exercise->setStatus("inactive");
        $exercise->updateTimestamp();
        $exerciseRepository->save($exercise, true);
        $this->cache->invalidateTags(['exercise_' . $exercise->getId(), 'zone_' . $exercise->getZone()->getId()]);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}