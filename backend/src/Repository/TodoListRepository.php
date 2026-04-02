<?php

namespace App\Repository;

use App\Entity\TodoList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TodoListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoList::class);
    }

    public function findPaginatedByUser(User $user, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $query = $this->createQueryBuilder('t')
            ->where('t.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('t.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $items = $query->getQuery()->getResult();

        $total = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.owner = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return ['items' => $items, 'total' => (int) $total];
    }

    public function findPaginatedAll(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $items = $this->createQueryBuilder('t')
            ->orderBy('t.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $total = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return ['items' => $items, 'total' => (int) $total];
    }
}
