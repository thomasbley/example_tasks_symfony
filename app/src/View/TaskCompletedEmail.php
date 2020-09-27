<?php

namespace App\View;

use App\Helper\TemplatedEmail;
use App\Model\Customer;
use App\Model\Task;
use App\Model\View;

class TaskCompletedEmail extends View
{
    public string $template = __DIR__ . '/TaskCompletedEmailTemplate.php';

    public Customer $customer;

    public Task $task;

    public function getEmail(): TemplatedEmail
    {
        $email = new TemplatedEmail();
        $email->from('Task Service <task.service@invalid.local>');
        $email->subject(sprintf('Task #%s completed', $this->task->id));
        $email->to($this->customer->email);
        $email->html($email->renderView($this));

        return $email;
    }
}
