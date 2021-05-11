<?php

//WE HAVE SQLITE3?
if (!extension_loaded('sqlite3')) {
    $msg = 'ERROR: dsijak/authman -> Sqlite3 extension not loaded!';
    error_log($msg);
    die($msg);
}

//CREATE NEW JWT SERVER KEY
$serverKeyFile = __DIR__ . '/serverKey.php';
if (!file_exists($serverKeyFile))
{
    $fp = fopen($serverKeyFile, "wb");
    fwrite($fp, '<?php define("DS_AM_SERVER_KEY", "'. bin2hex(openssl_random_pseudo_bytes(32)) .'");');
    fclose($fp);
    
    require_once($serverKeyFile);
}
else
{
    require_once($serverKeyFile);
}

//LOAD CONFIG
require_once(__DIR__ . '/consts.php');
require_once(__DIR__ . '/config.php');



