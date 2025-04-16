<?php

require_once("DbConnector.php");
$jv = new JsonToDBConverter();
// $jv->insertJsonIntoDb("test", "../data/spells.json");

class JsonToDBConverter 
{
    private $dbInstance;


    public function __construct() {
        $this->dbInstance = DbConector::singleton();
    }

    public function insertJsonIntoDb($tableName, $filePath) {
        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $json = json_decode($json);
            foreach ($json as $key => $spell) {
                # code...
                // if ($key == 0) {
                $this->dbInstance->insertSpell($spell);

                // }
            }
        }
    }
}
