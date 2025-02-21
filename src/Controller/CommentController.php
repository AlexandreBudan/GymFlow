<?php

namespace App\Controller;

use App\Entity\Exercise;
use App\Entity\CommentsExercise;
use App\Repository\CommentsExerciseRepository;
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

#[Route('/api/gym/{gym}/zone/{zone}/exercise/{exercise}/comment')]
#[OA\Tag(name: 'Comment')]
#[OA\Response(
    response: 400,
    description: 'Bad request'
)]
#[OA\Response(
    response: 401,
    description: 'Unauthorized'
)]
final class CommentController extends AbstractController
{
    private TagAwareCacheInterface $cache;

    public function __construct(TagAwareCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    #[Route('', name: 'comment.create', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Successful created response',
        content: null
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            example: [
                "comment" => "example",
                "grade" => 5
            ]
        )
    )]
    /**
     * Create Comment
     *
     * @param Exercise $exercise
     * @param Request $request
     * @param CommentsExerciseRepository $commentsExerciseRepository
     * @param SerializerInterfaceSymfo $serializerInterface
     * @return JsonResponse|null
     */
    public function createComment(Exercise $exercise, Request $request, CommentsExerciseRepository $commentsExerciseRepository, SerializerInterfaceSymfo $serializerInterface): JsonResponse
    {
        $comment = $serializerInterface->deserialize(
            $request->getContent(),
            CommentsExercise::class, 
            'json'
        );

        $comment->setExercise($exercise);
        $comment->initializeTimestamps();

        $commentsExerciseRepository->save($comment, true);
        $this->cache->invalidateTags(['exerciseCache', 'exercise_' . $exercise->getId()]);
        return new JsonResponse(null, Response::HTTP_CREATED, ["Comment" => $comment], true);
    }

    #[Route('/{comment}', name: 'comment.get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: CommentsExercise::class, groups: ['getOneComment'])
    )]
    /**
     * Get One Comment of a Exercise in detail
     *
     * @param Comment $comment
     * @param SerializerInterface $serializer
     * @return JsonResponse|null
     */
    public function getCommentInfos(CommentsExercise $comment, SerializerInterface $serializer): JsonResponse
    {
        $idCache = "getCommentInfos" . $comment->getId();
        $cacheRet = $this->cache->get($idCache, function (ItemInterface $item) use ($comment, $serializer) {
            $item->tag('comment_' . $comment->getId());

            return $serializer->serialize(
                $comment,
                'json',
                SerializationContext::create()->setGroups(['getOneComment'])
            );
        });

        return new JsonResponse($cacheRet, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/{comment}', name: 'comment.delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'Successful deleted response',
        content: null
    )]
    /**
     * Delete Comment
     *
     * @param Comment $comment
     * @param CommentExerciseRepository $commentExerciseRepository
     * @return JsonResponse|null
     */
    public function deleteComment(CommentsExercise $comment, CommentsExerciseRepository $commentExerciseRepository): JsonResponse
    {
        $comment->setStatus("inactive");
        $comment->updateTimestamp();
        $commentExerciseRepository->save($comment, true);
        $this->cache->invalidateTags(['comment_' . $comment->getId(), 'exercise_' . $comment->getExercise()->getId()]);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}
