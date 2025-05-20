<?php

define("DOWNLOAD_URL", getHost()."/API/download.php");

function generateDownloadLink($email, $email_id) {
    return false;
    $u_token = makeUniqueToken($email_id, $email);
    return DOWNLOAD_URL."?email=".$email."&u_token=".$u_token;
}