<?php

namespace App\Repository;

use App\Entity\Event;
use App\Repository\EventCategoryRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Repository\TaskRepository;


/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush)
        {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush)
        {
            $this->getEntityManager()->flush();
        }
    }

    public function createEventAlmacen(User $user, TaskRepository $taskRepository, EventCategoryRepository $eventCategoryRepository)
    {
        
        $event = new Event();
        $event->setName('Almacén');
        $event->setStartDate(new \DateTime());
        $event->setEndDate(new \DateTime());
        $event->setSchedule("");
        $event->setLinkInformation("");
        $event->setWorkersNumber(1);
        $event->setCompany($user->getCompany());

        //Hay que definir cual es la categoria de almacen
        $event->setCategory($eventCategoryRepository->find(1));
        
        $this->save($event, true);
        $task = $taskRepository->createAsignedTask($event, $user);
        $task->setType(1);
        $taskRepository->save($task,true);
        return $task->getId();
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}