<?php

namespace App\Controller;

use App\Model\Customer;
use App\Model\Task;
use App\Repository\TasksRepository;
use App\Serializer\TasksSerializer;
use App\View\TaskCompletedEmail;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @OA\Server(url="http://127.0.0.1:8080/", description="Development Server")
 * @OA\Info(title="PHP / Symfony example Tasks REST API", version="1.0")
 * @OA\SecurityScheme(
 *      securityScheme="bearer",
 *      type="http",
 *      scheme="bearer"
 * )
 */
class TasksController extends AbstractController
{
    protected TasksRepository $repo;

    protected TasksSerializer $serializer;

    protected MailerInterface $mailer;

    public function __construct(TasksRepository $repo, TasksSerializer $serializer, MailerInterface $mailer)
    {
        $this->repo = $repo;
        $this->serializer = $serializer;
        $this->mailer = $mailer;
    }

    /**
     * @OA\Get(
     *     path="/v1/tasks/{taskId}",
     *     description="get Task by ID",
     *     security={{"bearer": {}}},
     *     operationId="",
     *     @OA\Parameter(in="path", name="taskId", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="success",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="task not found"
     *     )
     * )
     */
    public function getTask(Customer $customer, int $taskId): JsonResponse
    {
        $task = $this->repo->getTask($customer, $taskId);

        if (empty($task)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'task not found');
        }

        $data = $this->serializer->serializeTask($task);

        return $this->json($data, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/v1/tasks",
     *     description="get (un-)completed Tasks",
     *     security={{"bearer": {}}},
     *     @OA\Parameter(in="query", name="completed", required=false, @OA\Schema(type="boolean")),
     *     @OA\Response(
     *         response="200",
     *         description="success",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Task"))
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="unauthorized"
     *     )
     * )
     */
    public function getTasks(Customer $customer, string $completed): JsonResponse
    {
        if (!empty($completed)) {
            $tasks = $this->repo->getCompletedTasks($customer);
        } else {
            $tasks = $this->repo->getCurrentTasks($customer);
        }

        $data = $this->serializer->serializeTasks($tasks);

        return $this->json($data, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/v1/tasks",
     *     description="create new Task",
     *     security={{"bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="My Task"),
     *             @OA\Property(property="duedate", type="string", format="date", example="2020-01-01"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="success",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="missing title"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="unauthorized"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/v1/tasks/{taskId}",
     *     description="update Task by ID",
     *     security={{"bearer": {}}},
     *     @OA\Parameter(in="path", name="taskId", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="My Task"),
     *             @OA\Property(property="duedate", type="string", format="date", example="2020-01-01"),
     *             @OA\Property(property="completed", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="success",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="missing title"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="task not found"
     *     )
     * )
     */
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

        if ($task->completed) {
            $email = new TaskCompletedEmail();
            $email->customer = $customer;
            $email->task = $task;

            $this->mailer->send($email->getEmail());
        }

        $data = $this->serializer->serializeTask($task);

        return $this->json($data, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/v1/tasks/{taskId}",
     *     description="delete Task by ID",
     *     security={{"bearer": {}}},
     *     @OA\Parameter(in="path", name="taskId", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="204",
     *         description="success"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="task not found"
     *     )
     * )
     */
    public function deleteTask(Customer $customer, int $taskId): Response
    {
        if (!$this->repo->taskExists($customer, $taskId)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'task not found');
        }

        $this->repo->deleteTask($taskId);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
