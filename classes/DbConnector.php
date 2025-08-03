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

    public function createUser($user, $password) {
        $consulta = $this->db->prepare("insert into usuario values(null, :username, :password)");

        $consulta->bindParam(":username",$user, PDO::PARAM_STR);
        $consulta->bindParam(":password",$password, PDO::PARAM_STR);

        return $consulta->execute();
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

    public function getCharName($id_char) {
        try {
            $consulta = $this->db->prepare("SELECT name FROM chars where id_char = :char_id");

            $consulta->bindParam(":char_id", $id_char, PDO::PARAM_INT);

            $results = $consulta->execute();
            $data = $consulta->fetch();
            return $data[0];
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

    public function getAllSpellsLevels($filters=null) {
        try {

            if ($filters != null) {
                $consulta = $this->db->prepare("SELECT distinct level FROM conjuros where ".$filters."  ORDER BY 
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
                $consulta = $this->db->prepare("SELECT distinct level FROM conjuros ORDER BY 
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
        $consultaHelper = $this->db->prepare("SELECT * FROM `spellset` where id_char = ".$id_char);
        $data = $consultaHelper->fetchAll();
        if (count($data) == 0) {
            $this->createSpellSheet($id_char);
        }
        
        $consulta = $this->db->prepare("UPDATE `spellset` SET `spells` = :spellList WHERE `spellset`.`id_char` = :id_char;");
        
        
        $consulta->bindParam(":id_char", $id_char, PDO::PARAM_STR);
        $consulta->bindParam(":spellList", $newSpellList, PDO::PARAM_STR);
        
        $results = $consulta->execute();

        return $results;
    }

    public function createSpellSheet($id_char) {
        
        $consulta = $this->db->prepare("INSERT INTO `spellset` (`id_spellset`, `id_char`, `spells`) VALUES (NULL, :id_char, '');");
        
        $consulta->bindParam(":id_char", $id_char, PDO::PARAM_STR);
        
        $results = $consulta->execute();

        return $results;
    }    

    public function addSpellIdToList($id_spell, $spellList) {

        if (strlen($spellList) == 0) {
            return "'".strval($id_spell)."'";
        } else {
            return $spellList.", ".$id_spell;
        }
    }

    public function createChar(
        $idUser,
        $nombrePersonaje,
        $razaPersonaje,
        $imagenPequeña,
        $imagenGeneral
    ) {
        $consulta = $this->db->prepare("insert into chars values(null, :userId, :charName, :charRace, 'ficha.pdf', :smallImage, :bigImage)");

        $consulta->bindParam("userId", $idUser, PDO::PARAM_INT);
        $consulta->bindParam("charName", $nombrePersonaje, PDO::PARAM_STR);
        $consulta->bindParam("charRace", $razaPersonaje, PDO::PARAM_STR);
        $consulta->bindParam("smallImage", $imagenPequeña, PDO::PARAM_STR);
        $consulta->bindParam("bigImage", $imagenGeneral, PDO::PARAM_STR);

        $results = $consulta->execute();
    }

    public function getNotes($userId) {
        try {
            $consulta = $this->db->prepare("SELECT RelatedChar, ID, ID_User,RelatedChar, Nombre, Date, Value FROM notes where ID_User like :userId ORDER BY RelatedChar");

            $consulta->bindParam("userId", $userId, PDO::PARAM_INT);
            $results = $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_GROUP);
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getNote($noteId) {
        try {
            $consulta = $this->db->prepare("SELECT * FROM notes where ID like :noteId");

            $consulta->bindParam("noteId", $noteId, PDO::PARAM_INT);
            $results = $consulta->execute();
            $data = $consulta->fetch();
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function createNote($id_user, $id_char, $name, $date) {
        try {
            $consulta = $this->db->prepare("
                INSERT INTO notes 
                (ID_User, RelatedChar, Nombre, Date, Value) 
                VALUES (:id_user, :id_char, :name, :date, '')
            ");

            $consulta->bindParam(":id_user", $id_user, PDO::PARAM_INT);
            $consulta->bindParam(":id_char", $id_char, PDO::PARAM_INT);
            $consulta->bindParam(":name", $name, PDO::PARAM_STR);
            $consulta->bindParam(":date", $date, PDO::PARAM_STR);
            $results = $consulta->execute();
            return $results;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function saveNote($noteId, $noteValue) {
        try {
            $consulta = $this->db->prepare("UPDATE notes SET Value = :noteValue WHERE ID = :noteId");

            $consulta->bindParam("noteId", $noteId, PDO::PARAM_INT);
            $consulta->bindParam("noteValue", $noteValue, PDO::PARAM_STR);
            $results = $consulta->execute();
            return $results;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getClasses() {
        try {
            // $consulta = $this->db->prepare("SELECT id, Nombre, short_desc, descr FROM clases");
            $consulta = $this->db->prepare("SELECT * FROM clases");

            $results = $consulta->execute();
            $data = $consulta->fetchAll();
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getRazas() {
        try {
            // $consulta = $this->db->prepare("SELECT id, Nombre, short_desc, descr FROM clases");
            $consulta = $this->db->prepare("SELECT * FROM razas");

            $results = $consulta->execute();
            $data = $consulta->fetchAll();
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getNoteChars($userId) {
        try {
            // $consulta = $this->db->prepare("SELECT id, Nombre, short_desc, descr FROM clases");
            $consulta = $this->db->prepare("select id_char,id_char, name, image_path from chars where id_char in (select RelatedChar from notes where ID_User = :userId);");

            $consulta->bindParam("userId", $userId, PDO::PARAM_STR);


            $results = $consulta->execute();
            // if (isset($fetch_group)) {
            //     $data = $consulta->fetchAll(PDO::FETCH_GROUP);
            //     return $data;
            // }
            $data = $consulta->fetchAll(PDO::FETCH_GROUP);
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
