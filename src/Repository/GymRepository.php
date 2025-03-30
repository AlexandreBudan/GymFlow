<?php

namespace App\Repository;

use App\Entity\Gym;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Gym>
 */
class GymRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gym::class);
    }

    /**
     * Sauvegarde d'une entité Gym.
     *
     * @param Gym $entity
     * @param bool $flush
     */
    public function save(Gym $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Suppression d'une entité Gym.
     *
     * @param Gym $entity
     * @param bool $flush
     */
    public function remove(Gym $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Obtient les gyms en fonction des filtres.
     *
     * @return array
     */
    public function settingsManagement(
        Request $request
    ) {

        $data = $request->query->all();

        $page = 1;
        if (isset($data['page']) && is_numeric($data['page']) && (int)$data['page'] > 1) {
            $page = (int)$data['page'];
        }

        $limit = 10;
        if (isset($data['limit']) && is_numeric($data['limit']) && (int)$data['limit'] >= 1) {
            $limit = (int)$data['limit'];
        }

        $search = "";
        if (isset($data['search'])) {
            $search = $data['search'];
        }

        $location = "";
        if (isset($data['location'])) {
            $location = $data['location'];
        }

        return array($page, $limit, $location, $search);
    }

    /**
     * Obtient tous les gyms par pagination.
     *
     * @param int    $page
     * @param int    $limit
     * @param string  $location
     * @param string $search
     *
     * @return Gym[]
     */
    public function findAllByPagination(
        int $page = 1,
        int $limit = 10,
        string $location = "",
        string $search = ""
    ) {
        $qb = $this->createQueryBuilder('g')
            ->leftJoin('g.address', 'a')
            ->andWhere('g.name LIKE :name')
            ->andWhere('a.city LIKE :location')
            ->setParameter('name', '%' . $search . '%')
            ->setParameter('location', '%' . $location . '%')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Gym[] Returns an array of Gym objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Gym
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
