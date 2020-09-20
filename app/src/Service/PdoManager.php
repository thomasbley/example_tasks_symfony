<?php

namespace App\Service;

use PDO;

class PdoManager extends PDO
{
    public function __construct(string $dsn, string $username, string $password)
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        parent::__construct($dsn, $username, $password, $options);
    }
}
