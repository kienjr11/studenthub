<?php
declare(strict_types=1);

abstract class MoHinh
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = CoSoDuLieu::pdo();
    }
}
