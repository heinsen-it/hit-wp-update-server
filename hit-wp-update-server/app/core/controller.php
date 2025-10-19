<?php

namespace hitwpupdateserver\app\core;

use http\Exception;

class Controller extends app{

    protected $_view;

    protected $data;

    public function __construct(){
        parent::__construct();
        http_response_code(500);
        $this->data['state']['status'] = 'fail';
        $this->data['state']['message'] = 'Something went wrong';
        $this->data['state']['code'] = '500';

        $this->_view = new View();
    }

}