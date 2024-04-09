<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/booking')]
class BookingController extends AbstractController
{
    #[Route('/', name: 'app_booking_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        $tasks = [];
        foreach ($taskRepository->findByPending($this->getUser()) as $task)
        {
            $tasks['yellow'][] = $task;
        }
        foreach ($taskRepository->findByAccepted($this->getUser()) as $task)
        {
            $tasks['gray'][] = $task;
        }

        $tasks = [];
        foreach ($taskRepository->findByAssigned($this->getUser()) as $task)
        {
            $tasks['green'][] = $task;
        }
        return $this->render('booking/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }
}