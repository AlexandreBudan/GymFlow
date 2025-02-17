<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Sauvegarde d'une entité User.
     *
     * @param User $entity
     * @param bool $flush
     */
    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Suppression d'une entité User.
     *
     * @param User $entity
     * @param bool $flush
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * 
     * @param PasswordAuthenticatedUserInterface $user
     * @param string                             $newHashedPassword
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Obtient l'utilisateur connecté.
     * 
     * @param UserInterface $userInterface
     * @return User
     */
    public function getUserConnected(UserInterface $userInterface): User
    {
        $user =  $this->findOneBy(["email" => $userInterface->getUserIdentifier()]);

        return $user;
    }

    /**
     * Vérifie les paramètres de la requête pour la création d'un nouvel utilisateur.
     * 
     * @param string $content
     * @throws BadRequestException
     */
    public function checkFormCreateUser(String $content)
    {
        $requestBody = json_decode($content);
        $requestBody->email ?? throw new BadRequestException("Missing email parameter for create a new User");
        $requestBody->password ?? throw new BadRequestException("Missing password parameter for create a new User");
        $requestBody->pseudo ?? throw new BadRequestException("Missing pseudo parameter for create a new User");
    }
}
