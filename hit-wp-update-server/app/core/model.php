<?php
namespace hitwpupdateserver\app\core;

use hitwpupdateserver\app\core\database;
class model {

    protected $_db = null;

    public function __construct(){
        $this->_db = new Database(DB_HOST,DB_USER,DB_PASS,DB_NAME);
    }

}
