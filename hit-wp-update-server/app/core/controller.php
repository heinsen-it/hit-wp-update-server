<?php

namespace hitwpupdateserver\app\core;

class Controller extends app{

    protected $_view;

    public function __construct(){
        parent::__construct();
        $this->_view = new View();
    }

}