<?php

namespace App\Repository;

use App\Entity\Posts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Posts>
 *
 * @method Posts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Posts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Posts[]    findAll()
 * @method Posts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostsRepository extends ServiceEntityRepository {
  /**
   * Constructor to initialize the object.
   *
   * @param ManagerRegistry $registry
   *   ManagerRegistery containing object manager for the entity class.
   */
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Posts::class);
  }

  /**
   * Find entities based on the limit and offset passed.
   *
   * @param int $limit
   * @param int $offset
   * 
   * @return Doctrine\ORM\EntityRepository
   *  Return Repository of objects according to the query executed. 
   */
  public function findLimitedEntities(int $limit, int $offset)
  {
    return $this->createQueryBuilder('e')
      ->orderBy('e.id', 'DESC')
      ->setMaxResults($limit)
      ->setFirstResult($offset)
      ->getQuery()
      ->getResult();
  }
}
