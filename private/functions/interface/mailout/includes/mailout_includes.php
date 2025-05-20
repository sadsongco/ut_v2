<?php

function getMailoutData($id, $db) {
    try {
        $query = "SELECT * FROM mailouts WHERE id = ?";
        return $db->query($query, [$id])->fetch();
    }
    catch (PDOException $e) {
        throw new Exception("Problem retrieving mailout data: ".$e->getMessage());
    }
}

function getCurrentMailout($db, $id)
{
    try {
        $query = "SELECT DATE_FORMAT(date, '%Y%m%d') AS `date` FROM mailouts WHERE id = ?";
        $result = $db->query($query, [$id])->fetch();
        return $result['date'];
    } catch (PDOException $e) {
        exit("Couldn't retrieve current mailout: ".$e->getMessage());
    }
}
