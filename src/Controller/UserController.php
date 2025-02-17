<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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

#[Route('/api/user')]
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
    #[Route('', name: 'user.get', methods: ['GET'])]
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
        $userInfos = $serializer->serialize(
            $userRepository->getUserConnected($this->getUser()),
            'json',
            SerializationContext::create()->setGroups(['getOneUser'])
        );
        return new JsonResponse($userInfos, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('', name: 'user.update', methods: ["PATCH"])]
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
                "email" => "example.example@gmail.com",
                "pseudo" => "pseudo"
            ]
        )
    )]
    /**
     * Update Information of User Connected
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SerializerInterfaceSymfo $serializerInterface
     * @return JsonResponse|null
     */
    public function updateUser(Request $request, UserRepository $userRepository, SerializerInterfaceSymfo $serializerInterface): ?JsonResponse
    {

        $user = $serializerInterface->deserialize(
            $request->getContent(),
            User::class,
            'json'
        );

        if (array_key_exists('password', json_decode($request->getContent(), true))) {
            throw new BadRequestException("Can't update password in this route. Use /api/user/passwordEdit for this");
        };

        $userRepository->save($user, true);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    #[Route('', name: 'user.delete', methods: ['DELETE'])]
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
     * @return JsonResponse|null
     */
    public function deleteUser(UserRepository $userRepository): JsonResponse
    {
        $userRepository->remove($this->getUser(), true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    #[Route('', name: 'user.create', methods: ["POST"])]
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
     * @return JsonResponse|null
     */
    public function createUser(Request $request, UserRepository $userRepository, SerializerInterfaceSymfo $serializerInterface, UserPasswordHasherInterface $passwordHasher): ?JsonResponse
    {

        $userRepository->checkFormCreateUser($request->getContent());

        $user = new User();

        $user = $serializerInterface->deserialize(
            $request->getContent(),
            User::class,
            'json'
        );

        $user->setPassword($passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        ));

        $userRepository->save($user, true);
        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }
}