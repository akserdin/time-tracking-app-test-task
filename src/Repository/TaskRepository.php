<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    const PER_PAGE = 3;

    private EntityManagerInterface $em;
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->em = $em;
        $this->paginator = $paginator;

        parent::__construct($registry, Task::class);
    }

    public function userTasksPaginated(int $userId, Request $request): array
    {
        $page = $request->query->getInt('page', 1);

        $qb = $this->createQueryBuilder('t')
            ->select('t.id, t.title, t.comment, t.timeSpent, t.createdAt')
            ->innerJoin('t.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId);

        $this->applyDateRangeFilters($request, $qb);

        $qb->orderBy('t.id', 'DESC');

        /** @var PaginatorInterface $paginator */
        $paginator = $this->paginator->paginate($qb->getQuery(), $page,self::PER_PAGE);
        $total = $paginator->getTotalItemCount();
        $currentPage = $paginator->getCurrentPageNumber();

        $hasMorePages = $currentPage*self::PER_PAGE < $total;

        return [
            'items' => $paginator->getItems(),
            'currentPage' => $currentPage,
            'total' => $total,
            'hasMorePages' => $hasMorePages
        ];
    }

    public function userTasks(int $userId, Request $request)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t')
            ->innerJoin('t.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId);

        $this->applyDateRangeFilters($request, $qb);

        return $qb->orderBy('t.id', 'DESC')->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    private function applyDateRangeFilters(Request $request, QueryBuilder $qb)
    {
        if ($request->query->has('minDate')) {
            $qb->andWhere('t.createdAt >= :minDate')
                ->setParameter('minDate', $request->query->get('minDate'));
        }

        if ($request->query->has('maxDate')) {
            $qb->andWhere('t.createdAt <= :maxDate')
                ->setParameter('maxDate', $request->query->get('maxDate'));
        }
    }
}
