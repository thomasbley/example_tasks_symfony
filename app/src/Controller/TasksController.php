<?php

namespace App\Controller;

use App\Model\Customer;
use App\Repository\TasksRepository;
use App\Serializer\TasksSerializer;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TasksController extends AbstractController
{
    protected TasksRepository $repo;

    protected TasksSerializer $serializer;

    public function __construct(TasksRepository $repo, TasksSerializer $serializer)
    {
        $this->repo = $repo;
        $this->serializer = $serializer;
    }

    public function createTask(Customer $customer, string $title, string $duedate): JsonResponse
    {
        if (empty($title)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'missing title');
        }

        if (!DateTime::createFromFormat('Y-m-d', $duedate)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'invalid duedate');
        }

        $task = $this->repo->createTask($customer, $title, $duedate);

        $location = sprintf('/v1/tasks/%s', $task->id);

        $data = $this->serializer->serializeTask($task);

        return $this->json($data, Response::HTTP_CREATED, ['Location' => $location]);
    }
}
