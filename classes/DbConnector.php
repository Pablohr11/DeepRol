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
            $consulta = $this->db->prepare("select username, password from usuario where username = :username");
            
            $consulta->bindParam(":username",$user, PDO::PARAM_STR);
            
            $results = $consulta->execute();
            $data = $consulta->fetch(PDO::FETCH_ASSOC);
            
            if ($data["password"] == $passwd) {
                return true;
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
}