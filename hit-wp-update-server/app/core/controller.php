<?php

namespace hitwpupdateserver\app\core;

class Controller{

    protected $_view;

    public function __construct(){

        $this->_view = new View();
    }

}