<?php

include_once('kint.phar');

class notify_me_helper{

    public function __construct(){
    }


    public function requireToVar($file){
        ob_start();
        require($file);
        return ob_get_clean();
    }

}
