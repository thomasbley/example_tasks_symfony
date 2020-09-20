<?php

namespace App\Repository;

use App\Model\Customer;
use App\Model\Task;
use App\Service\PdoManager;

class TasksRepository
{
    protected PdoManager $db;

    public function __construct(PdoManager $db)
    {
        $this->db = $db;
    }

    public function taskExists(Customer $customer, int $taskId): bool
    {
        $query = '
            SELECT id FROM task WHERE id = ? AND customer_id = ?
        ';
        $statement = $this->db->prepare($query);
        $statement->execute([$taskId, $customer->id]);

        return !empty($statement->fetchColumn());
    }

    public function createTask(Customer $customer, string $title, string $duedate): Task
    {
        $query = '
            INSERT INTO task SET customer_id = ?, title = ?, duedate = ?, completed = 0
        ';
        $statement = $this->db->prepare($query);
        $statement->execute([$customer->id, $title, $duedate]);

        $task = new Task();
        $task->id = (int) $this->db->lastInsertId();
        $task->title = $title;
        $task->duedate = $duedate;
        $task->completed = false;

        return $task;
    }

    public function updateTask(Task $task): void
    {
        $query = '
            UPDATE task SET title = ?, duedate = ?, completed = ? WHERE id = ?
        ';
        $statement = $this->db->prepare($query);
        $statement->execute([$task->title, $task->duedate, (int) $task->completed, $task->id]);
    }

    public function deleteTask(int $taskId): void
    {
        $query = '
            DELETE FROM task WHERE id = ?
        ';
        $this->db->prepare($query)->execute([$taskId]);
    }

    public function getTask(Customer $customer, int $taskId): ?Task
    {
        $query = '
            SELECT id, title, duedate, completed FROM task WHERE customer_id = ? AND id = ?
        ';
        $statement = $this->db->prepare($query);
        $statement->execute([$customer->id, $taskId]);

        return $statement->fetchObject(Task::class) ?: null;
    }

    /**
     * @return Task[]
     */
    public function getCurrentTasks(Customer $customer): array
    {
        $query = '
            SELECT id, title, duedate, completed FROM task
            WHERE customer_id = ? AND completed = 0 AND duedate < ?
            ORDER BY duedate
            LIMIT 500
        ';
        $statement = $this->db->prepare($query);
        $statement->execute([$customer->id, date('Y-m-d', strtotime('+1 week'))]);

        return $statement->fetchAll(PdoManager::FETCH_CLASS, Task::class);
    }

    /**
     * @return Task[]
     */
    public function getCompletedTasks(Customer $customer): array
    {
        $query = '
            SELECT id, title, duedate, completed FROM task
            WHERE customer_id = ? AND completed = 1
            ORDER BY duedate DESC
            LIMIT 500
        ';
        $statement = $this->db->prepare($query);
        $statement->execute([$customer->id]);

        return $statement->fetchAll(PdoManager::FETCH_CLASS, Task::class);
    }
}
