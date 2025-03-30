<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ReflectionClass;

class GenericEntityValueResolver implements ValueResolverInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $entityClass = $argument->getType();

        // Vérifie si la classe existe et si c'est une entité Doctrine
        if (!$entityClass || !class_exists($entityClass)) {
            return [];
        }

        $reflection = new ReflectionClass($entityClass);
        if (!$reflection->isSubclassOf('Doctrine\ORM\Mapping\Entity')) {
            return [];
        }

        // Trouve l'ID correct basé sur le nom de l'entité dans l'URL
        $routeParam = array_keys($request->attributes->all())[0]; // Premier paramètre dynamique de la route
        $id = $request->attributes->get($routeParam);

        if (!$id) {
            return [];
        }

        // Récupère l'entité demandée
        $entity = $this->entityManager->getRepository($entityClass)->find($id);
        if (!$entity) {
            throw new NotFoundHttpException("$entityClass not found");
        }

        return [$entity];
    }
}
