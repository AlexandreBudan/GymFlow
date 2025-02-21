<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Gym;
use App\Repository\AddressRepository;
use App\Repository\GymRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface as SerializerInterfaceSymfo;

#[Route('/api/gym')]
#[OA\Tag(name: 'Gym')]
#[OA\Response(
    response: 400,
    description: 'Bad request'
)]
#[OA\Response(
    response: 401,
    description: 'Unauthorized'
)]
final class GymController extends AbstractController
{

    private TagAwareCacheInterface $cache;

    public function __construct(TagAwareCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    #[Route('', name: 'gym.create', methods: ['POST'])]
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
                "address" => [
                    "street" => "example",
                    "city" => "example",
                    "zipCode" => "example",
                    "country" => "example"
                ]
            ]
        )
    )]
    /**
     * Create Gym
     *
     * @param Request $request
     * @param GymRepository $gymRepository
     * @param SerializerInterfaceSymfo $serializerInterface
     * @param AddressRepository $addressRepository
     * @return JsonResponse|null
     */
    public function createGym(Request $request, GymRepository $gymRepository, SerializerInterfaceSymfo $serializerInterface, AddressRepository $addressRepository): JsonResponse
    {
        $address = $serializerInterface->deserialize(
            json_decode($request->getContent(), true)['address'],
            Address::class, 
            'json'
        );
        $address->initializeTimestamps();

        $gym = new Gym();
        $gym->setAddress($address);
        $gym->setName($request->get('name'));
        $gym->initializeTimestamps();

        $addressRepository->save($address, true);
        $gymRepository->save($gym, true);
        $this->cache->invalidateTags(['gymCache']);
        return new JsonResponse(null, Response::HTTP_CREATED, ["Gym" => $gym], true);
    }

    #[Route('s', name: 'gym.getAll', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Gym::class, groups: ['getAllGyms']))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number',
        required: false,
        schema: new OA\Schema(type: 'integer')
    ),
    OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Number of elements per page',
        required: false,
        schema: new OA\Schema(type: 'integer')
    ),
    OA\Parameter(
        name: 'location',
        in: 'query',
        description: 'Location of the gym',
        required: false,
        schema: new OA\Schema(type: 'string')
    ),
    OA\Parameter(
        name: 'search',
        in: 'query',
        description: 'Search by name',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    /**
     * Get All Gym with filter possibilities
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param GymRepository $gymRepository
     * @return JsonResponse|null
     */
    public function getAllGyms(Request $request, SerializerInterface $serializer, GymRepository $gymRepository): JsonResponse
    {
        $list = $gymRepository->settingsManagement($request);

        $idCache = "getAllGyms_" . md5(serialize($list));
        $cacheRet = $this->cache->get($idCache, function (ItemInterface $item) use ($gymRepository, $serializer, $list) {
            $item->tag('gymCache');

            $page = $list[0];
            $limit = $list[1];
            $location = $list[2];
            $search = $list[3];

            return $serializer->serialize(
                $gymRepository->findAllByPagination($page, $limit, $location, $search),
                'json',
                SerializationContext::create()->setGroups(['getAllGyms'])
            );
        });

        return new JsonResponse($cacheRet, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{gym}', name: 'gym.get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Gym::class, groups: ['getOneGym'])
    )]
    /**
     * Get One Gym in detail
     *
     * @param Gym $gym
     * @param SerializerInterface $serializer
     * @return JsonResponse|null
     */
    public function getGymInfos(Gym $gym, SerializerInterface $serializer): JsonResponse
    {
        $idCache = "getGymInfos_" . $gym->getId();
        $cacheRet = $this->cache->get($idCache, function (ItemInterface $item) use ($gym, $serializer) {
            $item->tag('gym_' . $gym->getId());

            return $serializer->serialize(
                $gym,
                'json',
                SerializationContext::create()->setGroups(['getOneGym'])
            );
        });

        return new JsonResponse($cacheRet, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{gym}', name: 'gym.update', methods: ['PATCH'])]
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
                "address" => [
                    "street" => "example",
                    "city" => "example",
                    "zipCode" => "example",
                    "country" => "example"
                ]
            ]
        )
    )]
    /**
     * Update Gym
     *
     * @param Gym $gym
     * @param Request $request
     * @param GymRepository $gymRepository
     * @return JsonResponse|null
     */
    public function updateGym(Gym $gym, Request $request, GymRepository $gymRepository, SerializerInterfaceSymfo $serializerInterface, AddressRepository $addressRepository): JsonResponse
    {
        $address = $serializerInterface->deserialize(
            $request->getContent(), 
            Address::class, 
            'json',
            ['object_to_populate' => $gym->getAddress()],
        );

        $address->updateTimestamps();
        $gym->updateTimestamp();

        $addressRepository->save($address, true);
        $gymRepository->save($gym->setName($request->get('name')), true);
        $this->cache->invalidateTags(['gym_' . $gym->getId(), 'gymCache']);
        return new JsonResponse(null, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{gym}', name: 'gym.delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'Successful deleted response',
        content: null
    )]
    /**
     * Delete Gym
     *
     * @param Gym $gym
     * @param GymRepository $gymRepository
     * @return JsonResponse|null
     */
    public function deleteGym(Gym $gym, GymRepository $gymRepository): JsonResponse
    {
        $gym->updateTimestamp();
        $gymRepository->save($gym->setStatus("inactive"), true);
        $this->cache->invalidateTags(['gym_' . $gym->getId(), 'gymCache']);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}
