<?php

function getCategories($db) {
    $query = "SELECT DISTINCT category FROM Items";
    $categories = $db->query($query)->fetchAll();
    $categories[] = ["category"=>"All"];
    return $categories;
}