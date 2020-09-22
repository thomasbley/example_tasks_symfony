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
use App\Model\Task;

class TasksController extends AbstractController
{
    protected TasksRepository $repo;

    protected TasksSerializer $serializer;

    public function __construct(TasksRepository $repo, TasksSerializer $serializer)
    {
        $this->repo = $repo;
        $this->serializer = $serializer;
    }

    public function getTask(Customer $customer, int $taskId): JsonResponse
    {
        $task = $this->repo->getTask($customer, $taskId);

        if (empty($task)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'task not found');
        }

        $data = $this->serializer->serializeTask($task);

        return $this->json($data, Response::HTTP_OK);
    }

    public function getTasks(Customer $customer, string $completed): JsonResponse
    {
        if (!empty($completed)) {
            return $this->getCompletedTasks($customer);
        }

        return $this->getCurrentTasks($customer);
    }

    public function getCurrentTasks(Customer $customer): JsonResponse
    {
        $tasks = $this->repo->getCurrentTasks($customer);

        $data = $this->serializer->serializeTasks($tasks);

        return $this->json($data, Response::HTTP_OK);
    }

    public function getCompletedTasks(Customer $customer): JsonResponse
    {
        $tasks = $this->repo->getCompletedTasks($customer);

        $data = $this->serializer->serializeTasks($tasks);

        return $this->json($data, Response::HTTP_OK);
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

    public function updateTask(Customer $customer, int $taskId, string $title, string $duedate, string $completed): JsonResponse
    {
        $task = new Task();
        $task->id = $taskId;
        $task->title = $title;
        $task->duedate = $duedate;
        $task->completed = (bool) $completed;

        if (empty($task->title)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'missing title');
        }
        if (!DateTime::createFromFormat('Y-m-d', $task->duedate)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'invalid duedate');
        }

        if (!$this->repo->taskExists($customer, $task->id)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'task not found');
        }

        $this->repo->updateTask($task);

        // TODO implement update notification

        $data = $this->serializer->serializeTask($task);

        return $this->json($data, Response::HTTP_OK);
    }

    public function deleteTask(Customer $customer, int $taskId): Response
    {
        if (!$this->repo->taskExists($customer, $taskId)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'task not found');
        }

        $this->repo->deleteTask($taskId);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
