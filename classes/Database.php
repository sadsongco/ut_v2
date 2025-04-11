<?php

namespace Database;

class Database
{
    private $conn;
    function __construct($admin=false)
    {
        $path = $admin ? 'ut_a_connect.php' : 'ut_o_connect.php';
        include_once(base_path("../secure/scripts/$path"));
        $this->conn = $db;
    }

    public function query($query)
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function fetchAll($stmt)
    {
        return $stmt->fetchAll();
    }

    public function fetch($stmt)
    {
        return $stmt->fetch();
    }
}