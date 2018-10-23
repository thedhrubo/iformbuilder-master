<?php
header("Access-Control-Allow-Origin: *");
$path = explode('/', $_SERVER['PATH_INFO']);
if (isset($path[1]))
    $class = $path[1];
else {
    echo 'please enter any class name';
    exit;
}
if (isset($path[2]))
    $function = $path[2];
else {
    echo 'please enter any function';
    exit;
}
$file = $class . '.php';
include $file;
$instance = new $class;
echo $instance->$function();

//$ch = curl_init();
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($ch, CURLOPT_URL, "https://app.iformbuilder.com/exzact/api/v60/profiles/479916");
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//curl_setopt($ch, CURLOPT_HEADER, FALSE);

//curl_setopt($ch, CURLOPT_HTTPHEADER, array(
 //"Authorization: Bearer $token"
//));

//$response = curl_exec($ch);
//curl_close($ch);

//echo $response;
