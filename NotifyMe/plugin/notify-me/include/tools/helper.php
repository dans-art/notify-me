<?php

include_once('kint.phar');

class notify_me_helper{

    public function __construct(){
    }

    /**
     * Gets a file and transport some data to use.
     *
     * @param [type] $file
     * @param array $data
     * @return void
     */
    public function load_template($file, $data = array()){
        ob_start();
        require($file);
        return ob_get_clean();
    }


}
