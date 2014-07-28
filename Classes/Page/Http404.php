<?php
namespace Page;

use Response;

class Http404 extends \SystemPage {

    public function output(){
        Response::setStatus(Response::STATUS_404);
        Response::Output('404 error page');
    }

}