<?php 

//}require_once($_SERVER["DOCUMENT_ROOT"]."/storage/data.php");

class DbConector {
    
    private const DB_DATA = "mysql:host=localhost;dbname=deeprol";
    private const USERNAME = "root" ;
    private const PASSWD = "";
    private $db = "";

    // Hold an instance of the class
    private static $instance;

    // The singleton method
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->db = new PDO($this::DB_DATA, $this::USERNAME, $this::PASSWD);
    }

    public function checkLogin($user, $passwd) {
        try {
            $consulta = $this->db->prepare("select ID_usuario, username, password from usuario where username = :username");
            
            $consulta->bindParam(":username",$user, PDO::PARAM_STR);
            
            $results = $consulta->execute();
            $data = $consulta->fetch(PDO::FETCH_ASSOC);
            
            if ($data["password"] == $passwd) {
                return $data["ID_usuario"];
            }

            return false;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        
        return false;
    }

    
    public function signIn($parentId) {
        try {
            // $consulta = $this->db->prepare("select ID_usuario, NombreUsuario, Contraseña, Correo, Telefono, rutaImagenPerfil from Usuario where NombreUsuario = :username");
            $consulta = $this->db->prepare("SELECT * FROM subcategoria where Id_Cat = :parentId;");
            
            $consulta->bindParam(":parentId", $parentId, PDO::PARAM_INT);
            //$consulta->bindParam(":passwd", $passwd, PDO::PARAM_STR);
    
            $results = $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
            return $data;
    //        foreach ($data as $usuario) {
            // if ($data["NombreUsuario"] == $user && password_verify($passwd, $data["Contraseña"] )) {
                // return true;
            // }
      //      }
        } catch (PDOException $e) {
            /*echo $e->getMessage();*/
        }
        
        return false;
    }
    public function getChars($id_user) {
        try {
            $consulta = $this->db->prepare("SELECT * FROM chars where id_user = :user_id");

            $consulta->bindParam(":user_id", $id_user, PDO::PARAM_INT);

            $results = $consulta->execute();
            $data = $consulta->fetchAll();
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getChar($id_char) {
        try {
            $consulta = $this->db->prepare("SELECT * FROM chars where id_char = :char_id");

            $consulta->bindParam(":char_id", $id_char, PDO::PARAM_INT);

            $results = $consulta->execute();
            $data = $consulta->fetch();
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertSpell($spellData) {
        try {
            $consulta = $this->db->prepare("INSERT INTO conjuros values(null, :nombre, :descr, :duracion, :concentracion, :casteo, :spell_level, :rango, :clases, :escuela)");
            
            $consulta->bindParam(":nombre", $spellData->name, PDO::PARAM_STR);
            $consulta->bindParam(":descr", $spellData->desc, PDO::PARAM_STR);
            $consulta->bindParam(":duracion", $spellData->duration , PDO::PARAM_STR);
            $consulta->bindParam(":concentracion", $spellData->concentration  , PDO::PARAM_STR);
            $consulta->bindParam(":casteo", $spellData->casting_time , PDO::PARAM_STR);
            $consulta->bindParam(":spell_level", $spellData->level , PDO::PARAM_STR);
            $consulta->bindParam(":rango", $spellData->range , PDO::PARAM_STR);
            $consulta->bindParam(":clases", $spellData->class , PDO::PARAM_STR);
            $consulta->bindParam(":escuela", $spellData->school , PDO::PARAM_STR);

            $results = $consulta->execute();
            echo($results);
            return $results;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getSpellsIds($id_char) {
        try {
            $consulta = $this->db->prepare("SELECT spells FROM spellset where id_char = :char_id");

            $consulta->bindParam(":char_id", $id_char, PDO::PARAM_INT);

            $results = $consulta->execute();
            $data = $consulta->fetch();
            if (isset($data["spells"])) {
                return $data["spells"];
            } else {
                return "";
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getSpells($ids, $diffOrder = null) {
        try {
            if ($ids != null) {

                if ($diffOrder == null) {
                    $consulta = $this->db->prepare("SELECT * FROM conjuros where id_spell in ($ids) ORDER BY `conjuros`.`level` ASC");
                } else {
                    $consulta = $this->db->prepare("SELECT * FROM conjuros where id_spell in ($ids)  ORDER BY 
                CASE
                    WHEN level = 'Truco' THEN 0
                    WHEN level = 'Nivel 1' THEN 1
                    WHEN level = 'Nivel 2' THEN 2
                    WHEN level = 'Nivel 3' THEN 3
                    WHEN level = 'Nivel 4' THEN 4
                    WHEN level = 'Nivel 5' THEN 5
                    WHEN level = 'Nivel 6' THEN 6
                    WHEN level = 'Nivel 7' THEN 7
                    WHEN level = 'Nivel 8' THEN 8
                    WHEN level = 'Nivel 9' THEN 9
                    ELSE 10
                END");
                }
            } else {
                return [];
            }
            
           
            $results = $consulta->execute();
            $data = $consulta->fetchAll();

            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getAllSpells($filters=null) {
        try {

            if ($filters != null) {
                $consulta = $this->db->prepare("SELECT * FROM conjuros where ".$filters."  ORDER BY 
                CASE
                    WHEN level = 'Truco' THEN 0
                    WHEN level = 'Nivel 1' THEN 1
                    WHEN level = 'Nivel 2' THEN 2
                    WHEN level = 'Nivel 3' THEN 3
                    WHEN level = 'Nivel 4' THEN 4
                    WHEN level = 'Nivel 5' THEN 5
                    WHEN level = 'Nivel 6' THEN 6
                    WHEN level = 'Nivel 7' THEN 7
                    WHEN level = 'Nivel 8' THEN 8
                    WHEN level = 'Nivel 9' THEN 9
                    ELSE 10
                END");
            } else {
                $consulta = $this->db->prepare("SELECT * FROM conjuros ORDER BY 
                CASE
                    WHEN level = 'Truco' THEN 0
                    WHEN level = 'Nivel 1' THEN 1
                    WHEN level = 'Nivel 2' THEN 2
                    WHEN level = 'Nivel 3' THEN 3
                    WHEN level = 'Nivel 4' THEN 4
                    WHEN level = 'Nivel 5' THEN 5
                    WHEN level = 'Nivel 6' THEN 6
                    WHEN level = 'Nivel 7' THEN 7
                    WHEN level = 'Nivel 8' THEN 8
                    WHEN level = 'Nivel 9' THEN 9
                    ELSE 10
                END");
            }


            $results = $consulta->execute();
            $data = $consulta->fetchAll();
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function addSpell($id_char, $id_spell) {
        
        $spellsIds = $this->getSpellsIds($id_char);
        $newSpellList = ($this->addSpellIdToList($id_spell, $spellsIds));
        
        // echo $spellsIds;
        
        $consulta = $this->db->prepare("UPDATE `spellset` SET `spells` = :spellList WHERE `spellset`.`id_char` = :id_char;");
        
        
        $consulta->bindParam(":id_char", $id_char, PDO::PARAM_STR);
        $consulta->bindParam(":spellList", $newSpellList, PDO::PARAM_STR);
        
        $results = $consulta->execute();
    }


    public function addSpellIdToList($id_spell, $spellList) {
        // var_dump($spellList);
        // echo("'".strval($id_spell)."'");
        if (strlen($spellList) == 0) {
            return "'".strval($id_spell)."'";
        } else {
            return $spellList.", ".$id_spell;
        }
    }
}
