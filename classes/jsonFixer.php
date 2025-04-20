<?php

$json = file_get_contents("../data/conjuros_mal copy.json");
$json = json_decode($json);
$aux = 0;
foreach ($json as $key => $spell) {
    // var_dump($spell->name);
    if (($spell->name) == "... (continúa)") {    
        $aux += 2;
        echo ($spell->name."-".$json[($key+$aux)]->name);
    }
    
    
    if (isset($json[$key+$aux])) {
        $spell->name = $json[($key+$aux)]->name;
    }
        
    

}

file_put_contents("../data/conjuros_mal copy.json", json_encode($json,JSON_UNESCAPED_UNICODE));

// var_dump($json[0]);

?>