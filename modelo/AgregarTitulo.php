<?php
    include_once 'Conexion.php';

    class AgregarTitulo {
        var $objetos;

        public function __construct(){
            $db = new Conexion();
            $this->acceso = $db->pdo;
        }

        //-----------------------------------------------------------
        // Buscar los registros segun criterio de busqueda en consulta
        //-----------------------------------------------------------
        function BuscarTodos($consulta){
            try {
                if(!empty($consulta)){                
                    $sql = "SELECT DISTINCT tituloObtenido as nombre FROM egresado WHERE tituloObtenido LIKE :consulta ORDER BY tituloObtenido ASC";      
                    $query = $this->acceso->prepare($sql);
                    $query->execute(array(':consulta'=>"%$consulta%"));
                }
                else{
                    $sql = "SELECT DISTINCT tituloObtenido as nombre FROM egresado WHERE tituloObtenido IS NOT NULL AND tituloObtenido != '' ORDER BY tituloObtenido ASC";          
                    $query = $this->acceso->prepare($sql);
                    $query->execute();
                }
                
                $results = $query->fetchall(PDO::FETCH_ASSOC);
                $this->objetos = [];
                foreach($results as $row) {
                    $obj = new stdClass();
                    $obj->id = $row['nombre'];
                    $obj->nombre = $row['nombre'];
                    $this->objetos[] = $obj;
                }
                return $this->objetos;
            } catch (PDOException $e) {
                error_log("Error in BuscarTodos: " . $e->getMessage());
                $this->objetos = [];
                return $this->objetos;
            }
        } 
        
        //-----------------------------------------------------------
        // Buscar los registros segun criterio de busqueda en consulta
        //-----------------------------------------------------------
        function Buscar($id){
            // Simular búsqueda por ID (que ahora es el nombre)
            $obj = new stdClass();
            $obj->id = $id;
            $obj->nombre = $id;
            $this->objetos = [$obj];
            return $this->objetos;    
        }   

        //---------------------------------------------------------
        // Crear - DEPRECATED
        //---------------------------------------------------------
        function Crear($id, $nombre){
            // La tabla titulo ya no existe. No hacemos nada.
            echo 'add'; 
        }

        //-----------------------------------------------------------
        // Editar - DEPRECATED
        //-----------------------------------------------------------
        function Editar($id, $nombre){
            // La tabla titulo ya no existe. No hacemos nada.
            echo 'update';
         }

        //-----------------------------------------------------------
        // Eliminar - DEPRECATED
        //-----------------------------------------------------------
        function Eliminar($id){
            // La tabla titulo ya no existe. No hacemos nada.
            echo 'eliminado';
        }

        //-----------------------------------------------------------
        // Función para cargar un ComboBox
        //-----------------------------------------------------------
        function Seleccionar(){
            $sql = "SELECT DISTINCT tituloObtenido as nombre FROM egresado WHERE tituloObtenido IS NOT NULL AND tituloObtenido != '' ORDER BY tituloObtenido asc";          
            $query = $this->acceso->prepare($sql);
            $query->execute();
            $results = $query->fetchall(PDO::FETCH_ASSOC);
            
            $this->objetos = [];
            foreach($results as $row) {
                $obj = new stdClass();
                $obj->id = $row['nombre'];
                $obj->nombre = $row['nombre'];
                $this->objetos[] = $obj;
            }
        
            return $this->objetos;    
        }
    }     
?>