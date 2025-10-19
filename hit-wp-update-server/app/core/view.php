<?php
namespace hitwpupdateserver\app\core;

class view {



    // Funktion um die Views zu laden.
    public function render($path, $data = false, $error = false) {
        $path = DOCROOT."/app/views/$path.php";
        if(file_exists($path))
        {
            require $path;
        }
        else
        {
          echo  'Dieses View('.$path.') ist leer und automatisch generiert.</br>';
          die;

        }
    }

    public function json($path, $data = false, $error = false) {
        $path = DOCROOT."/app/views/$path.json";
        if(file_exists($path))
        {
            $json = file_get_contents($path);
            header('Content-Type: application/json');
            echo $json;
        }
        else
        {
            echo  'Dieses View('.$path.') ist leer und automatisch generiert.</br>';
            die;
        }
    }

}
