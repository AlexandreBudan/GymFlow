<?php

namespace App\Controller;

use App\Entity\CustomMedia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/', name: 'api_media_')]
#[OA\Tag(name: 'Media')]
#[OA\Response(
    response: 400,
    description: 'Bad request'
)]
#[OA\Response(
    response: 401,
    description: 'Unauthorized'
)]
final class MediaController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/MediaController.php',
        ]);
    }

    #[Route('/api/media', name: 'create', methods: ["POST"])]
    #[OA\Response(
        response: 201,
        description: 'Successful created response',
        content: null
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "media",
                        type: "string",
                        format: "binary"
                    )
                ]
            )
        )
    )]
    public function createMedia(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $file = $request->files->get("media");

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/docs/medias';

        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
            return new JsonResponse(['error' => 'Failed to create upload directory'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $newFilename = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($uploadDirectory, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'File upload error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $media = new CustomMedia();
        $mediaFile = new \Symfony\Component\HttpFoundation\File\File($uploadDirectory . '/' . $newFilename);
        $media->setMedia($mediaFile);
        $media->setPublicPath('/public/docs/medias');
        $media->setRealPath($newFilename);
        $media->setRealname($file->getClientOriginalName());

        $entityManager->persist($media);
        $entityManager->flush();

        $jsonMedia = $serializer->serialize($media, "json");

        $location = $urlGenerator->generate("api_media_get", ["id" => $media->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonMedia, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/media/{id}', name: 'get', methods: ["GET"])]
    public function getMedia(CustomMedia $media, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        if (!$media) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $publicPath = $urlGenerator->generate("api_media_index", [], UrlGeneratorInterface::ABSOLUTE_URL);
        $fileUrl = $publicPath . str_replace("/public/", "", $media->getPublicPath()) . "/" . $media->getRealPath();

        $jsonMedia = $serializer->serialize($media, "json");

        return new JsonResponse($jsonMedia, Response::HTTP_OK, ["Location" => $fileUrl], true);
    }
}
