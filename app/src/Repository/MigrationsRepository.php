<?php

namespace App\Repository;

use App\Service\PdoManager;
use InvalidArgumentException;

class MigrationsRepository
{
    protected PdoManager $db;

    public function __construct(PdoManager $db)
    {
        $this->db = $db;
    }

    public function importSqlFile(string $file): void
    {
        if (!is_readable($file)) {
            throw new InvalidArgumentException('invalid file');
        }

        $this->db->exec(file_get_contents($file));

        $query = 'INSERT INTO migration SET filename = ?, created_at = now()';
        $this->db->prepare($query)->execute([basename($file)]);
    }

    public function isImported(string $filename): bool
    {
        $query = "SHOW tables LIKE 'migration'";
        $result = $this->db->query($query)->fetch();
        if (empty($result)) {
            return false;
        }

        $query = 'SELECT filename FROM migration WHERE filename = ?';
        $statement = $this->db->prepare($query);
        $statement->execute([$filename]);

        return $statement->rowCount() ? true : false;
    }
}
