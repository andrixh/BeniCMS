<?php
class SystemPage extends Page {

    public function __construct(){
       HtmlCache::off();
    }

}