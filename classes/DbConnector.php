<?php 

//}require_once($_SERVER["DOCUMENT_ROOT"]."/storage/data.php");

class DbConector {
    
<<<<<<< Updated upstream
    // private const DB_DATA = "mysql:host=qanr736.deeprol.com;dbname=qanr736;charset=utf8mb4";
    private const DB_DATA = "mysql:host=localhost;dbname=deeprol;charset=utf8mb4";
    // private const USERNAME = "qanr736";
    private const USERNAME = "root";
    // private const PASSWD = "Jaktuni.calo2@J";
    private const PASSWD = "";
    private $db = "";
=======
    private PDO $db;
>>>>>>> Stashed changes

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
        $dsn = getenv('DEEPROL_DB_DSN') ?: 'mysql:host=localhost;dbname=deeprol;charset=utf8mb4';
        $user = getenv('DEEPROL_DB_USER') ?: 'root';
        $password = getenv('DEEPROL_DB_PASSWORD') ?: '';
        $this->db = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function checkLogin($user, $passwd) {
        try {
            $consulta = $this->db->prepare("select ID_usuario, username, password from usuario where username = :username");
            
            $consulta->bindParam(":username",$user, PDO::PARAM_STR);
            
            $results = $consulta->execute();
            $data = $consulta->fetch(PDO::FETCH_ASSOC);
            
            if ($data && (password_verify($passwd, $data['password']) || hash_equals((string) $data['password'], $passwd))) {
                if (!password_get_info((string) $data['password'])['algo']) {
                    $this->updatePassword((int) $data['ID_usuario'], $passwd);
                }
                return $data["ID_usuario"];
            }

            return false;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        
        return false;
    }

<<<<<<< Updated upstream
=======
    public function createUser($user, $password) {
        $consulta = $this->db->prepare("INSERT INTO usuario (username, password) VALUES (:username, :password)");

        $user = strtoupper(trim($user));
        $password = password_hash($password, PASSWORD_DEFAULT);

        $consulta->bindParam(":username",$user, PDO::PARAM_STR);
        $consulta->bindParam(":password",$password, PDO::PARAM_STR);

        return $consulta->execute();
    }

    private function updatePassword(int $userId, string $password): void {
        $statement = $this->db->prepare('UPDATE usuario SET password = :password WHERE ID_usuario = :id');
        $statement->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $userId]);
    }
>>>>>>> Stashed changes
    
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
            $idList = array_values(array_filter(array_map('intval', preg_split('/\s*,\s*/', trim((string) $ids, " '\"")))));
            if ($idList) {

                $placeholders = implode(',', array_fill(0, count($idList), '?'));

                if ($diffOrder == null) {
                    $consulta = $this->db->prepare("SELECT * FROM conjuros WHERE id_spell IN ($placeholders) ORDER BY `conjuros`.`level` ASC");
                } else {
                    $consulta = $this->db->prepare("SELECT * FROM conjuros WHERE id_spell IN ($placeholders) ORDER BY
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
            
           
            $results = $consulta->execute($idList);
            $data = $consulta->fetchAll();

            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getAllSpells($filters=null) {
        try {
            $where = [];
            $params = [];
            if (!empty($filters['name'])) { $where[] = 'name LIKE :name'; $params['name'] = '%' . $filters['name'] . '%'; }
            if (!empty($filters['class'])) { $where[] = 'clases LIKE :class'; $params['class'] = '%' . $filters['class'] . '%'; }
            if ($where) {
                $consulta = $this->db->prepare("SELECT * FROM conjuros WHERE ".implode(' AND ', $where)." ORDER BY
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

            $results = $consulta->execute($params);
            $data = $consulta->fetchAll();
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getAllSpellsLevels($filters=null) {
        try {
            $where = [];
            $params = [];
            if (!empty($filters['name'])) { $where[] = 'name LIKE :name'; $params['name'] = '%' . $filters['name'] . '%'; }
            if (!empty($filters['class'])) { $where[] = 'clases LIKE :class'; $params['class'] = '%' . $filters['class'] . '%'; }
            if ($where) {
                $consulta = $this->db->prepare("SELECT distinct level FROM conjuros WHERE ".implode(' AND ', $where)." ORDER BY
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


            $results = $consulta->execute($params);
            $data = $consulta->fetchAll();
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function addSpell($id_char, $id_spell) {
<<<<<<< Updated upstream
        
        $spellsIds = $this->getSpellsIds($id_char);
        $newSpellList = ($this->addSpellIdToList($id_spell, $spellsIds));
        
        // echo $spellsIds;
=======
        $id_char = (int) $id_char;
        $id_spell = (int) $id_spell;
        $spellsIds = $this->getSpellsIds($id_char);
        $newSpellList = ($this->addSpellIdToList($id_spell, $spellsIds));
        $consultaHelper = $this->db->prepare("SELECT * FROM `spellset` WHERE id_char = :id_char");
        $consultaHelper->execute(['id_char' => $id_char]);
        $data = $consultaHelper->fetchAll();
        if (count($data) == 0) {
            $this->createSpellSheet($id_char);
        }
>>>>>>> Stashed changes
        
        $consulta = $this->db->prepare("UPDATE `spellset` SET `spells` = :spellList WHERE `spellset`.`id_char` = :id_char;");
        
        
        $consulta->bindParam(":id_char", $id_char, PDO::PARAM_STR);
        $consulta->bindParam(":spellList", $newSpellList, PDO::PARAM_STR);
        
        $results = $consulta->execute();
    }


    public function addSpellIdToList($id_spell, $spellList) {
        // var_dump($spellList);
        // echo("'".strval($id_spell)."'");
        if (strlen($spellList) == 0) {
            return strval((int) $id_spell);
        } else {
            return $spellList.", ".$id_spell;
        }
    }
<<<<<<< Updated upstream
=======

    public function createChar(
        $idUser,
        $nombrePersonaje,
        $razaPersonaje,
        $imagenPequeña,
        $imagenGeneral
    ) {
        $consulta = $this->db->prepare("INSERT INTO chars (id_user, name, raza, pdf_path, image_path, full_body_image_path) VALUES (:userId, :charName, :charRace, 'ficha.pdf', :smallImage, :bigImage)");

        $consulta->bindParam("userId", $idUser, PDO::PARAM_INT);
        $consulta->bindParam("charName", $nombrePersonaje, PDO::PARAM_STR);
        $consulta->bindParam("charRace", $razaPersonaje, PDO::PARAM_STR);
        $consulta->bindParam("smallImage", $imagenPequeña, PDO::PARAM_STR);
        $consulta->bindParam("bigImage", $imagenGeneral, PDO::PARAM_STR);

        return $consulta->execute();
    }

    public function getNotes($userId) {
        try {
            $consulta = $this->db->prepare("SELECT ID, Nombre, RelatedChar, Date, name, image_path FROM notes as n, chars where  relatedChar = id_char and n.ID_User like :userId order by RelatedChar asc");

            $consulta->bindParam("userId", $userId, PDO::PARAM_INT);
            $results = $consulta->execute();
            $data = $consulta->fetchAll();
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

<<<<<<< Updated upstream
=======
    public function getCharForUser(int $charId, int $userId) {
        $statement = $this->db->prepare('SELECT * FROM chars WHERE id_char = :charId AND id_user = :userId');
        $statement->execute(['charId' => $charId, 'userId' => $userId]);
        return $statement->fetch();
    }

    public function getNoteForUser(int $noteId, int $userId) {
        $statement = $this->db->prepare('SELECT * FROM notes WHERE ID = :noteId AND ID_User = :userId');
        $statement->execute(['noteId' => $noteId, 'userId' => $userId]);
        return $statement->fetch();
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

>>>>>>> Stashed changes
    public function saveNote($noteId, $noteValue) {
        try {
            $consulta = $this->db->prepare("UPDATE notes SET Value = :noteValue WHERE ID = :noteId");

            $consulta->bindParam(":noteId", $noteId, PDO::PARAM_INT);
            $consulta->bindParam(":noteValue", $noteValue, PDO::PARAM_STR);
            $results = $consulta->execute();

            return $results;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function saveNoteForUser(int $noteId, int $userId, string $noteValue): bool {
        $statement = $this->db->prepare('UPDATE notes SET Value = :value WHERE ID = :noteId AND ID_User = :userId');
        $statement->execute(['value' => $noteValue, 'noteId' => $noteId, 'userId' => $userId]);
        return $statement->rowCount() > 0;
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
            $consulta = $this->db->prepare("SELECT * FROM razas");

            $results = $consulta->execute();
            $data = $consulta->fetchAll();
            // echo '<pre>'; print_r($data); echo '</pre>';
            return $data;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
>>>>>>> Stashed changes
}
