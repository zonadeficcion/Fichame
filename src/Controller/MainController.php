<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\Role;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(TaskRepository $taskRepository, UserRepository $userRepository): Response
    {
      $user = $this->getUser();
      if ($user)
        {
          $date= new \DateTime();
          $now=$date->format('Y-m-17'); 
            $user = $this->getUser();
            $user->setLastLogin(new \DateTime());
            $userRepository->save($user, true);
            $user->changeRemainingHours();
            return $this->render('main/index.html.twig', [

                'tasks' => $taskRepository->showPendingTasksByUser($user),
                'taskAsignments' => $taskRepository->showAsignByUserUncompleted($user),
                'now' => $now,
              
            ]);


        }
        else
        {


            return $this->render('main/index.html.twig', [
                'controller_name' => 'MainController',
            ]);

        }
    }
}
