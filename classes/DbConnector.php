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
            
            if (is_array($data) && isset($data["password"]) && $data["password"] == $passwd) {
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
            error_log("DeepRol getChar: " . $e->getMessage());
            return false;
        }
    }

    public function getCharForUser($id_char, $id_user) {
        try {
            $consulta = $this->db->prepare(
                "SELECT * FROM chars
                WHERE id_char = :char_id AND id_user = :user_id
                LIMIT 1"
            );
            $consulta->execute([
                ":char_id" => (int) $id_char,
                ":user_id" => (int) $id_user,
            ]);

            return $consulta->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DeepRol getCharForUser: " . $e->getMessage());
            return false;
        }
    }

    public function updateCharacterSheetMetadata(
        $id_char,
        $id_user,
        $raza,
        $subraza,
        $nivel,
        $clase,
        $subclase,
        $pdf_path,
        array $classLevels = [],
        array $languages = []
    ) {
        try {
            $this->db->beginTransaction();
            $consulta = $this->db->prepare(
                "UPDATE chars
                SET raza = :char_race,
                    subraza = :char_subrace,
                    nivel = :char_level,
                    clase = :char_class,
                    subclase = :char_subclass,
                    pdf_path = :pdf_path
                WHERE id_char = :char_id AND id_user = :user_id"
            );

            $updated = $consulta->execute([
                ":char_race" => (string) $raza,
                ":char_subrace" => (string) $subraza,
                ":char_level" => (int) $nivel,
                ":char_class" => (string) $clase,
                ":char_subclass" => (string) $subclase,
                ":pdf_path" => (string) $pdf_path,
                ":char_id" => (int) $id_char,
                ":user_id" => (int) $id_user,
            ]);

            if (!$updated) {
                throw new RuntimeException("No se pudo actualizar el personaje.");
            }

            $this->replaceCharacterProgression(
                (int) $id_char,
                $classLevels ?: [[
                    "class_name" => (string) $clase,
                    "subclass_name" => (string) $subclase,
                    "level" => (int) $nivel,
                    "is_primary" => true,
                ]],
                $languages
            );
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("DeepRol updateCharacterSheetMetadata: " . $e->getMessage());
            return false;
        }
    }

    public function getCharacterClasses($id_char): array
    {
        try {
            $consulta = $this->db->prepare(
                "SELECT class_name, subclass_name, class_level, is_primary, sort_order
                FROM character_class_levels
                WHERE id_char = :char_id
                ORDER BY is_primary DESC, sort_order ASC, id_character_class ASC"
            );
            $consulta->execute([":char_id" => (int) $id_char]);
            return array_map(
                static function (array $row): array {
                    return [
                        "class_name" => (string) $row["class_name"],
                        "subclass_name" => (string) $row["subclass_name"],
                        "level" => (int) $row["class_level"],
                        "is_primary" => (bool) $row["is_primary"],
                        "sort_order" => (int) $row["sort_order"],
                    ];
                },
                $consulta->fetchAll(PDO::FETCH_ASSOC)
            );
        } catch (PDOException $e) {
            error_log("DeepRol getCharacterClasses: " . $e->getMessage());
            return [];
        }
    }

    public function getCharacterLanguages($id_char): array
    {
        try {
            $consulta = $this->db->prepare(
                "SELECT language_name
                FROM character_languages
                WHERE id_char = :char_id
                ORDER BY sort_order ASC, id_character_language ASC"
            );
            $consulta->execute([":char_id" => (int) $id_char]);
            return array_map("strval", $consulta->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log("DeepRol getCharacterLanguages: " . $e->getMessage());
            return [];
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
            error_log("DeepRol getSpellsIds: " . $e->getMessage());
            return "";
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
            error_log("DeepRol getSpells: " . $e->getMessage());
            return [];
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
        $subrazaPersonaje,
        $nivelPersonaje,
        $clasePersonaje,
        $subclasePersonaje,
        $pdfPath,
        $imagenPequeña,
        $imagenGeneral,
        array $classLevels = [],
        array $languages = []
    ) {
        try {
            $this->db->beginTransaction();

            $consulta = $this->db->prepare(
                "INSERT INTO chars (
                    id_user,
                    name,
                    raza,
                    subraza,
                    nivel,
                    clase,
                    subclase,
                    pdf_path,
                    image_path,
                    full_body_image_path
                ) VALUES (
                    :userId,
                    :charName,
                    :charRace,
                    :charSubrace,
                    :charLevel,
                    :charClass,
                    :charSubclass,
                    :pdfPath,
                    :smallImage,
                    :bigImage
                )"
            );

            $consulta->execute([
                ":userId" => (int) $idUser,
                ":charName" => (string) $nombrePersonaje,
                ":charRace" => (string) $razaPersonaje,
                ":charSubrace" => (string) $subrazaPersonaje,
                ":charLevel" => (int) $nivelPersonaje,
                ":charClass" => (string) $clasePersonaje,
                ":charSubclass" => (string) $subclasePersonaje,
                ":pdfPath" => (string) $pdfPath,
                ":smallImage" => (string) $imagenPequeña,
                ":bigImage" => (string) $imagenGeneral,
            ]);

            $characterId = (int) $this->db->lastInsertId();
            $spellSheet = $this->db->prepare(
                "INSERT INTO spellset (id_char, spells) VALUES (:charId, '')"
            );
            $spellSheet->execute([":charId" => $characterId]);
            $this->replaceCharacterProgression(
                $characterId,
                $classLevels ?: [[
                    "class_name" => (string) $clasePersonaje,
                    "subclass_name" => (string) $subclasePersonaje,
                    "level" => (int) $nivelPersonaje,
                    "is_primary" => true,
                ]],
                $languages
            );

            $this->db->commit();
            return $characterId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    private function replaceCharacterProgression(
        int $characterId,
        array $classLevels,
        array $languages
    ): void {
        $deleteClasses = $this->db->prepare(
            "DELETE FROM character_class_levels WHERE id_char = :char_id"
        );
        $deleteClasses->execute([":char_id" => $characterId]);

        $insertClass = $this->db->prepare(
            "INSERT INTO character_class_levels (
                id_char,
                class_name,
                subclass_name,
                class_level,
                is_primary,
                sort_order
            ) VALUES (
                :char_id,
                :class_name,
                :subclass_name,
                :class_level,
                :is_primary,
                :sort_order
            )"
        );
        foreach (array_values($classLevels) as $index => $classLevel) {
            if (!is_array($classLevel)) {
                continue;
            }

            $insertClass->execute([
                ":char_id" => $characterId,
                ":class_name" => trim((string) ($classLevel["class_name"] ?? "")),
                ":subclass_name" => trim((string) ($classLevel["subclass_name"] ?? "")),
                ":class_level" => max(1, min(20, (int) ($classLevel["level"] ?? 1))),
                ":is_primary" => $index === 0 ? 1 : 0,
                ":sort_order" => $index,
            ]);
        }

        $deleteLanguages = $this->db->prepare(
            "DELETE FROM character_languages WHERE id_char = :char_id"
        );
        $deleteLanguages->execute([":char_id" => $characterId]);

        $insertLanguage = $this->db->prepare(
            "INSERT INTO character_languages (
                id_char,
                language_name,
                sort_order
            ) VALUES (
                :char_id,
                :language_name,
                :sort_order
            )"
        );
        foreach (array_values(array_unique(array_map("strval", $languages))) as $index => $language) {
            $language = trim($language);
            if ($language === "") {
                continue;
            }

            $insertLanguage->execute([
                ":char_id" => $characterId,
                ":language_name" => $language,
                ":sort_order" => $index,
            ]);
        }
    }

    public function getNotes($userId, $characterId = null) {
        try {
            $query = "
                SELECT RelatedChar, ID, ID_User, Nombre, Date, Value
                FROM notes
                WHERE ID_User = :userId
            ";
            $parameters = [":userId" => (int) $userId];

            if ($characterId !== null) {
                $query .= " AND RelatedChar = :characterId";
                $parameters[":characterId"] = (int) $characterId;
            }

            $query .= " ORDER BY RelatedChar, Date DESC, ID DESC";
            $consulta = $this->db->prepare($query);
            $results = $consulta->execute($parameters);
            $data = $consulta->fetchAll(PDO::FETCH_GROUP);
            return $data;
        } catch (PDOException $e) {
            error_log("DeepRol getNotes: " . $e->getMessage());
            return [];
        }
    }

    public function searchCharacters($userId, $query, $limit = 6) {
        try {
            $limit = max(1, min(50, (int) $limit));
            $search = "%" . trim((string) $query) . "%";
            $consulta = $this->db->prepare(
                "SELECT id_char, name, raza, subraza, nivel, clase, subclase
                FROM chars
                WHERE id_user = :userId
                AND (
                    name LIKE :searchName
                    OR raza LIKE :searchRace
                    OR subraza LIKE :searchSubrace
                    OR clase LIKE :searchClass
                    OR subclase LIKE :searchSubclass
                )
                ORDER BY
                    CASE WHEN name LIKE :prefix THEN 0 ELSE 1 END,
                    name ASC
                LIMIT {$limit}"
            );
            $consulta->execute([
                ":userId" => (int) $userId,
                ":searchName" => $search,
                ":searchRace" => $search,
                ":searchSubrace" => $search,
                ":searchClass" => $search,
                ":searchSubclass" => $search,
                ":prefix" => trim((string) $query) . "%",
            ]);

            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DeepRol searchCharacters: " . $e->getMessage());
            return [];
        }
    }

    public function searchSpells($query, $limit = 6) {
        try {
            $limit = max(1, min(50, (int) $limit));
            $search = "%" . trim((string) $query) . "%";
            $consulta = $this->db->prepare(
                "SELECT id_spell, name, level, escuela, clases, descr
                FROM conjuros
                WHERE
                    name LIKE :searchName
                    OR descr LIKE :searchDescription
                    OR level LIKE :searchLevel
                    OR escuela LIKE :searchSchool
                    OR clases LIKE :searchClasses
                ORDER BY
                    CASE WHEN name LIKE :prefix THEN 0 ELSE 1 END,
                    name ASC
                LIMIT {$limit}"
            );
            $consulta->execute([
                ":searchName" => $search,
                ":searchDescription" => $search,
                ":searchLevel" => $search,
                ":searchSchool" => $search,
                ":searchClasses" => $search,
                ":prefix" => trim((string) $query) . "%",
            ]);

            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DeepRol searchSpells: " . $e->getMessage());
            return [];
        }
    }

    public function searchNotes($userId, $query, $limit = 6) {
        try {
            $limit = max(1, min(50, (int) $limit));
            $search = "%" . trim((string) $query) . "%";
            $consulta = $this->db->prepare(
                "SELECT
                    notes.ID,
                    notes.Nombre,
                    notes.Value,
                    notes.Date,
                    notes.RelatedChar,
                    chars.name AS character_name
                FROM notes
                LEFT JOIN chars
                    ON chars.id_char = notes.RelatedChar
                    AND chars.id_user = notes.ID_User
                WHERE notes.ID_User = :userId
                AND (
                    notes.Nombre LIKE :searchName
                    OR notes.Value LIKE :searchValue
                    OR chars.name LIKE :searchCharacter
                )
                ORDER BY
                    CASE WHEN notes.Nombre LIKE :prefix THEN 0 ELSE 1 END,
                    notes.Date DESC,
                    notes.ID DESC
                LIMIT {$limit}"
            );
            $consulta->execute([
                ":userId" => (int) $userId,
                ":searchName" => $search,
                ":searchValue" => $search,
                ":searchCharacter" => $search,
                ":prefix" => trim((string) $query) . "%",
            ]);

            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DeepRol searchNotes: " . $e->getMessage());
            return [];
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
