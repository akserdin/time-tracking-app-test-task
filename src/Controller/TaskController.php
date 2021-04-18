<?php
/*
 * Created by Dmytro Zolotar. on 17/04/2021.
 * Copyright (c) 2021. All rights reserved.
 */

namespace App\Controller;


use App\Entity\Task;
use App\Service\TaskStoreService;
use App\Traits\HandleErrorsTrait;
use Dompdf\Dompdf;
use Dompdf\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    use HandleErrorsTrait;

    /**
     * @Route("/", name="app_home")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function mainPage()
    {
        return $this->render('task/index.html.twig');
    }

    /**
     * @Route("/task", name="app_task_list", methods={"GET","HEAD"})
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $repository = $this->getDoctrine()->getRepository(Task::class);

        return $this->json(
            $repository->userTasksPaginated($this->getUser()->getId(), $request)
        );
    }

    /**
     * @Route("/task", name="app_task_store", methods={"POST"})
     * @param Request $request
     * @param TaskStoreService $taskStoreService
     * @return JsonResponse
     */
    public function store(Request $request, TaskStoreService $taskStoreService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->validateTaskStore($data);

        if ($this->hasErrors()) {
            return $this->json(['errors' => $this->errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $taskStoreService->storeUserTask($this->getUser(), $data);

        return $this->json('OK');
    }

    /**
     * @Route("/download-pdf", name="app_tasks_pdf", methods={"GET"})
     * @return mixed
     */
    public function downloadPdf(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Task::class);
        $tasks = $repository->userTasks($this->getUser()->getId(), $request);

        if (empty($tasks)) {
            return new Response('No tasks for such criteria');
        }

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('pdf/index.html.twig', [
            'tasks' => $tasks,
            'total' => $this->getTotalTimeSpent($tasks)
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("tasks.pdf", [
            "Attachment" => false
        ]);
    }

    private function getTotalTimeSpent($tasks): int
    {
        $total = 0;

        foreach ($tasks as $task) {
            // $total += $task->getTimeSpent();
            $total += $task['timeSpent'];
        }

        return $total;
    }

    private function validateTaskStore(array $data)
    {
        if (strlen($data['title']) < 6 || strlen($data['title']) > 200) {
            $this->errors['title'] = 'Title must be from 6 to 200 characters long';
        }

        if ($data['timeSpent'] < 1) {
            $this->errors['timeSpent'] = 'Time spent must be at least 1';
        }

        if (! is_numeric($data['timeSpent'])) {
            $this->errors['timeSpent'] = 'Time spent must integer';
        }
    }
}
