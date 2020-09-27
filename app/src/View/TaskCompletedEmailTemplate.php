<?php

/**
 * @var App\Helper\TemplatedEmail $this
 * @var App\View\TaskCompletedEmail $view
 */
$service = $this;

?>
<!DOCTYPE html>
<html>
    <body>
    	Task <b><?= $service->e($view->task->title); ?></b> completed!
    </body>
</html>