<?php

namespace hitwpupdateserver\app\core;

class app{

    public  $_url;

    private $_controller = null;

    public function __construct() {
           // Setzt die URL
        $this->_getUrl();
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


}