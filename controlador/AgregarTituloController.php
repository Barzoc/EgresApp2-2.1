<?php
    // Set proper headers for JSON response
    header('Content-Type: application/json');
    
    // Enable error reporting for debugging (remove in production)
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Don't display errors in output
    ini_set('log_errors', 1); // Log errors instead
    
    include_once '../modelo/AgregarTitulo.php';
    
    $titulo = new AgregarTitulo();

    if (!isset($_POST['funcion']) || empty($_POST['funcion'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetro "funcion" requerido']);
        exit;
    }
    
    //-------------------------------------------------------------------
    // Funcion para buscar todos los registros DATATABLES
    //-------------------------------------------------------------------
    if ($_POST['funcion'] == 'listar'){
        $json = Array();
        //LLamado al controlador
        $titulo->BuscarTodos('');
        foreach ($titulo->objetos as $objeto) {
            $json[]=array(
                            'id'=>$objeto->id,
                            'nombre'=>$objeto->nombre
            );
        }
        $jsonstring = json_encode($json);
        echo $jsonstring;

    }

    //-------------------------------------------------------------------
    // Funcion para buscar un titulo
    //-------------------------------------------------------------------
    if ($_POST['funcion'] == 'buscar'){
        $json = Array();
        //LLamado al controlador
        $titulo->Buscar($_POST['dato']);
        foreach ($titulo->objetos as $objeto) {
            $json[]=array(
                            'id'=>$objeto->id,
                            'nombre'=>$objeto->nombre
            );
        }
        $jsonstring = json_encode($json[0]);
        echo $jsonstring;
        
    }

    //-------------------------------------------------------------------
    // Funcion para crear
    //-------------------------------------------------------------------
    if ($_POST['funcion'] == 'crear'){
        $titulo->Crear($_POST['id'], $_POST['nombre']);

    }

    //-------------------------------------------------------------------
    // Funcion para editar
    //-------------------------------------------------------------------
    if ($_POST['funcion'] == 'editar'){
        $titulo->Editar($_POST['id'], $_POST['nombre']);

    }

    //-------------------------------------------------------------------
    // Funcion para eliminar
    //-------------------------------------------------------------------
    if ($_POST['funcion'] == 'eliminar'){
        $titulo->Eliminar($_POST['id']);

    }

    if ($_POST['funcion'] == 'seleccionar'){
        $json = Array();
        //LLamado al controlador
        $titulo->Seleccionar();
        foreach ($titulo->objetos as $objeto) {
            $json[]=array(
                            'id'=>$objeto->id,
                            'nombre'=>$objeto->nombre
            );
        }
        $jsonstring = json_encode($json);
        echo $jsonstring;

    }



?>
