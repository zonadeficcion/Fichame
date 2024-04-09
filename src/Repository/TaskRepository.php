<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush)
        {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush)
        {
            $this->getEntityManager()->flush();
        }
    }

    public function createTask(Event $event, User $user): void
    {
        $task = new Task();
        $task->setUser($user);
        $task->setEvent($event);
        $task->setType(0);
        $task->setStartTimeCompare($event->getStartDate());

        if ($task->getUser()->getRoles() == "ROLE_ALMACEN")
        {
            $task->setType(1);
            $task->getStartTime();
        }

        $this->save($task, true);
    }

    public function createAsignedTask(Event $event, User $user): Task
    {

        $task = new Task();
        $task->setUser($user);
        $task->setEvent($event);
        $task->setStateRequest(1);
        $task->setState(1);
        $task->setType(0);
        $this->save($task, true);
        return $task;
    }


    /**
     * @return Task[] Returns an array of Task objects
     */
    public function showPendingTasksByUser(User $user): array
    {
        //Esto es para el state_request
        $userId = $user->getId();

        return $this->createQueryBuilder('task')
            ->andWhere('task.state_request is NULL and task.user=:userId')
            ->setParameter('userId', $userId)
            ->getQuery()
           ->getResult()
       ;
   }
   public function getHorasMensuales( $value, $value2): array
   {
        $tot = $value . '-' . $value2 . '%';
       return $this->createQueryBuilder('t')
           ->andWhere("t.start_time LIKE :val")
           ->setParameter('val', $tot)
           ->orderBy('t.id', 'ASC')
           ->select('t.id as id , t.start_time as st , t.end_time as et, t.extra_time as ext')
           ->getQuery()
           ->getResult()
       ;
   }
   public function getHorasRealizadas($arr){
    $salida = [];
    foreach($arr as $task) {
        if($task['st'] && $task['et']){
          $time = $task['st']->diff($task['et']);
          $salida = [$task['id'], $time->days*24+$time->h+($task['ext']*60)/(3600)];
        } 
      }
      return $salida;  
   }

   public function findByMonth(int $month, int $userid): Array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM task
            WHERE MONTH(start_time) = :month && user_id = :userid
            ORDER BY start_time ASC
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['month' => $month, 'userid' => $userid]);

         // returns an array of arrays (raw data set)
         return $resultSet->fetchAllAssociative();
    }

    public function findByDate($value, $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.end_time LIKE :val AND t.user = :uid')
            ->setParameter('val', $value.'%')
            ->setParameter('uid', $user->getId())
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function showAcceptedTasksByUser(User $user): array
    {
        //Esto es para el state_request
        $userId = $user->getId();

        return $this->createQueryBuilder('task')
            ->andWhere('task.state_request=1 and task.user=:userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }




    public function showAsignByUser(User $user): array
    {
        //Esto es para el state
        $userId = $user->getId();

        return $this->createQueryBuilder('task')
            ->andWhere('task.state_request=1 and task.state=1 and task.user=:userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function showAsignByUserUncompleted(User $user): array
    {
        //Esto es para el state
        $userId = $user->getId();
        $date = new \DateTime();
        $now=$date->format('Y-m-d'); 

        //comprobamos si hay alguna tarea comenzada
        $return = $this->createQueryBuilder('task')
            ->andWhere('task.state_request=1 and task.state=1 and task.user=:userId and task.end_time is NULL and task.start_time is not NULL')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
        if (count($return) > 0)
        {
            return $return;
        }
        else
        {
            //si no hay ninguna comenzada
            //task.state_request=1 and task.state=1 and task.User=:userId and e.start_time>= :date_start and task.end_time is NULL
            return $this->createQueryBuilder('task')
            ->join('task.Event', 'e')
            ->andWhere('task.state_request=1 and task.state=1 and task.user=:userId and task.startTimeCompare LIKE :date and e.endDate >= :date2 and task.end_time is NULL')
            ->setParameter('userId', $userId)
            ->setParameter('date', $now."%")
            ->setParameter('date2', $now)
            ->getQuery()
            ->getResult()
            ;
        }

    }

    public function findByUser(User $user): array
    {
        //Esto es para el state
        $userId = $user->getId();

        return $this->createQueryBuilder('task')
            ->andWhere('task.user=:userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByPending(User $user): array
    {
        return $this->showPendingTasksByUser($user);
    }
    public function findByAssigned(User $user): array
    {
        return $this->showAsignByUser($user);
    }

    public function findByAccepted(User $user): array
    {
        return $this->showAcceptedTasksByUser($user);
    }

    //    public function showAsignByUser(User $user): array
//    {

    //     $userId=$user->getId();

    //        return $this->createQueryBuilder('task')
//            ->andWhere('task.state_request=3 and task.User=:userId and task.state=:state')
//            ->setParameter('userId', $userId)
//            ->setParameter('state', 1)
//            ->getQuery()
//            ->getResult()
//        ;
//    }


    //    public function showAsignByUser(User $user): array
//    {

    //     $userId=$user->getId();

    //        return $this->createQueryBuilder('task')
//            ->andWhere('task.state_request=1 and task.User=:userId')
//            ->setParameter('userId', $userId)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function showAsignByUser(User $user): array
//    {

    //     $userId=$user->getId();

    //        return $this->createQueryBuilder('task')
//            ->andWhere('task.state_request=3 and task.User=:userId and task.state=:state')
//            ->setParameter('userId', $userId)
//            ->setParameter('state', 1)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    /**
     * @return Task[] Returns an array of Task objects
     */
    public function showAsignTasksByUser(User $user): array
    {

        $userId = $user->getId();

        return $this->createQueryBuilder('task')
            ->andWhere('task.state_request=1 and task.state=1 and task.user=:userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }



    public function createTomorrowTask(Event $event, User $user, \DatetimeInterface $date): void
    {

      $date->modify('+1 day');
      $task = new Task();
      $task->setUser($user);
      $task->setState(1);
      $task->setStateRequest(1);
      $task->setStartTimeCompare($date);
      $task->setEvent($event);
      $task -> setType(0);


      $this->save($task, true);
  }

}
