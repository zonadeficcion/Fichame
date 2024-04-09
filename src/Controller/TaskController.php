<?php
namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/task')]
class TaskController extends AbstractController
{

  #[Route('/seeAsignedTasks', name: 'app_seeAsignedTasks', methods: ['GET', 'POST'])]

    public function seeAsignedTasks(Request $request, TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findBy([
            'state' => 1,
            'user' => $this->getUser()
        ]
        );

        return $this->render('task/index.html.twig',[
            'tasks' => $tasks
        ]);

    }

    #[Route('/seeTaskToday', name: 'app_ seeTaskToday', methods: ['GET', 'POST'])]
    public function seeTaskToday(Request $request, TaskRepository $taskRepository): Response
    {

        /* Hoy de mañana
        $hoy= new \DateTime('2023-02-11');*/

        //hoy de hoy 
        $hoy = new \DateTime();
        $tomorrow = new \DateTime('2023-02-11');


        return $this->render('task/taskfull.html.twig', [
            'tasks' => $taskRepository->findAll(),
            'hoy' => $hoy,
            'tomorrow' => $tomorrow,

        ]);

    }

    #[Route('/', name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task, true);

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
            'totaltime' =>$task->getTotalTime(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task, true);

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $taskRepository->remove($task, true);
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit/{state_request}', name: 'app_task_edit_State_request', methods: ['GET', 'POST'])]
    public function editState_request(Request $request, int $state_request, Task $task, TaskRepository $taskRepository): Response
    {



        //El estado 1 es Aceptado
        //El estado 0 es Rechazado

        $fecha = new \DateTime();
        $task->setStateRequest($state_request);
        $task->setStatusResolveDate($fecha);

        $fecha= new \DateTime();
        $task-> setStatusResolveDate($fecha);

        $taskRepository->save($task, true);

        return $this->redirectToRoute('app_main', [], Response::HTTP_SEE_OTHER);


    }

    #[Route('/{id}/updateState', name: 'app_task_update_State', methods: ['GET', 'POST'])]
    public function UpdateState(Request $request, Task $task, TaskRepository $taskRepository): Response
    {

        //En state 1 es Asignado
        //En state NULL es no asignado

        $breakTime = $request->get("breakHours");
        if (!$breakTime)
            $breakTime = 0;

        $task->setBreakTime($breakTime);

        $fecha= new \DateTime();
        if($task->getStartTime()!=NULL){
            if ($task->getStartTime() != $task->getEvent()->getEndDate()){
                $taskRepository->createTomorrowTask($task->getEvent(),$this->getUser(),$task->getStartTime() );
            }
            $task->setEnd_Time($fecha);
        
        }else{
            $task->setStart_Time($fecha);   
            $task->setStartTimeCompare($fecha);

        }
        // $task->setStateRequest($state_request);
        $taskRepository->save($task, true);

        return $this->redirectToRoute('app_main', [], Response::HTTP_SEE_OTHER);

    }
    #[Route('/admin/task/{id}/resolve', name: 'app_task_resolve', methods: ["GET"])]
    public function resolve(Task $task, Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $state = $request->get("state");
        $coordinator = $request->get("coordinator");
        $driver = $request->get("driver");

        if(isset($state)){
            if($state == 1 || $state == 0){
                $task->setState($state);
                $entityManager->flush();
            }
        }
        if($coordinator && $driver){
            $task->setChore(["ROLE_DRIVER","ROLE_COORDINATOR"]);
            $entityManager->flush();
        } else if($coordinator){
            $task->setChore(["ROLE_COORDINATOR"]);
            $entityManager->flush();
        } else if($driver){
            $task->setChore(["ROLE_DRIVER"]);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_report', ["id" => $task->getEvent()->getId()], Response::HTTP_SEE_OTHER);
    }
}
