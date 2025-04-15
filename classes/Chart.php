<?php

require_once("classes/DbConnector.php");
//var_dump($_POST);
$db = DbConector::singleton();

class Chart {
    private $width = 0;
    private $height = 0;
    private $chars = [];
    private $entitiesOrder = [];

    private $backgroundImage; 
    
    function __construct($id_partida, $chars, $width, $height) {

    }
}


