<?php

namespace hitwpupdateserver\app\core;

class app{

    public  $_url;

    private $_controller = null;

    public function __construct() {
        if(!extension_loaded('openssl'))
        {
            throw new \Exception('Diese Application benötigt OpenSSL. Bitte installiere die PHP-Erweiterung.');
        }
           // Setzt die URL
        $this->_getUrl();
    }

    public function init(){
     $this->_loadExistingController();
    }

    public function setController($name) {
        $this->_controller = $name;
    }

    private function _getUrl() {
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : NULL;
        $url = urlencode($url ?? '');
        $url = urldecode(htmlspecialchars($url));
        $this->_url = explode('/', $url);
    }


    private function _loadExistingController() {
        if(empty($this->_url[0])){
            $controllerName = 'Start';
        } else {
            $controllerName = $this->_url[0] ?? 'Start';
        }
       $actionName = $this->_url[1] ?? 'index';

        // Namespace-Prefix für Controller
        $namespacePrefix = MYNAMESPACE.'\\app\\controllers\\';

        // Vollqualifizierter Klassenname des Controllers
        $controllerClassName = $namespacePrefix . $controllerName;
        // Überprüfe, ob der Controller existiert
        if (class_exists($controllerClassName)) {
            $controller = new $controllerClassName();

            // Überprüfe, ob die Aktionsmethode existiert
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            } else {
              $this->myerror();
            }
        } else {
            $this->myerror();
        }

    }


    public function myerror(){
        http_response_code(404);
        header('Content-Type: application/json');
        $data['status'] = 'fail';
        $data['code'] = '404';
        $data['message'] = 'Hallo Start';
        echo json_encode($data);
    }


}