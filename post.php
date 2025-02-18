<?php

include_once "/var/www/private/utils/config.php";
include_once SCRIPTS_PATH . "phrases/add_phrase.php";
include_once SCRIPTS_PATH . "auth/auth_header_check.php";

allowCORS();

checkHeader();

$phrase = $_POST['phrase'];

addPhrase($phrase, "Unknown");

?>