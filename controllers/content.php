<?php

include(base_path("functions/interface/blog/get_article_media.php"));

define("IMAGE_UPLOAD_PATH", "/assets/images/article_images/");

use Database\Database;
$db = new Database('content');

function getTabs($db) {
    try {
        $query = "SELECT * FROM tabs ORDER BY tab_id ASC;";
        return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        throw new Exception ($e->getMessage());
    }
}

function getArticles($db, $tab_id) {
    try {
        $query = "SELECT article_id,
                        title
                    FROM articles
                    WHERE tab = ?
                    AND draft = 0
                    AND added <= NOW()
                    ORDER BY added DESC;";
        return $db->query($query, [$tab_id])->fetchAll();
    }
    catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
}

function getArticle($db, $article_id) {
    try {
        $query = "SELECT * FROM articles WHERE article_id = ?;";
        return $db->query($query, [$article_id])->fetch();
    }
    catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
}

$host = getHost();

if (!$paths) {
    $show_tab = 1;
} else {
    $show_tab = $paths[0];
}


try {
    $tabs = getTabs($db);
    foreach ($tabs as &$tab) {
        if ($tab['tab_id'] == $show_tab) {
            $tab['active'] = 'active';
        }
    }
    $articles = getArticles($db, $show_tab);
    if (!$paths || !isset($paths[1])) {
        $show_article = $articles[0]['article_id'];
    } else {
        $show_article = $paths[1];
    }
    $linked_articles = [];
    foreach ($articles as $article) {
        if ($article['article_id'] != $show_article) {
            $linked_articles[] = $article;
        }
    }
    $article = getArticle($db, $show_article);
    $auth = [];
    $article['body'] = parseBody($article['body'], $db, $auth, $this->renderer, $host);
}
catch (Exception $e) {
    die ("System error: ".$e->getMessage());
}

$blog_stylesheets = ['articles'];

echo $this->renderer->render('blog', ['tabs' => $tabs, 'tab_id'=>$show_tab, 'path'=>$path, 'article'=>$article, 'linked_articles'=>$linked_articles, 'nav' => $this->nav, 'stylesheets' => $blog_stylesheets]);
