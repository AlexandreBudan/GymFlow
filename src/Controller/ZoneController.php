<?php

namespace App\Controller;

use App\Entity\Gym;
use App\Entity\Zone;
use App\Repository\ZoneRepository;
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

#[Route('/api/gym/{gym}/zone', name: 'api_zone_')]
#[OA\Tag(name: 'Zone')]
#[OA\Response(
    response: 400,
    description: 'Bad request'
)]
#[OA\Response(
    response: 401,
    description: 'Unauthorized'
)]
class ZoneController extends AbstractController
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
            ]
        )
    )]
    /**
     * Create Zone
     *
     * @param Gym $gym
     * @param Request $request
     * @param ZoneRepository $zoneRepository
     * @param SerializerInterfaceSymfo $serializerInterface
     * @return JsonResponse|null
     */
    public function createZone(Gym $gym, Request $request, ZoneRepository $zoneRepository, SerializerInterfaceSymfo $serializerInterface): JsonResponse
    {
        $zone = $serializerInterface->deserialize(
            $request->getContent(),
            Zone::class, 
            'json'
        );

        $zone->setGym($gym);
        $zone->initializeTimestamps();

        $zoneRepository->save($zone, true);
        $this->cache->invalidateTags(['zoneCache']);
        return new JsonResponse(null, Response::HTTP_CREATED, ["Zone" => $zone], true);
    }

    #[Route('/{zone}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Zone::class, groups: ['getOneZone'])
    )]
    /**
     * Get One Zone of a gym in detail
     *
     * @param Zone $zone
     * @param SerializerInterface $serializer
     * @param ZoneRepository $zoneRepository
     * @return JsonResponse|null
     */
    public function getZoneInfos(Zone $zone, SerializerInterface $serializer): JsonResponse
    {
        $idCache = "getZoneInfos_" . $zone->getId();
        $cacheRet = $this->cache->get($idCache, function (ItemInterface $item) use ($zone, $serializer) {
            $item->tag('zone_' . $zone->getId());

            return $serializer->serialize(
                $zone,
                'json',
                SerializationContext::create()->setGroups(['getOneZone'])
            );
        });

        return new JsonResponse($cacheRet, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{zone}', name: 'update', methods: ['PATCH'])]
    #[OA\Response(
        response: 201,
        description: 'Successful created response',
        content: null
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            example: [
                "name" => "example"
            ]
        )
    )]
    /**
     * Update Zone
     *
     * @param Zone $zone
     * @param Request $request
     * @param ZoneRepository $zoneRepository
     * @return JsonResponse|null
     */
    public function updateZone(Zone $zone, Request $request, ZoneRepository $zoneRepository): JsonResponse
    {
        $zone->updateTimestamp();
        $zoneRepository->save($zone->setName($request->get('name')), true);
        $this->cache->invalidateTags(['zone_' . $zone->getId(), 'gym_' . $zone->getGym()->getId()]);
        return new JsonResponse(null, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{zone}', name: 'delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'Successful deleted response',
        content: null
    )]
    /**
     * Delete Zone
     *
     * @param Zone $zone
     * @param ZoneRepository $zoneRepository
     * @return JsonResponse|null
     */
    public function deleteZone(Zone $zone, ZoneRepository $zoneRepository): JsonResponse
    {
        $zone->setStatus("inactive");
        $zone->updateTimestamp();
        $zoneRepository->save($zone, true);
        $this->cache->invalidateTags(['zone_' . $zone->getId(), 'gym_' . $zone->getGym()->getId()]);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}