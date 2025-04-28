<?php

function getCategories($db) {
    $query = "SELECT DISTINCT category FROM Items";
    return $db->query($query)->fetchAll();
}