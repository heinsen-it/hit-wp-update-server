<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
ini_set('error_log', 'php-errors.log');
ob_start();
define( 'KB_IN_BYTES', 1024 );

require '../config.php';
require '../autoload.php';

use hitwpupdateserver\app\core\app;



try {
    $app = new app();
    $app->setController('start');
    $app->init();
} catch(\Exception $e){
    http_response_code(500);
    header('Content-Type: application/json');
    $data['status'] = 'fail';
    $data['code'] = '500';
    $data['message'] = $e->getMessage();
    echo json_encode($data);

}

?>