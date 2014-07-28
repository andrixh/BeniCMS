<?php
namespace Page;

use Response;
use Request;
use Config;

class PhpInfo extends \SystemPage {

    public function output(){
        phpinfo();
    }

}

