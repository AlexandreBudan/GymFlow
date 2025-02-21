<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserDetail;
use App\Repository\UserDetailRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface as SerializerInterfaceSymfo;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api/user', name: 'api_user_')]
#[OA\Tag(name: 'User')]
#[OA\Response(
    response: 400,
    description: 'Bad request'
)]
#[OA\Response(
    response: 401,
    description: 'Unauthorized'
)]
class UserController extends AbstractController
{

    private TagAwareCacheInterface $cache;
    private EntityManager $em;

    public function __construct(TagAwareCacheInterface $cache, EntityManager $em)
    {
        $this->em = $em;
        $this->em->getFilters()->enable('active_filter');
        $this->cache = $cache;
    }

    #[Route('', name: 'create', methods: ["POST"])]
    #[OA\Response(
        response: 201,
        description: 'Successful created response',
        content: null
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            example: [
                "email" => "example.example@gmail.com",
                "password" => "password",
                "pseudo" => "pseudo"
            ]
        )
    )]
    /**
     * Create User
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SerializerInterfaceSymfo $serializerInterface
     * @param UserPasswordHasherInterface $passwordHasher
     * @return JsonResponse|null
     */
    public function createUser(Request $request, UserRepository $userRepository, UserDetailRepository $userDetailRepository, SerializerInterfaceSymfo $serializerInterface, UserPasswordHasherInterface $passwordHasher): ?JsonResponse
    {

        $userRepository->checkFormCreateUser($request->getContent());
        $user = $serializerInterface->deserialize(
            $request->getContent(),
            User::class,
            'json'
        );
        $user->setPassword($passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        ));

        $userDetail = new UserDetail();
        $userDetail->setUserAuth($user);
        $userDetail->initializeTimestamps();
        $userDetailRepository->save($userDetail->setPseudo(json_decode($request->getContent(), true)['pseudo']), true);

        $user->setUserDetail($userDetail);
        $userRepository->save($user, true);
        return new JsonResponse(null, Response::HTTP_CREATED, ["User" => $user], true);
    }

    #[Route('', name: 'get', methods: ['GET'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: User::class, groups: ['getOneUser'])
    )]
    /**
     * Get User Connected
     *
     * @param SerializerInterface $serializer
     * @param UserRepository $userRepository
     * @return JsonResponse|null
     */
    public function getUserInfos(SerializerInterface $serializer, UserRepository $userRepository): JsonResponse
    {
        $idUserCaller = $userRepository->getUserConnected($this->getUser())->getId();

        $idCache = "getUserInfos_" . $idUserCaller;
        $cacheRet = $this->cache->get($idCache, function (ItemInterface $item) use ($userRepository, $serializer, $idUserCaller) {
            $item->tag('user_' . $idUserCaller);

            return $serializer->serialize(
                $userRepository->getUserConnected($this->getUser()),
                'json',
                SerializationContext::create()->setGroups(['getOneUser'])
            );
        });

        return new JsonResponse($cacheRet, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('', name: 'update', methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[OA\Response(
        response: 201,
        description: 'Successful created response',
        content: null
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            example: [
                "pseudo" => "pseudo"
            ]
        )
    )]
    /**
     * Update Information of User Connected
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserDetailRepository $userDetailRepository
     * @param SerializerInterfaceSymfo $serializerInterface
     * @return JsonResponse|null
     */
    public function updateUser(Request $request, UserRepository $userRepository, UserDetailRepository $userDetailRepository, SerializerInterfaceSymfo $serializerInterface): ?JsonResponse
    {
        if (array_key_exists('password', json_decode($request->getContent(), true))) {
            throw new BadRequestException("Can't update password or email in this route. Use /api/user/passwordEdit for this");
        };

        $idUserCaller = $userRepository->getUserConnected($this->getUser())->getId();

        $userDetail = $serializerInterface->deserialize(
            $request->getContent(),
            UserDetail::class,
            'json',
            ['object_to_populate' => $userRepository->getUserConnected($this->getUser())->getUserDetail()]
        );

        $userDetailRepository->save($userDetail, true);
        $this->cache->invalidateTags(["user_" . $idUserCaller]);
        return new JsonResponse(null, Response::HTTP_OK, ["UserDetail" => $userDetail], false);
    }

    #[Route('/del', name: 'delete', methods: ['DELETE'])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[OA\Response(
        response: 204,
        description: 'Successful no content response',
        content: null
    )]
    /**
     * Delete User Connected
     *
     * @param UserRepository $userRepository
     * @param UserDetailRepository $userDetailRepository
     * @return JsonResponse|null
     */
    public function deleteUser(UserRepository $userRepository, UserDetailRepository $userDetailRepository): JsonResponse
    {
        $user = $userRepository->getUserConnected($this->getUser());
        $idUserCaller = $user->getId();

        $userDetailRepository->save($user->getUserDetail()->setStatus("anon")->setPseudo("anon"), true);
        $userRepository->remove($user);
        $this->cache->invalidateTags(["user_" . $idUserCaller]);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}