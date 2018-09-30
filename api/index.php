<?php
header("Access-Control-Allow-Origin: *");
include 'iformbuilder_api.php';
$instance = new iformbuilder_api();
$postdata = json_decode(file_get_contents("php://input"));
$token =  $instance->getToken();
$id = $instance->saveData($postdata,$token);

if(is_int($id->id)==true)
echo json_encode(array('msg'=>'success','id'=>$id->id));
else
echo json_encode(array('msg'=>$id));

