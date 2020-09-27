<?php

namespace App\Helper;

use App\Model\View;
use InvalidArgumentException;
use Symfony\Component\Mime\Email;

class TemplatedEmail extends Email
{
    /**
     * @psalm-suppress PossiblyUnusedParam
     * @psalm-suppress UnresolvableInclude
     */
    public function renderView(View $view): string
    {
        if (empty($view->template)) {
            throw new InvalidArgumentException('missing template');
        }

        ob_start();

        require $view->template;

        return trim(ob_get_clean());
    }

    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
    }
}
