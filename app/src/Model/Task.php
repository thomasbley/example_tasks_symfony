<?php

namespace App\Model;

/**
 * @OA\Schema()
 */
class Task
{
    /**
     * @OA\Property(property="id", type="integer", description="ID", example=42)
     */
    public int $id;

    /**
     * @OA\Property(property="title", type="string", description="Title", example="My Task")
     */
    public string $title;

    /**
     * @OA\Property(property="duedate", type="string", format="date", description="Due date", example="2020-01-01")
     */
    public string $duedate;

    /**
     * @OA\Property(property="completed", type="boolean", description="Completed", example=false)
     */
    public bool $completed;
}
